<?php
require_once 'config.php';

// For demo, use user ID 1
$userId = 1;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Profile Header -->
            <div class="relative h-40 bg-gradient-to-r from-blue-500 via-purple-500 to-blue-600">
                <a href="edit-profile.php" class="absolute top-4 right-4 bg-white bg-opacity-90 p-2 rounded-full hover:bg-opacity-100 transition-opacity">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                </a>
            </div>

            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-end -mt-16 mb-6">
                    <div class="h-32 w-32 rounded-full border-4 border-white overflow-hidden shadow-md">
                        <?php if ($user['profile_image']): ?>
                        <img src="<?= e($user['profile_image']) ?>" alt="<?= e($user['name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                        <div class="h-full w-full bg-gradient-to-br from-blue-400 to-purple-400 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 sm:mt-0 sm:ml-6">
                        <h1 class="text-2xl font-bold text-gray-900"><?= e($user['name']) ?></h1>
                        <div class="flex items-center mt-1 text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span><?= e($user['location']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Contact and Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="col-span-2">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">About</h2>
                        <p class="text-gray-700"><?= e($user['bio']) ?></p>
                        
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-gray-700"><?= e($user['email']) ?></span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="text-gray-700"><?= e($user['phone']) ?></span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-gray-700">Member since <?= date('F Y', strtotime($user['member_since'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="edit-profile.php" class="flex items-center p-2 bg-white rounded-md hover:shadow transition-shadow">
                                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                <span>Edit Profile</span>
                            </a>
                            <a href="add-service.php" class="flex items-center p-2 bg-white rounded-md hover:shadow transition-shadow">
                                <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Add Service</span>
                            </a>
                            <a href="bookmarks.php" class="flex items-center p-2 bg-white rounded-md hover:shadow transition-shadow">
                                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <span>My Bookmarks</span>
                            </a>
                            <a href="messages.php" class="flex items-center p-2 bg-white rounded-md hover:shadow transition-shadow">
                                <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span>Messages</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
                    <div class="border rounded-lg divide-y">
                        <?php
                        $activities = [
                            ['type' => 'message', 'with' => 'Alex Johnson', 'date' => '2 days ago', 'preview' => 'Thanks for your help!'],
                            ['type' => 'booking', 'with' => 'Sarah Miller', 'date' => '1 week ago', 'service' => 'Lawn Care'],
                            ['type' => 'review', 'for' => 'Michael Rodriguez', 'date' => '2 weeks ago', 'rating' => 5]
                        ];
                        foreach ($activities as $activity): ?>
                        <div class="p-4 flex items-start">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center flex-shrink-0">
                                <?php if ($activity['type'] === 'message'): ?>
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <?php elseif ($activity['type'] === 'booking'): ?>
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <?php else: ?>
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php if ($activity['type'] === 'message'): ?>
                                        Message with <?= e($activity['with']) ?>
                                    <?php elseif ($activity['type'] === 'booking'): ?>
                                        Booked <?= e($activity['service']) ?> with <?= e($activity['with']) ?>
                                    <?php else: ?>
                                        Reviewed <?= e($activity['for']) ?> - <?= $activity['rating'] ?>/5 stars
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1"><?= e($activity['date']) ?></p>
                                <?php if (isset($activity['preview'])): ?>
                                <p class="text-sm text-gray-700 mt-1">"<?= e($activity['preview']) ?>"</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>