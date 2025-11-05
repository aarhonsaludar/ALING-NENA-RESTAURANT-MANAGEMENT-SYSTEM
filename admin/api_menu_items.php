<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "itpMidtermLabExam";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Function to handle image upload
function handleImageUpload($file, $category)
{
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'];
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }

    // Create upload directory if it doesn't exist
    $upload_dir = "../img/" . $category . "/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return relative path for database
        return ['success' => true, 'path' => "img/" . $category . "/" . $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}

try {
    switch ($action) {
        case 'get_all':
            // Get all menu items
            $stmt = $pdo->query("SELECT * FROM food_items ORDER BY id ASC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $items]);
            break;

        case 'get_one':
            // Get single menu item
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM food_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $item]);
            break;

        case 'add':
            // Add new menu item with file upload
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $category = $_POST['category'] ?? 'Other';

            // Handle file upload
            $imageUrl = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = handleImageUpload($_FILES['image'], $category);
                if ($uploadResult['success']) {
                    $imageUrl = $uploadResult['path'];
                } else {
                    echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Image upload is required']);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO food_items (name, description, price, image_url, category) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $price, $imageUrl, $category]);

            echo json_encode([
                'success' => true,
                'message' => 'Menu item added successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'update':
            // Update menu item with optional file upload
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $category = $_POST['category'] ?? 'Other';

            // Get current image URL
            $stmt = $pdo->prepare("SELECT image_url FROM food_items WHERE id = ?");
            $stmt->execute([$id]);
            $currentItem = $stmt->fetch(PDO::FETCH_ASSOC);
            $imageUrl = $currentItem['image_url'];

            // Handle file upload if new file is provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = handleImageUpload($_FILES['image'], $category);
                if ($uploadResult['success']) {
                    // Delete old image file if it exists
                    if ($imageUrl && file_exists('../' . $imageUrl)) {
                        unlink('../' . $imageUrl);
                    }
                    $imageUrl = $uploadResult['path'];
                } else {
                    echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                    exit;
                }
            }

            $stmt = $pdo->prepare("
                UPDATE food_items 
                SET name = ?, description = ?, price = ?, image_url = ?, category = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $price, $imageUrl, $category, $id]);

            echo json_encode(['success' => true, 'message' => 'Menu item updated successfully']);
            break;

        case 'delete':
            // Delete menu item
            $id = $_POST['id'] ?? 0;

            // Check if item is in any orders
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE food_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete item that has been ordered. Consider hiding it instead.'
                ]);
                break;
            }

            // Get image path before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM food_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM food_items WHERE id = ?");
            $stmt->execute([$id]);

            // Delete image file if it exists
            if ($item && $item['image_url'] && file_exists('../' . $item['image_url'])) {
                unlink('../' . $item['image_url']);
            }

            echo json_encode(['success' => true, 'message' => 'Menu item deleted successfully']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
