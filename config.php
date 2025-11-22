<?php
// Database configuration for SQL Server
define('DB_HOST', 'localhost'); // or 'localhost\SQLEXPRESS' if using a named instance
define('DB_USER', 'sa');        // your SQL Server username
define('DB_PASS', 'YourPassword123!'); // your SQL Server password
define('DB_NAME', 'friendlink');

// Referral system configuration
define('POINTS_PER_SIGNUP', 50);
define('POINTS_PER_FIRST_SERVICE', 30);
define('POINTS_PER_FIRST_BOOKING', 20);

// Start session
session_start();

// Create database connection using PDO for SQL Server
try {
    $conn = new PDO("sqlsrv:Server=" . DB_HOST . ";Database=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // echo "Connected successfully"; // uncomment for testing
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to sanitize output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Helper function to get general categories
function getGeneralCategories($conn) {
    $stmt = $conn->query("SELECT * FROM general_categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to get service categories by general category
function getServiceCategoriesByGeneral($conn, $generalCategoryId) {
    $stmt = $conn->prepare("
        SELECT sc.*, gc.name as general_category_name, gc.icon, gc.color
        FROM service_categories sc
        JOIN general_categories gc ON sc.general_category_id = gc.id
        WHERE sc.general_category_id = ? AND sc.is_approved = 1
        ORDER BY sc.name
    ");
    $stmt->execute([$generalCategoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Generate unique referral code
function generateReferralCode($conn, $username) {
    $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $username), 0, 6));
    $code = $baseCode . rand(100, 999);
    
    // Ensure uniqueness
    $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
    $stmt->execute([$code]);
    
    if ($stmt->fetch()) {
        return generateReferralCode($conn, $username . rand(1, 9));
    }
    
    return $code;
}

// Process referral signup
function processReferralSignup($conn, $newUserId, $referralCode) {
    if (!$referralCode) return;
    
    try {
        // Find referrer
        $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$referralCode]);
        $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($referrer) {
            // Create referral record
            $stmt = $conn->prepare("
                INSERT INTO referrals (referrer_id, referred_user_id, referral_code, status, points_earned, created_at)
                VALUES (?, ?, ?, 'completed', ?, GETDATE())
            ");
            $stmt->execute([$referrer['id'], $newUserId, $referralCode, POINTS_PER_SIGNUP]);
            
            // Get the inserted referral ID
            $stmt = $conn->query("SELECT SCOPE_IDENTITY() as id");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $referralId = $result['id'];
            
            // Award points to referrer
            $stmt = $conn->prepare("
                UPDATE users 
                SET referral_points = referral_points + ?, total_referrals = total_referrals + 1
                WHERE id = ?
            ");
            $stmt->execute([POINTS_PER_SIGNUP, $referrer['id']]);
            
            // Create reward record
            $stmt = $conn->prepare("
                INSERT INTO referral_rewards (user_id, referral_id, reward_type, points, description, created_at)
                VALUES (?, ?, 'signup', ?, 'Friend signed up using your referral code', GETDATE())
            ");
            $stmt->execute([$referrer['id'], $referralId, POINTS_PER_SIGNUP]);
            
            // Update new user's referred_by
            $stmt = $conn->prepare("UPDATE users SET referred_by = ? WHERE id = ?");
            $stmt->execute([$referrer['id'], $newUserId]);
            
            // Check for milestone rewards
            checkMilestoneRewards($conn, $referrer['id']);
        }
    } catch(PDOException $e) {
        error_log("Referral processing error: " . $e->getMessage());
    }
}

// Check and award milestone rewards
function checkMilestoneRewards($conn, $userId) {
    try {
        // Get user's total referrals
        $stmt = $conn->prepare("SELECT total_referrals FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return;
        
        // Get applicable milestones
        $stmt = $conn->prepare("
            SELECT * FROM referral_milestones 
            WHERE referrals_required <= ? AND is_active = 1
            ORDER BY referrals_required DESC
        ");
        $stmt->execute([$user['total_referrals']]);
        $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($milestones as $milestone) {
            // Check if already awarded
            $stmt = $conn->prepare("
                SELECT id FROM referral_rewards 
                WHERE user_id = ? AND reward_type = 'milestone' 
                AND description LIKE ?
            ");
            $stmt->execute([$userId, '%' . $milestone['title'] . '%']);
            
            if (!$stmt->fetch()) {
                // Award milestone
                $stmt = $conn->prepare("
                    UPDATE users SET referral_points = referral_points + ? WHERE id = ?
                ");
                $stmt->execute([$milestone['points_reward'], $userId]);
                
                // Create reward record (use latest referral)
                $stmt = $conn->prepare("
                    SELECT TOP 1 id FROM referrals WHERE referrer_id = ? ORDER BY created_at DESC
                ");
                $stmt->execute([$userId]);
                $latestReferral = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($latestReferral) {
                    $stmt = $conn->prepare("
                        INSERT INTO referral_rewards (user_id, referral_id, reward_type, points, description, created_at)
                        VALUES (?, ?, 'milestone', ?, ?, GETDATE())
                    ");
                    $stmt->execute([
                        $userId, 
                        $latestReferral['id'], 
                        $milestone['points_reward'],
                        $milestone['badge_icon'] . ' ' . $milestone['title'] . ': ' . $milestone['description']
                    ]);
                }
            }
        }
    } catch(PDOException $e) {
        error_log("Milestone rewards error: " . $e->getMessage());
    }
}

// Get user's referral stats
function getReferralStats($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT 
            u.referral_code,
            u.referral_points,
            u.total_referrals,
            COUNT(DISTINCT r.id) as completed_referrals,
            ISNULL(SUM(rr.points), 0) as total_points_earned
        FROM users u
        LEFT JOIN referrals r ON u.id = r.referrer_id
        LEFT JOIN referral_rewards rr ON u.id = rr.user_id
        WHERE u.id = ?
        GROUP BY u.id, u.referral_code, u.referral_points, u.total_referrals
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>