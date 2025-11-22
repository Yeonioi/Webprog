<?php
require_once 'config.php';

// For demo, use user ID 1
$userId = 1;

$search = $_GET['q'] ?? '';

// Fetch bookmarked services
$sql = "SELECT s.*, u.name, u.profile_image, sc.name as category_name, gc.name as general_category_name,
        (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) as review_count
        FROM services s
        JOIN users u ON s.user_id = u.id
        JOIN service_categories sc ON s.service_category_id = sc.id
        JOIN general_categories gc ON sc.general_category_id = gc.id
        JOIN bookmarks b ON s.id = b.service_id
        WHERE b.user_id = ?";

if ($search) {
    $sql .= " AND (s.title LIKE ? OR sc.name LIKE ? OR s.description LIKE ?)";
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if ($search) {
    $searchTerm = "%$search%";
    $stmt->execute([$userId, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt->execute([$userId]);
}
$bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookmarks - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center mb-6">
            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-900">My Bookmarks</h1>
            <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full"><?= count($bookmarks) ?></span>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
            <form action="bookmarks.php" method="GET">
                <div class="relative">
                    <input type="text" name="q" value="<?= e($search) ?>" 
                           placeholder="Search your bookmarks"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </form>
        </div>

        <!-- Bookmarked Services -->
        <?php if (count($bookmarks) > 0): ?>
        <div class="space-y-6">
            <?php foreach ($bookmarks as $service): ?>
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
                            <button class="bookmark-btn text-blue-600 hover:text-red-600 transition-colors" data-service-id="<?= $service['id'] ?>">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
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
                                <span class="text-lg font-semibold text-blue-600"><?= e($service['rate']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="flex justify-center mb-4">
                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center">
                    <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                <?= $search ? 'No matching bookmarks found' : 'No bookmarks yet' ?>
            </h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">
                <?= $search 
                    ? "Try adjusting your search query to find what you're looking for." 
                    : "When you find services you like, click the bookmark icon to save them here for quick access." 
                ?>
            </p>
            <?php if (!$search): ?>
            <a href="search.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
                Browse Services
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const serviceId = this.dataset.serviceId;
                const card = this.closest('.bg-white');
                
                fetch('ajax/toggle-bookmark.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({service_id: serviceId})
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        // Fade out and remove the card
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            // Reload if no bookmarks left
                            if(document.querySelectorAll('.bookmark-btn').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                });
            });
        });
    </script>
</body>
</html>