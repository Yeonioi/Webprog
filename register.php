<?php
require_once 'config.php';

$message = '';
$messageType = '';
$referralCode = $_GET['ref'] ?? '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $referredBy = trim($_POST['referral_code'] ?? '');

    // Validation
    if (!$name || !$email || !$password) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $message = 'Email already registered.';
                $messageType = 'error';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Generate unique referral code
                $userReferralCode = generateReferralCode($conn, $name);
                
                // Insert user
                $sql = "INSERT INTO users (name, email, password, phone, location, referral_code, created_at, updated_at, member_since) 
                        VALUES (?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), GETDATE())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $email, $hashedPassword, $phone, $location, $userReferralCode]);
                
                // Get new user ID
                $newUserId = $conn->query("SELECT SCOPE_IDENTITY() AS id")->fetch()['id'];
                
                // Process referral if code provided
                if ($referredBy) {
                    processReferralSignup($conn, $newUserId, $referredBy);
                }
                
                // Auto-login
                $_SESSION['user_id'] = $newUserId;
                
                redirect('home.php');
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $message = 'Error creating account. Please try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12">

    <div class="w-full max-w-md bg-white rounded-xl shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold">
                <span class="text-blue-600">Friend</span><span class="text-purple-600">Link</span>
            </h1>
            <p class="text-gray-600 mt-2">Join your community today</p>
        </div>

        <?php if ($referralCode): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">
                🎉 You've been invited! Code: <strong><?= e($referralCode) ?></strong>
            </p>
        </div>
        <?php endif; ?>

        <!-- Status Message -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?= $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="register.php<?= $referralCode ? '?ref=' . urlencode($referralCode) : '' ?>" class="space-y-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input type="text" name="name" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" name="phone"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" placeholder="City, State"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Referral Code (Optional)</label>
                <input type="text" name="referral_code" value="<?= e($referralCode) ?>"
                       placeholder="Enter referral code"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Have a friend's referral code? Enter it here!</p>
            </div>

            <button type="submit"
                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Create Account
            </button>
        </form>

        <!-- Extra Links -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Already have an account? <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">Sign in</a></p>
        </div>
    </div>

</body>
</html>