<?php
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');

// Get POST data
$food_id = $_POST['food_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;

// Validate input
if (empty($food_id) || empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing food_id or user_id']);
    exit;
}

// Check if item already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ?");
$stmt->bind_param("ii", $user_id, $food_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + 1;

    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_quantity, $row['id']);
    $success = $update->execute();
    $update->close();
} else {
    // Insert new item
    $insert = $conn->prepare("INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $food_id);
    $success = $insert->execute();
    $insert->close();
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
