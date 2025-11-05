<?php
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');

// Query to get all food items including category
$query = "SELECT id, name, description, price, image_url, category FROM food_items ORDER BY category, name";
$result = $conn->query($query);

$items = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'desc' => $row['description'],
            'price' => (float)$row['price'],
            'image' => $row['image_url'],
            'category' => $row['category'],
            'rating' => round(mt_rand(30, 50) / 10, 1), // Random rating 3.0-5.0
            'ratingCount' => mt_rand(50, 550) // Random rating count 50-550
        ];
    }
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
$conn->close();
