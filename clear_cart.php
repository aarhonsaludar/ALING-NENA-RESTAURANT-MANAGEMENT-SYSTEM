<?php
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');

// Get user_id
$user_id = $_POST['user_id'] ?? 0;

// Validate input
if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

// Delete all cart items for the user
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
