<?php
header('Content-Type: application/json');
require_once('connect.php');

// Get user_id from query string
$user_id = $_GET['user_id'] ?? 0;

// Validate input
if (empty($user_id)) {
    echo json_encode(['success' => false, 'items' => [], 'grand_total' => 0]);
    exit;
}

// Get cart items
$query = "SELECT c.id as cart_id, c.quantity, f.id as food_id, f.name, f.price, f.image_url, (f.price * c.quantity) as total 
          FROM cart c 
          JOIN food_items f ON c.food_id = f.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);

$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$grand_total = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $grand_total += $row['total'];
    }
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'grand_total' => $grand_total
]);

$stmt->close();
$conn->close();
