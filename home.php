<?php
require_once "config.php";

// Check user login
if (!isLoggedIn()) {
    redirect("login.php");
}

$userId = $_SESSION['user_id'];

// ---------------------------------------------
//  FETCH GENERAL CATEGORIES
// ---------------------------------------------
$categoriesQuery = "
    SELECT 
        gc.*, 
        (
            SELECT COUNT(*) 
            FROM service_categories sc 
            WHERE sc.general_category_id = gc.id 
              AND sc.is_approved = 1
        ) AS service_count
    FROM general_categories gc
    ORDER BY gc.name
";
$stmt = $conn->query($categoriesQuery);
$generalCategories = $stmt->fetchAll();

// ---------------------------------------------
//  FETCH FEATURED SERVICES (TOP 3)
// ---------------------------------------------
$featuredQuery = "
    SELECT TOP 3
        s.*, 
        u.name AS provider_name, 
        u.profile_image,
        sc.name AS category_name,
        gc.name AS general_category_name,
        ISNULL((SELECT AVG(rating) FROM reviews WHERE service_id = s.id), 0) AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) AS review_count
    FROM services s
    JOIN users u ON s.user_id = u.id
    JOIN service_categories sc ON s.service_category_id = sc.id
    JOIN general_categories gc ON sc.general_category_id = gc.id
    WHERE s.is_featured = 1
    ORDER BY s.created_at DESC
";
$stmt = $conn->query($featuredQuery);
$featuredServices = $stmt->fetchAll();

// ---------------------------------------------
//  FETCH NEWEST SERVICES (TOP 6)
// ---------------------------------------------
$newServicesQuery = "
    SELECT TOP 6
        s.*, 
        u.name AS provider_name, 
        u.profile_image,
        sc.name AS category_name,
        gc.name AS general_category_name,
        ISNULL((SELECT AVG(rating) FROM reviews WHERE service_id = s.id), 0) AS avg_rating
    FROM services s
    JOIN users u ON s.user_id = u.id
    JOIN service_categories sc ON s.service_category_id = sc.id
    JOIN general_categories gc ON sc.general_category_id = gc.id
    ORDER BY s.created_at DESC
";
$stmt = $conn->query($newServicesQuery);
$newServices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- HEADER -->
<?php include "includes/header.php"; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- FEATURED SERVICES -->
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Services</h2>
    <?php if (count($featuredServices) === 0): ?>
        <p class="text-gray-600">No featured services yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredServices as $service): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <img src="<?= e($service['image'] ?? 'assets/default.jpg'); ?>" alt=""
                         class="w-full h-40 object-cover rounded-lg mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?= e($service['title']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1">
                        <?= e($service['category_name']); ?> • <?= e($service['general_category_name']); ?>
                    </p>
                    <p class="text-sm text-yellow-600 mt-2">
                        ⭐ <?= number_format($service['avg_rating'], 1); ?> (<?= $service['review_count']; ?> reviews)
                    </p>
                    <a href="service.php?id=<?= $service['id']; ?>"
                       class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        View Service
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- GENERAL CATEGORIES -->
    <h2 class="text-2xl font-bold text-gray-900 mt-12 mb-6">Browse by Category</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($generalCategories as $category): ?>
            <a class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center hover:shadow-md transition-shadow"
               href="category.php?id=<?= $category['id']; ?>">
                <div class="text-4xl mb-3"><?= e($category['icon']); ?></div>
                <h3 class="text-lg font-semibold text-gray-900"><?= e($category['name']); ?></h3>
                <p class="text-sm text-gray-600"><?= $category['service_count']; ?> services</p>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- NEWEST SERVICES -->
    <h2 class="text-2xl font-bold text-gray-900 mt-12 mb-6">Newest Services</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($newServices as $service): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <img src="<?= e($service['image'] ?? 'assets/default.jpg'); ?>" alt=""
                     class="w-full h-40 object-cover rounded-lg mb-4">
                <h3 class="text-lg font-semibold text-gray-900"><?= e($service['title']); ?></h3>
                <p class="text-sm text-gray-600 mt-1">
                    <?= e($service['category_name']); ?> • <?= e($service['general_category_name']); ?>
                </p>
                <p class="text-sm text-yellow-600 mt-2">⭐ <?= number_format($service['avg_rating'], 1); ?></p>
                <a href="service.php?id=<?= $service['id']; ?>"
                   class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    View Service
                </a>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- FOOTER -->
<?php include "includes/footer.php"; ?>

</body>
</html>
