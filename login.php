<?php
require_once 'config.php';

$message = '';
$messageType = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        try {
            // Fetch user by email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify password against bcrypt hash in 'password' column
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                session_regenerate_id(true); // prevent session fixation

                redirect('home.php');
            } else {
                $message = 'Invalid email or password.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $message = 'Error logging in. Please try again.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please enter both email and password.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-md p-8">
        <h1 class="text-2xl font-bold text-gray-900 text-center mb-6">Login to FriendLink</h1>

        <!-- Status Message -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?= $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Login
            </button>
        </form>

        <!-- Extra Links -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Don't have an account? <a href="register.php" class="text-blue-600 hover:text-blue-800">Sign up</a></p>
        </div>
    </div>

</body>
</html>
