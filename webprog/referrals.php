<?php
require_once 'config.php';

// For demo, use user ID 1
$userId = 1;

// Get referral stats
$stats = getReferralStats($conn, $userId);

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get referral history
$stmt = $conn->prepare("
    SELECT r.*, u.name, u.email, u.profile_image, u.created_at as join_date
    FROM referrals r
    JOIN users u ON r.referred_user_id = u.id
    WHERE r.referrer_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$userId]);
$referralHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get reward history
$stmt = $conn->prepare("
    SELECT rr.*, r.referred_user_id, u.name as referred_name
    FROM referral_rewards rr
    JOIN referrals r ON rr.referral_id = r.id
    LEFT JOIN users u ON r.referred_user_id = u.id
    WHERE rr.user_id = ?
    ORDER BY rr.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$rewardHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get milestones
$stmt = $conn->query("SELECT * FROM referral_milestones WHERE is_active = TRUE ORDER BY referrals_required");
$milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate next milestone
$nextMilestone = null;
foreach ($milestones as $milestone) {
    if ($milestone['referrals_required'] > $user['total_referrals']) {
        $nextMilestone = $milestone;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Program - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Referral Program</h1>
            <p class="text-gray-600 mt-2">Share FriendLink with friends and earn rewards!</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Total Referrals</p>
                        <p class="text-3xl font-bold mt-2"><?= $user['total_referrals'] ?></p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Points Earned</p>
                        <p class="text-3xl font-bold mt-2"><?= number_format($user['referral_points']) ?></p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Active Referrals</p>
                        <p class="text-3xl font-bold mt-2"><?= $stats['completed_referrals'] ?></p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm">Next Reward</p>
                        <p class="text-xl font-bold mt-2"><?= $nextMilestone ? $nextMilestone['points_reward'] . ' pts' : 'Max Level!' ?></p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Code Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Your Referral Code</h2>
                <p class="text-gray-600 mb-6">Share this code with friends to earn rewards</p>
                
                <div class="flex items-center justify-center gap-4 mb-6">
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 border-2 border-blue-200 rounded-lg px-8 py-4">
                        <span class="text-3xl font-bold text-blue-600 tracking-wider"><?= e($user['referral_code']) ?></span>
                    </div>
                    <button onclick="copyReferralCode()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy Code
                    </button>
                </div>

                <div class="flex justify-center gap-4">
                    <button onclick="shareEmail()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Email
                    </button>
                    <button onclick="shareWhatsApp()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <span class="mr-2">💬</span>
                        WhatsApp
                    </button>
                    <button onclick="shareFacebook()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <span class="mr-2">📘</span>
                        Facebook
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Milestones -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Referral Milestones</h3>
                <div class="space-y-4">
                    <?php foreach ($milestones as $milestone): 
                        $achieved = $user['total_referrals'] >= $milestone['referrals_required'];
                        $progress = min(100, ($user['total_referrals'] / $milestone['referrals_required']) * 100);
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4 <?= $achieved ? 'bg-green-50 border-green-200' : '' ?>">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3"><?= $milestone['badge_icon'] ?></span>
                                <div>
                                    <h4 class="font-semibold text-gray-900"><?= e($milestone['title']) ?></h4>
                                    <p class="text-sm text-gray-600"><?= e($milestone['description']) ?></p>
                                </div>
                            </div>
                            <?php if ($achieved): ?>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">Achieved!</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600"><?= $milestone['referrals_required'] ?> referrals</span>
                            <span class="font-semibold text-blue-600">+<?= number_format($milestone['points_reward']) ?> points</span>
                        </div>
                        <?php if (!$achieved): ?>
                        <div class="mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?= $user['total_referrals'] ?> / <?= $milestone['referrals_required'] ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Referrals -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Referrals</h3>
                <?php if (count($referralHistory) > 0): ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($referralHistory, 0, 5) as $referral): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full overflow-hidden mr-3">
                                <img src="<?= e($referral['profile_image']) ?>" alt="<?= e($referral['name']) ?>" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= e($referral['name']) ?></p>
                                <p class="text-xs text-gray-500">Joined <?= date('M j, Y', strtotime($referral['join_date'])) ?></p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">+<?= $referral['points_earned'] ?> pts</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-500">No referrals yet. Start sharing your code!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- How It Works -->
        <div class="mt-8 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-8 border border-blue-100">
            <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">How Referral Rewards Work</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">1️⃣</span>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Share Your Code</h4>
                    <p class="text-sm text-gray-600">Invite friends using your unique referral code</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">2️⃣</span>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">They Sign Up</h4>
                    <p class="text-sm text-gray-600">When they join using your code, you earn points</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">3️⃣</span>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Unlock Rewards</h4>
                    <p class="text-sm text-gray-600">Reach milestones and earn bonus rewards</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyReferralCode() {
            const code = '<?= $user['referral_code'] ?>';
            navigator.clipboard.writeText(code).then(() => {
                alert('Referral code copied to clipboard!');
            });
        }

        function shareEmail() {
            const code = '<?= $user['referral_code'] ?>';
            const subject = encodeURIComponent('Join me on FriendLink!');
            const body = encodeURIComponent(`Hey! I'm using FriendLink to connect with neighbors for local services. Join using my code ${code} and let's build our community together! https://friendlink.com/signup?ref=${code}`);
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
        }

        function shareWhatsApp() {
            const code = '<?= $user['referral_code'] ?>';
            const text = encodeURIComponent(`Join me on FriendLink! Use code ${code} to sign up: https://friendlink.com/signup?ref=${code}`);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }

        function shareFacebook() {
            const url = encodeURIComponent('https://friendlink.com/signup?ref=<?= $user['referral_code'] ?>');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }
    </script>
</body>
</html>