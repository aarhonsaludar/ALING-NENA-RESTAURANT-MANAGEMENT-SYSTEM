<?php
session_start();
header('Content-Type: application/json');

require_once('connect.php');

// Get user_id from query parameter
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

// Delete all cart items for this user
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
}

$stmt->close();
$conn->close();
