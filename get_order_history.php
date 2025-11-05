<?php
header('Content-Type: application/json');
require_once('connect.php');

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 20
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Get order items
    $itemStmt = $conn->prepare("
        SELECT food_name, quantity, price, subtotal 
        FROM order_items 
        WHERE order_id = ?
    ");
    $itemStmt->bind_param("i", $row['id']);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    $items = [];
    while ($item = $itemResult->fetch_assoc()) {
        $items[] = $item;
    }
    $itemStmt->close();

    $row['items'] = $items;
    $orders[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'orders' => $orders
]);
