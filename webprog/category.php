<?php
require_once 'config.php';

$categoryId = $_GET['id'] ?? null;

if (!$categoryId) {
    redirect('home.php');
}

// Fetch general category
$stmt = $conn->prepare("SELECT * FROM general_categories WHERE id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    redirect('home.php');
}

// Fetch service categories
$stmt = $conn->prepare("
    SELECT sc.*, COUNT(s.id) as service_count
    FROM service_categories sc
    LEFT JOIN services s ON sc.id = s.service_category_id
    WHERE sc.general_category_id = ? AND sc.is_approved = TRUE
    GROUP BY sc.id
    ORDER BY sc.name
");
$stmt->execute([$categoryId]);
$serviceCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($category['name']) ?> - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="home.php" class="text-blue-600 hover:text-blue-800 flex items-center mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
            <div class="flex items-center">
                <span class="text-4xl p-4 rounded-full <?= e($category['color']) ?>"><?= e($category['icon']) ?></span>
                <div class="ml-4">
                    <h1 class="text-3xl font-bold text-gray-900"><?= e($category['name']) ?></h1>
                    <p class="text-gray-600 mt-1"><?= e($category['description']) ?></p>
                </div>
            </div>
        </div>

        <!-- Add New Service Category -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Don't see your service?</h3>
                    <p class="text-gray-600 text-sm mt-1">Create a custom service category that matches your skills</p>
                </div>
                <a href="add-category.php?general=<?= $categoryId ?>" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                    + Add Category
                </a>
            </div>
        </div>

        <!-- Service Categories -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Available Services (<?= count($serviceCategories) ?>)</h2>
            
            <?php if (count($serviceCategories) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($serviceCategories as $sc): ?>
                <a href="search.php?category_id=<?= $sc['id'] ?>" 
                   class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900"><?= e($sc['name']) ?></h3>
                            <p class="text-sm text-gray-500 mt-1"><?= $sc['service_count'] ?> provider<?= $sc['service_count'] != 1 ? 's' : '' ?> available</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    <div class="mt-4">
                        <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">
                            Browse services →
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="text-6xl mb-4"><?= e($category['icon']) ?></div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No services yet in this category</h3>
                <p class="text-gray-600 mb-6">Be the first to add a service category!</p>
                <a href="add-category.php?general=<?= $categoryId ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors font-medium">
                    + Create First Service
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>