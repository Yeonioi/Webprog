<?php
/**
 * FriendLink – Global Config & Database Connection
 * Using PDO ODBC for SQL Server (works with LocalDB + Windows Auth)
 */

// ---------------------------------------------
// DATABASE CONFIGURATION
// ---------------------------------------------

// For LocalDB (works on Windows without username/password)
define('DB_SERVER', '(localdb)\\MSSQLLocalDB');
define('DB_NAME', 'friendlink');

// Referral system constants
define('POINTS_PER_SIGNUP', 50);
define('POINTS_PER_FIRST_SERVICE', 30);
define('POINTS_PER_FIRST_BOOKING', 20);

// Start session
session_start();

// ---------------------------------------------
// DATABASE CONNECTION (ODBC)
// ---------------------------------------------
try {
    $dsn = "odbc:Driver={ODBC Driver 17 for SQL Server};Server=" . DB_SERVER . ";Database=" . DB_NAME . ";Trusted_Connection=yes;";
    
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ---------------------------------------------
// UTILITY FUNCTIONS
// ---------------------------------------------

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get logged-in user info
function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit();
}

// Escape HTML output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ---------------------------------------------
// CATEGORY HELPERS
// ---------------------------------------------

function getGeneralCategories($conn) {
    $stmt = $conn->query("SELECT * FROM general_categories ORDER BY name");
    return $stmt->fetchAll();
}

function getServiceCategoriesByGeneral($conn, $generalCategoryId) {
    $stmt = $conn->prepare("
        SELECT sc.*, gc.name AS general_category_name, gc.icon, gc.color
        FROM service_categories sc
        JOIN general_categories gc ON sc.general_category_id = gc.id
        WHERE sc.general_category_id = ? AND sc.is_approved = 1
        ORDER BY sc.name
    ");
    $stmt->execute([$generalCategoryId]);
    return $stmt->fetchAll();
}

// ---------------------------------------------
// REFERRAL CODE GENERATOR
// ---------------------------------------------

function generateReferralCode($conn, $username) {
    // Clean + shorten username
    $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $username), 0, 6));
    $code = $base . rand(100, 999);

    // Ensure uniqueness
    $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
    $stmt->execute([$code]);

    if ($stmt->fetch()) {
        return generateReferralCode($conn, $username . rand(1, 9));
    }

    return $code;
}

// ---------------------------------------------
// REFERRAL PROCESSING
// ---------------------------------------------

function processReferralSignup($conn, $newUserId, $referralCode) {
    if (!$referralCode) return;

    try {
        // Find referrer
        $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$referralCode]);
        $referrer = $stmt->fetch();

        if (!$referrer) return;

        // Create referral record
        $stmt = $conn->prepare("
            INSERT INTO referrals (referrer_id, referred_user_id, referral_code, status, points_earned, created_at)
            VALUES (?, ?, ?, 'completed', ?, GETDATE())
        ");
        $stmt->execute([$referrer['id'], $newUserId, $referralCode, POINTS_PER_SIGNUP]);

        // Get inserted referral ID
        $referralId = $conn->query("SELECT SCOPE_IDENTITY() AS id")->fetch()['id'];

        // Update referrer totals
        $stmt = $conn->prepare("
            UPDATE users 
            SET referral_points = referral_points + ?, total_referrals = total_referrals + 1
            WHERE id = ?
        ");
        $stmt->execute([POINTS_PER_SIGNUP, $referrer['id']]);

        // Insert reward record
        $stmt = $conn->prepare("
            INSERT INTO referral_rewards (user_id, referral_id, reward_type, points, description, created_at)
            VALUES (?, ?, 'signup', ?, 'Friend signed up using your referral code', GETDATE())
        ");
        $stmt->execute([$referrer['id'], $referralId, POINTS_PER_SIGNUP]);

        // Update referred user
        $stmt = $conn->prepare("UPDATE users SET referred_by = ? WHERE id = ?");
        $stmt->execute([$referrer['id'], $newUserId]);

        // Check milestone rewards
        checkMilestoneRewards($conn, $referrer['id']);

    } catch(PDOException $e) {
        error_log("Referral error: " . $e->getMessage());
    }
}

// ---------------------------------------------
// MILESTONE REWARDS
// ---------------------------------------------

function checkMilestoneRewards($conn, $userId) {
    try {
        // User totals
        $stmt = $conn->prepare("SELECT total_referrals FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) return;

        // Find milestones user qualifies for
        $stmt = $conn->prepare("
            SELECT * FROM referral_milestones
            WHERE referrals_required <= ? AND is_active = 1
            ORDER BY referrals_required DESC
        ");
        $stmt->execute([$user['total_referrals']]);
        $milestones = $stmt->fetchAll();

        foreach ($milestones as $m) {
            // Skip already awarded milestones
            $stmt = $conn->prepare("
                SELECT id FROM referral_rewards
                WHERE user_id = ? AND reward_type = 'milestone'
                AND description LIKE ?
            ");
            $stmt->execute([$userId, '%' . $m['title'] . '%']);

            if ($stmt->fetch()) continue;

            // Award milestone points
            $stmt = $conn->prepare("UPDATE users SET referral_points = referral_points + ? WHERE id = ?");
            $stmt->execute([$m['points_reward'], $userId]);

            // Get latest referral
            $latest = $conn->prepare("
                SELECT TOP 1 id FROM referrals 
                WHERE referrer_id = ? ORDER BY created_at DESC
            ");
            $latest->execute([$userId]);
            $ref = $latest->fetch();

            if (!$ref) continue;

            // Insert milestone reward
            $stmt = $conn->prepare("
                INSERT INTO referral_rewards (user_id, referral_id, reward_type, points, description, created_at)
                VALUES (?, ?, 'milestone', ?, ?, GETDATE())
            ");
            $stmt->execute([
                $userId,
                $ref['id'],
                $m['points_reward'],
                $m['badge_icon'] . ' ' . $m['title'] . ': ' . $m['description']
            ]);
        }

    } catch(PDOException $e) {
        error_log("Milestone error: " . $e->getMessage());
    }
}

// ---------------------------------------------
// REFERRAL STATS
// ---------------------------------------------

function getReferralStats($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT 
            u.referral_code,
            u.referral_points,
            u.total_referrals,
            COUNT(DISTINCT r.id) AS completed_referrals,
            ISNULL(SUM(rr.points), 0) AS total_points_earned
        FROM users u
        LEFT JOIN referrals r ON u.id = r.referrer_id
        LEFT JOIN referral_rewards rr ON u.id = rr.user_id
        WHERE u.id = ?
        GROUP BY u.id, u.referral_code, u.referral_points, u.total_referrals
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

?>
