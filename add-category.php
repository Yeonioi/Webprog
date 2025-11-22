<?php
require_once 'config.php';

// For demo, use user ID 1
$userId = 1;

$generalId = $_GET['general'] ?? null;
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name'] ?? '');
    $generalCategoryId = $_POST['general_category_id'] ?? null;
    
    if ($categoryName && $generalCategoryId) {
        try {
            // Check if category already exists
            $stmt = $conn->prepare("SELECT id FROM service_categories WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$categoryName]);
            
            if ($stmt->fetch()) {
                $message = 'This service category already exists!';
                $messageType = 'error';
            } else {
                // Insert new category
                $stmt = $conn->prepare("INSERT INTO service_categories (general_category_id, name, created_by) VALUES (?, ?, ?)");
                $stmt->execute([$generalCategoryId, $categoryName, $userId]);
                
                $message = 'Service category created successfully!';
                $messageType = 'success';
                
                // Redirect to add service page
                $newCategoryId = $conn->lastInsertId();
                header("refresh:2;url=add-service.php?category_id=$newCategoryId");
            }
        } catch (PDOException $e) {
            $message = 'Error creating category. Please try again.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    }
}

// Fetch general categories
$generalCategories = getGeneralCategories($conn);

// If general category is pre-selected, fetch its info
$selectedCategory = null;
if ($generalId) {
    $stmt = $conn->prepare("SELECT * FROM general_categories WHERE id = ?");
    $stmt->execute([$generalId]);
    $selectedCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service Category - FriendLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="<?= $generalId ? 'category.php?id=' . $generalId : 'home.php' ?>" class="text-blue-600 hover:text-blue-800 flex items-center mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Create a Service Category</h1>
                <p class="text-gray-600 mt-2">Add a custom service category that matches your unique skills</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?= $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                <?= e($message) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="add-category.php<?= $generalId ? '?general=' . $generalId : '' ?>">
                <!-- General Category Selection -->
                <div class="mb-6">
                    <label for="general_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        General Category <span class="text-red-500">*</span>
                    </label>
                    <?php if ($selectedCategory): ?>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="text-3xl <?= e($selectedCategory['color']) ?> p-2 rounded-lg"><?= e($selectedCategory['icon']) ?></span>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900"><?= e($selectedCategory['name']) ?></p>
                            <p class="text-sm text-gray-600"><?= e($selectedCategory['description']) ?></p>
                        </div>
                    </div>
                    <input type="hidden" name="general_category_id" value="<?= $selectedCategory['id'] ?>">
                    <?php else: ?>
                    <select name="general_category_id" id="general_category_id" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a general category...</option>
                        <?php foreach ($generalCategories as $gc): ?>
                        <option value="<?= $gc['id'] ?>"><?= e($gc['icon']) ?> <?= e($gc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>

                <!-- Service Category Name -->
                <div class="mb-6">
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Service Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="category_name" name="category_name" required
                           placeholder="e.g., Piano Lessons, Web Design, Dog Training"
                           class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-2">Be specific and descriptive so others can find your service easily</p>
                </div>

                <!-- Examples Section -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">💡 Examples of Good Service Categories:</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>Education:</strong> SAT Tutoring, Piano Lessons, Spanish Language Classes</li>
                        <li>• <strong>Technology:</strong> WordPress Setup, Mobile App Development, IT Consulting</li>
                        <li>• <strong>Home & Garden:</strong> Deck Building, Pool Maintenance, Interior Painting</li>
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="<?= $generalId ? 'category.php?id=' . $generalId : 'home.php' ?>" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Create Category
                    </button>
                </div>
            </form>

            <!-- Info Box -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">What happens next?</h3>
                <ol class="text-sm text-gray-600 space-y-2">
                    <li>1. Your service category will be created instantly</li>
                    <li>2. You'll be redirected to add your service details</li>
                    <li>3. Other users can also use this category for their services</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>