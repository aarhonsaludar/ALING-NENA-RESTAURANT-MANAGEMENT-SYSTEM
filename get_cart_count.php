<?php
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');

// Get user_id from query string
$user_id = $_GET['user_id'] ?? 0;

// Validate input
if (empty($user_id)) {
    echo json_encode(['count' => 0]);
    exit;
}

// Query to get the sum of quantities in the cart
$stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $count = $row['count'] ?: 0;
    echo json_encode(['count' => (int)$count]);
} else {
    echo json_encode(['count' => 0]);
}

$stmt->close();
$conn->close();
