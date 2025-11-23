<?php
require_once 'config.php';

// For demo, use user ID 1
$userId = 1;

// Get user's referral stats
$userStats = getReferralStats($conn, $userId);

// Fetch general categories with service count
$stmt = $conn->query("
    SELECT gc.*, 
           (SELECT COUNT(*) FROM service_categories sc WHERE sc.general_category_id = gc.id AND sc.is_approved = 1) as service_count
    FROM general_categories gc
    ORDER BY gc.name
");
$generalCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch featured services - FIXED for SQL Server
$stmt = $conn->query("
    SELECT TOP 3 s.*, u.name, u.profile_image, sc.name as category_name, gc.name as general_category_name,
           (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) as review_count
    FROM services s
    JOIN users u ON s.user_id = u.id
    JOIN service_categories sc ON s.service_category_id = sc.id
    JOIN general_categories gc ON sc.general_category_id = gc.id
    WHERE s.is_featured = 1
    ORDER BY s.created_at DESC
");
$featuredServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Welcome to FriendLink</h1>
            <p class="mt-2 text-gray-600">Find trusted neighbors to help with tasks and services in your community.</p>
            <form action="search.php" method="GET" class="mt-6 relative">
                <input type="text" name="q" placeholder="What service do you need help with?" 
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button type="submit" class="absolute right-2 top-2 bg-blue-600 text-white px-4 py-1.5 rounded-full hover:bg-blue-700 transition-colors">
                    Search
                </button>
            </form>
        </div>

        <!-- Referral Promotion Banner -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-xl font-bold mb-2 flex items-center">
                            <span class="text-2xl mr-2">🎁</span>
                            Invite Friends & Earn Rewards!
                        </h3>
                        <p class="text-blue-100">Share FriendLink with friends and earn points for every signup. Unlock exclusive rewards!</p>
                        <div class="mt-3 flex items-center gap-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="font-semibold"><?= $userStats['total_referrals'] ?> Referrals</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                <span class="font-semibold"><?= number_format($userStats['referral_points']) ?> Points</span>
                            </div>
                        </div>
                    </div>
                    <a href="referrals.php" class="px-6 py-3 bg-white text-blue-600 rounded-full hover:bg-blue-50 transition-colors font-semibold whitespace-nowrap shadow-md">
                        View Referral Dashboard →
                    </a>
                </div>
            </div>
        </div>

        <!-- General Categories -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Browse by Category</h2>
                <a href="categories.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                    View all
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <?php foreach ($generalCategories as $cat): ?>
                <a href="category.php?id=<?= $cat['id'] ?>" 
                   class="flex flex-col items-center p-4 bg-white rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <span class="text-3xl p-3 rounded-full <?= e($cat['color']) ?>"><?= e($cat['icon']) ?></span>
                    <span class="mt-2 text-sm font-medium text-gray-700 text-center"><?= e($cat['name']) ?></span>
                    <span class="mt-1 text-xs text-gray-500"><?= $cat['service_count'] ?> services</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Action: Add Service -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Offer Your Services</h3>
                        <p class="text-gray-600 mt-1">Share your skills with the community and earn money doing what you love.</p>
                    </div>
                    <a href="add-service.php" class="px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                        + Add Service
                    </a>
                </div>
            </div>
        </div>

        <!-- Featured Services -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Featured Services</h2>
                </div>
                <a href="search.php?sort=featured" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                    View all
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($featuredServices as $service): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="md:flex">
                        <div class="md:w-48 h-48 md:h-auto">
                            <img src="<?= e($service['image']) ?>" alt="<?= e($service['title']) ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900"><?= e($service['name']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= e($service['title']) ?></p>
                                </div>
                                <button class="bookmark-btn text-gray-400 hover:text-blue-600" data-service-id="<?= $service['id'] ?>">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full"><?= e($service['general_category_name']) ?></span>
                                <span class="text-xs text-gray-500">›</span>
                                <span class="text-xs text-gray-600"><?= e($service['category_name']) ?></span>
                            </div>
                            <p class="mt-3 text-gray-700"><?= e($service['description']) ?></p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="text-yellow-400">★</span>
                                    <span class="ml-1 text-sm font-medium text-gray-900"><?= number_format($service['avg_rating'], 1) ?></span>
                                    <span class="ml-1 text-sm text-gray-500">(<?= $service['review_count'] ?> reviews)</span>
                                    <span class="ml-4 text-sm text-gray-500">📍 <?= e($service['distance']) ?></span>
                                </div>
                                <div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activity & Community -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                </div>
                <div class="space-y-4">
                    <?php for($i = 1; $i <= 3; $i++): ?>
                    <div class="flex items-center p-3 border-b border-gray-100 last:border-0">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 flex-shrink-0"></div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">New service added in your area</p>
                            <p class="text-xs text-gray-500"><?= $i * 2 ?> hours ago</p>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-900">Community Highlights</h2>
                </div>
                <div class="space-y-4">
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-800">🎉 100+ active service providers</p>
                        <p class="text-xs text-gray-600 mt-1">Join our growing community</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-800">⭐ 500+ satisfied customers</p>
                        <p class="text-xs text-gray-600 mt-1">Highly rated services</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-800">🚀 Growing through referrals</p>
                        <p class="text-xs text-gray-600 mt-1">
                            <a href="referrals.php" class="text-green-700 hover:underline">Invite friends now →</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const serviceId = this.dataset.serviceId;
                fetch('ajax/toggle-bookmark.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({service_id: serviceId})
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        this.classList.toggle('text-blue-600');
                    }
                });
            });
        });
    </script>
</body>
</html>