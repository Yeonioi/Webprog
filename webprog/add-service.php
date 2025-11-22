<?php
require_once 'config.php';

// For demo, use user ID 1
$userId = 1;

$categoryId = $_GET['category_id'] ?? null;
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $serviceCategoryId = $_POST['service_category_id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $rate = trim($_POST['rate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $distance = trim($_POST['distance'] ?? '');
    $image = trim($_POST['image'] ?? 'https://images.unsplash.com/photo-1556761175-b413da4baf72');
    
    if ($title && $serviceCategoryId && $description && $rate) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO services (user_id, service_category_id, title, description, rate, location, distance, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $serviceCategoryId, $title, $description, $rate, $location, $distance, $image]);
            
            $message = 'Service added successfully!';
            $messageType = 'success';
            
            header("refresh:2;url=profile.php");
        } catch (PDOException $e) {
            $message = 'Error adding service. Please try again.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    }
}

// Fetch general categories with their service categories
$stmt = $conn->query("
    SELECT gc.*, 
           GROUP_CONCAT(CONCAT(sc.id, ':', sc.name) SEPARATOR '||') as service_categories
    FROM general_categories gc
    LEFT JOIN service_categories sc ON gc.id = sc.general_category_id AND sc.is_approved = TRUE
    GROUP BY gc.id
    ORDER BY gc.name
");
$categoriesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info for location
$stmt = $conn->prepare("SELECT location FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
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
        <a href="home.php" class="text-blue-600 hover:text-blue-800 flex items-center mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Home
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Add Your Service</h1>
                <p class="text-gray-600 mt-2">Share your skills with the community and start earning</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?= $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                <?= e($message) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="add-service.php<?= $categoryId ? '?category_id=' . $categoryId : '' ?>">
                <!-- Service Category -->
                <div class="mb-6">
                    <label for="service_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Category <span class="text-red-500">*</span>
                    </label>
                    <select name="service_category_id" id="service_category_id" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a service category...</option>
                        <?php foreach ($categoriesData as $gc): 
                            if ($gc['service_categories']): ?>
                        <optgroup label="<?= e($gc['icon']) ?> <?= e($gc['name']) ?>">
                            <?php 
                            $services = explode('||', $gc['service_categories']);
                            foreach ($services as $svc):
                                list($id, $name) = explode(':', $svc);
                            ?>
                            <option value="<?= $id ?>" <?= $categoryId == $id ? 'selected' : '' ?>><?= e($name) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                            <?php endif;
                        endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">
                        Don't see your category? 
                        <a href="add-category.php" class="text-blue-600 hover:underline">Create a new one</a>
                    </p>
                </div>

                <!-- Service Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                           placeholder="e.g., Professional Piano Teacher with 10+ Years Experience"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" rows="5" required
                              placeholder="Describe your service, experience, qualifications, and what makes you unique..."
                              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Rate -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="rate" class="block text-sm font-medium text-gray-700 mb-2">
                            Rate <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="rate" name="rate" required
                               placeholder="e.g., $30/hour or $50/session"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="distance" class="block text-sm font-medium text-gray-700 mb-2">
                            Service Area
                        </label>
                        <input type="text" id="distance" name="distance"
                               placeholder="e.g., 5 miles radius"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Location -->
                <div class="mb-6">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                        Location
                    </label>
                    <input type="text" id="location" name="location" 
                           value="<?= e($user['location'] ?? '') ?>"
                           placeholder="Your general location"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Image URL (simplified for demo) -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Image URL (optional)
                    </label>
                    <input type="url" id="image" name="image"
                           placeholder="https://example.com/image.jpg"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-2">Add a professional photo or leave blank for default</p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="home.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Add Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>