<?php
require_once 'config.php';

$generalCategoryId = $_GET['id'] ?? null;

if (!$generalCategoryId || !ctype_digit($generalCategoryId)) {
    redirect('home.php');
}

// Fetch general category
$stmt = $conn->prepare("SELECT * FROM general_categories WHERE id = ?");
$stmt->execute([$generalCategoryId]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    redirect('home.php');
}

// Fetch service categories with service count - FIXED: Changed TRUE to 1
$stmt = $conn->prepare("
    SELECT sc.*, gc.name AS general_category_name, gc.icon, gc.color,
           (SELECT COUNT(*) FROM services s WHERE s.service_category_id = sc.id) as service_count
    FROM service_categories sc
    JOIN general_categories gc ON sc.general_category_id = gc.id
    WHERE sc.general_category_id = ? AND sc.is_approved = 1
    ORDER BY sc.name
");
$stmt->execute([$generalCategoryId]);
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="home.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Category Header -->
        <div class="flex items-start mb-10">
            <div class="text-5xl p-4 rounded-full <?= e($category['color']) ?>">
                <?= e($category['icon']) ?>
            </div>
            <div class="ml-6 mt-2">
                <h1 class="text-3xl font-bold text-gray-900"><?= e($category['name']) ?></h1>
                <p class="text-gray-600 mt-1 text-sm leading-relaxed"><?= e($category['description']) ?></p>
            </div>
        </div>

        <!-- Add Custom Service Category -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-10">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Can't find the service you offer?</h3>
                    <p class="text-gray-600 text-sm mt-1">
                        Add your own service category under <strong><?= e($category['name']) ?></strong>.
                    </p>
                </div>

                <a href="add-category.php?general=<?= $generalCategoryId ?>"
                   class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                    + Add Category
                </a>
            </div>
        </div>

        <!-- Service Category List -->
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Service Categories (<?= count($serviceCategories) ?>)
        </h2>

        <?php if (!empty($serviceCategories)): ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <?php foreach ($serviceCategories as $sc): ?>
                <a href="search.php?category_id=<?= $sc['id'] ?>"
                   class="bg-white rounded-lg border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">

                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?= e($sc['name']) ?></h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <?= $sc['service_count'] ?> provider<?= $sc['service_count'] != 1 ? 's' : '' ?>
                            </p>
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

            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="text-7xl mb-6"><?= e($category['icon']) ?></div>

                <h3 class="text-lg font-semibold text-gray-800 mb-2">No service categories yet</h3>
                <p class="text-gray-600 mb-6">
                    Be the first to add a service under this category!
                </p>

                <a href="add-category.php?general=<?= $generalCategoryId ?>"
                   class="px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors font-medium inline-block">
                    + Create First Service
                </a>
            </div>

        <?php endif; ?>

    </div>
</body>
</html>