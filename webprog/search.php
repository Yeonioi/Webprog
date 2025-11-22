<?php
require_once 'config.php';

$search = $_GET['q'] ?? '';
$categoryId = $_GET['category_id'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';

// Build query
$sql = "SELECT s.*, u.name, u.profile_image, sc.name as category_name, gc.name as general_category_name,
        (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) as review_count
        FROM services s
        JOIN users u ON s.user_id = u.id
        JOIN service_categories sc ON s.service_category_id = sc.id
        JOIN general_categories gc ON sc.general_category_id = gc.id
        WHERE 1=1";

if ($search) {
    $sql .= " AND (s.title LIKE :search OR s.description LIKE :search OR sc.name LIKE :search OR gc.name LIKE :search)";
}
if ($categoryId) {
    $sql .= " AND s.service_category_id = :category_id";
}

// Sorting
switch ($sort) {
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC";
        break;
    case 'distance':
        $sql .= " ORDER BY s.distance ASC";
        break;
    case 'price_low':
        $sql .= " ORDER BY s.rate ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY s.rate DESC";
        break;
    default:
        $sql .= " ORDER BY s.created_at DESC";
}

$stmt = $conn->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
if ($categoryId) {
    $stmt->bindValue(':category_id', $categoryId);
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get general categories for filter
$generalCategories = getGeneralCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Services - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Find Services</h1>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- Filters Sidebar -->
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sticky top-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Filters</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">General Category</label>
                        <select class="w-full p-2 border border-gray-300 rounded-md text-sm" 
                                onchange="window.location.href='category.php?id=' + this.value">
                            <option value="">All Categories</option>
                            <?php foreach ($generalCategories as $gc): ?>
                            <option value="<?= $gc['id'] ?>"><?= e($gc['icon']) ?> <?= e($gc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Distance</label>
                        <input type="range" min="0" max="10" value="5" class="w-full">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>0 mi</span>
                            <span>10 mi</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="space-y-2">
                            <label class="flex items-center text-sm">
                                <input type="checkbox" class="rounded text-blue-600 mr-2">
                                <span>★★★★★ (5.0)</span>
                            </label>
                            <label class="flex items-center text-sm">
                                <input type="checkbox" class="rounded text-blue-600 mr-2">
                                <span>★★★★☆ (4.0+)</span>
                            </label>
                            <label class="flex items-center text-sm">
                                <input type="checkbox" class="rounded text-blue-600 mr-2">
                                <span>★★★☆☆ (3.0+)</span>
                            </label>
                        </div>
                    </div>

                    <button class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 text-sm">Apply Filters</button>
                    <button class="w-full mt-2 text-blue-600 text-sm hover:underline">Clear All</button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-grow">
                <!-- Search Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
                    <form action="search.php" method="GET">
                        <div class="relative">
                            <input type="text" name="q" value="<?= e($search) ?>" 
                                   placeholder="Search for services or providers"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <button type="submit" class="absolute right-2 top-1.5 bg-blue-600 text-white px-4 py-1 rounded-md hover:bg-blue-700">
                                Search
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Results Controls -->
                <div class="flex justify-between items-center mb-4">
                    <p class="text-sm text-gray-600"><?= count($results) ?> results found</p>
                    <select name="sort" onchange="window.location.href='search.php?q=<?= urlencode($search) ?>&sort=' + this.value" 
                            class="p-1.5 text-sm border border-gray-300 rounded bg-white">
                        <option value="relevance" <?= $sort === 'relevance' ? 'selected' : '' ?>>Sort by: Relevance</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Sort by: Rating</option>
                        <option value="distance" <?= $sort === 'distance' ? 'selected' : '' ?>>Sort by: Distance</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Sort by: Price (Low to High)</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Sort by: Price (High to Low)</option>
                    </select>
                </div>

                <!-- Results -->
                <div class="space-y-6">
                    <?php foreach ($results as $service): ?>
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
                                        <span class="text-lg font-semibold text-blue-600"><?= e($service['rate']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($results)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                        <div class="text-gray-400 text-6xl mb-4">🔍</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                        <p class="text-gray-600 mb-4">Try adjusting your search or filters to find what you're looking for.</p>
                        <a href="add-service.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700">
                            Add Your Service
                        </a>
                    </div>
                    <?php endif; ?>
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