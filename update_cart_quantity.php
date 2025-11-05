<?php
header('Content-Type: application/json');
require_once('connect.php');

$cart_id = $_POST['cart_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;

if (!$cart_id || !$quantity) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
$stmt->bind_param("ii", $quantity, $cart_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
