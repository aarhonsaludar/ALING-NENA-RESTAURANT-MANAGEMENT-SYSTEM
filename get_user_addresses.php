<?php
header('Content-Type: application/json');
require_once('connect.php');

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

// Get user's saved addresses
$stmt = $conn->prepare("
    SELECT * FROM user_addresses 
    WHERE user_id = ?
    ORDER BY is_default DESC, created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'addresses' => $addresses
]);
