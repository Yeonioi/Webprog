<?php
require_once 'config.php';

// Get logged-in user
$currentUser = getCurrentUser($conn);
$userId = $currentUser ? $currentUser['id'] : null;

if (!$userId) {
    redirect('login.php');
}

$categoryId = $_GET['category_id'] ?? null;
$message = '';
$messageType = '';

/* ---------------------------------------------------------
   Handle Form Submission
--------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $serviceCategoryId = $_POST['service_category_id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $rate = trim($_POST['rate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $distance = trim($_POST['distance'] ?? '');
    $image = trim($_POST['image'] ?? '');

    if (!$image) {
        $image = "https://images.unsplash.com/photo-1556761175-b413da4baf72";
    }

    if ($title && $serviceCategoryId && $description && $rate) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO services 
                    (user_id, service_category_id, title, description, rate, location, distance, image, created_at)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())
            ");

            $stmt->execute([
                $userId,
                $serviceCategoryId,
                $title,
                $description,
                $rate,
                $location,
                $distance,
                $image
            ]);

            $message = "Service added successfully!";
            $messageType = "success";

            header("Location: profile.php");
            exit;

        } catch (PDOException $e) {
            error_log("Add-service error: " . $e->getMessage());
            $message = "Error adding service. Please try again.";
            $messageType = "error";
        }
    } else {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    }
}

/* ---------------------------------------------------------
   Fetch General Categories & Service Categories (SQL Server)
--------------------------------------------------------- */
$stmt = $conn->query("
    SELECT 
        gc.id, gc.name, gc.icon, gc.color, gc.description,
        STRING_AGG(CONCAT(sc.id, ':', sc.name), '||') AS service_categories
    FROM general_categories gc
    LEFT JOIN service_categories sc 
        ON gc.id = sc.general_category_id 
       AND sc.is_approved = 1
    GROUP BY gc.id, gc.name, gc.icon, gc.color, gc.description
    ORDER BY gc.name
");

$categoriesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------------------------
   Fetch User Location
--------------------------------------------------------- */
$stmt = $conn->prepare("SELECT location FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userLocation = $user['location'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Back Button -->
        <a href="home.php" class="text-blue-600 hover:text-blue-800 flex items-center mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Home
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Add Your Service</h1>
            <p class="text-gray-600 mb-6">Share your skills with the community and start earning.</p>

            <!-- Message Box -->
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-md 
                    <?= $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="add-service.php<?= $categoryId ? '?category_id=' . $categoryId : '' ?>">

                <!-- Category -->
                <div class="mb-6">
                    <label for="service_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Category <span class="text-red-500">*</span>
                    </label>
                    <select id="service_category_id" name="service_category_id" required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:outline-none">
                        
                        <option value="">Select a service category...</option>

                        <?php foreach ($categoriesData as $gc): ?>
                            <?php if ($gc['service_categories']): ?>
                                <optgroup label="<?= e($gc['icon']) ?> <?= e($gc['name']) ?>">
                                    <?php
                                    $services = explode('||', $gc['service_categories']);
                                    foreach ($services as $svc):
                                        list($id, $name) = explode(':', $svc);
                                    ?>
                                        <option value="<?= $id ?>" <?= ($categoryId == $id ? 'selected' : '') ?>>
                                            <?= e($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>

                    </select>

                    <p class="text-xs text-gray-500 mt-2">
                        Don’t see your category?
                        <a href="add-category.php" class="text-blue-600 hover:underline">Create one</a>
                    </p>
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required
                        placeholder="e.g., Professional Piano Teacher with 10+ Years Experience"
                        class="w-full p-3 border rounded-lg focus:ring-blue-500">
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="5" required
                        placeholder="Describe your service and experience…"
                        class="w-full p-3 border rounded-lg focus:ring-blue-500"></textarea>
                </div>

                <!-- Rate + Distance -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rate <span class="text-red