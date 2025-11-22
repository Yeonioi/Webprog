<?php
require_once '../config.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$serviceId = $input['service_id'] ?? null;

// For demo, use user ID 1
$userId = 1;

if (!$serviceId) {
    echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
    exit;
}

try {
    // Check if bookmark exists
    $stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND service_id = ?");
    $stmt->execute([$userId, $serviceId]);
    $bookmark = $stmt->fetch();

    if ($bookmark) {
        // Remove bookmark
        $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND service_id = ?");
        $stmt->execute([$userId, $serviceId]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add bookmark
        $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, service_id) VALUES (?, ?)");
        $stmt->execute([$userId, $serviceId]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>