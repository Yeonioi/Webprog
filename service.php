<?php
require_once 'config.php';

$serviceId = $_GET['id'] ?? null;

if (!$serviceId) {
    redirect('search.php');
}

// Fetch service details
$sql = "SELECT s.*, 
        u.id as provider_id, u.name as provider_name, u.email, u.phone, u.location as provider_location, 
        u.profile_image, u.bio as provider_bio, u.member_since,
        sc.name as category_name, gc.name as general_category_name, gc.icon, gc.color,
        (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) as review_count
        FROM services s
        JOIN users u ON s.user_id = u.id
        JOIN service_categories sc ON s.service_category_id = sc.id
        JOIN general_categories gc ON sc.general_category_id = gc.id
        WHERE s.id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    redirect('search.php');
}

// Fetch reviews
$stmt = $conn->prepare("
    SELECT r.*, u.name as reviewer_name, u.profile_image as reviewer_image
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.service_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$serviceId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($service['title']) ?> - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <a href="search.php" class="text-blue-600 hover:text-blue-800 flex items-center mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Search
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2">
                
                <!-- Service Image -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <img src="<?= e($service['image']) ?>" alt="<?= e($service['title']) ?>" 
                         class="w-full h-96 object-cover">
                </div>

                <!-- Service Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-block px-3 py-1 <?= e($service['color']) ?> text-sm rounded-full">
                            <?= e($service['icon']) ?> <?= e($service['general_category_name']) ?>
                        </span>
                        <span class="text-gray-400">›</span>
                        <span class="text-sm text-gray-600"><?= e($service['category_name']) ?></span>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= e($service['title']) ?></h1>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center">
                            <span class="text-yellow-400 text-xl">★</span>
                            <span class="ml-1 text-lg font-semibold text-gray-900">
                                <?= number_format($service['avg_rating'], 1) ?>
                            </span>
                            <span class="ml-1 text-gray-500">(<?= $service['review_count'] ?> reviews)</span>
                        </div>
                        <span class="text-gray-400">•</span>
                        <span class="text-gray-600">📍 <?= e($service['distance']) ?></span>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">About This Service</h2>
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(e($service['description'])) ?></p>
                    </div>

                    <div class="mt-6 flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Rate</p>
                            <p class="text-2xl font-bold text-blue-600"><?= e($service['rate']) ?></p>
                        </div>
                        <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            Contact Provider
                        </button>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Reviews (<?= count($reviews) ?>)
                    </h2>

                    <?php if (count($reviews) > 0): ?>
                        <div class="space-y-6">
                            <?php foreach ($reviews as $review): ?>
                            <div class="border-b border-gray-100 pb-6 last:border-0">
                                <div class="flex items-start">
                                    <div class="h-10 w-10 rounded-full overflow-hidden mr-3 flex-shrink-0">
                                        <img src="<?= e($review['reviewer_image']) ?>" alt="<?= e($review['reviewer_name']) ?>"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-semibold text-gray-900"><?= e($review['reviewer_name']) ?></h4>
                                            <div class="flex items-center">
                                                <span class="text-yellow-400">★</span>
                                                <span class="ml-1 font-semibold"><?= $review['rating'] ?></span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-2">
                                            <?= date('F j, Y', strtotime($review['created_at'])) ?>
                                        </p>
                                        <p class="text-gray-700"><?= e($review['comment']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No reviews yet. Be the first to review!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                
                <!-- Provider Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Service Provider</h3>
                    
                    <div class="flex items-center mb-4">
                        <div class="h-16 w-16 rounded-full overflow-hidden mr-4">
                            <img src="<?= e($service['profile_image']) ?>" alt="<?= e($service['provider_name']) ?>"
                                 class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900"><?= e($service['provider_name']) ?></h4>
                            <p class="text-sm text-gray-500">
                                Member since <?= date('Y', strtotime($service['member_since'])) ?>
                            </p>
                        </div>
                    </div>

                    <p class="text-gray-700 text-sm mb-4"><?= e($service['provider_bio']) ?></p>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            <span class="text-gray-700"><?= e($service['provider_location']) ?></span>
                        </div>
                    </div>

                    <button class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium mb-3">
                        Send Message
                    </button>

                    <button class="w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                        Save Service
                    </button>
                </div>

                <!-- Similar Services -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Similar Services</h3>
                    <p class="text-sm text-gray-500">Check out other services in this category</p>
                    <a href="search.php?category_id=<?= $service['service_category_id'] ?>" 
                       class="mt-4 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Browse all →
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>