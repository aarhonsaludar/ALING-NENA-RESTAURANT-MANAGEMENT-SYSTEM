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

// Simple admin check - you can enhance this with proper admin authentication
// For now, we'll allow access for testing (comment out to require login)
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

try {
    $data = [];

    // 1. Get Total Revenue (Today, This Week, This Month)
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $monthStart = date('Y-m-01');

    // Today's revenue (all orders, including pending)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $data['revenue_today'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['revenue']);

    // This week's revenue (all orders, including pending)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) >= ?");
    $stmt->execute([$weekStart]);
    $data['revenue_week'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['revenue']);

    // This month's revenue (all orders, including pending)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) >= ?");
    $stmt->execute([$monthStart]);
    $data['revenue_month'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['revenue']);

    // 2. Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $data['total_orders'] = intval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

    // 3. Average Order Value (all orders)
    $stmt = $pdo->query("SELECT COALESCE(AVG(total_amount), 0) as avg_value FROM orders");
    $data['avg_order_value'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['avg_value']);

    // 4. Total Registered Customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $data['total_customers'] = intval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

    // 5. Most Popular Item
    $stmt = $pdo->query("
        SELECT oi.food_name, SUM(oi.quantity) as total_sold
        FROM order_items oi
        GROUP BY oi.food_id, oi.food_name
        ORDER BY total_sold DESC
        LIMIT 1
    ");
    $popularItem = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['popular_item'] = $popularItem ? $popularItem['food_name'] : 'N/A';
    $data['popular_item_sold'] = $popularItem ? intval($popularItem['total_sold']) : 0;

    // 6. Pending Orders Count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
    $data['pending_orders'] = intval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

    // 7. Sales Overview (Last 30 days) - All orders including pending
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $data['sales_overview'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Popular Menu Items (Top 10)
    $stmt = $pdo->query("
        SELECT oi.food_name, SUM(oi.quantity) as total_sold, COALESCE(SUM(oi.subtotal), 0) as revenue
        FROM order_items oi
        GROUP BY oi.food_id, oi.food_name
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    $data['popular_items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 9. Order Status Distribution
    $stmt = $pdo->query("
        SELECT order_status, COUNT(*) as count
        FROM orders
        GROUP BY order_status
    ");
    $data['order_status_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 10. Revenue by Category (we'll need to add category to food_items or derive from name)
    // For now, let's categorize based on item names
    $stmt = $pdo->query("
        SELECT 
            oi.food_name,
            COALESCE(SUM(oi.subtotal), 0) as revenue
        FROM order_items oi
        GROUP BY oi.food_name
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Categorize items
    $categories = [
        'Appetizers' => 0,
        'Main Dishes' => 0,
        'Salads' => 0,
        'Desserts' => 0
    ];

    foreach ($items as $item) {
        $name = strtolower($item['food_name']);
        if (
            strpos($name, 'spring roll') !== false || strpos($name, 'dumpling') !== false ||
            strpos($name, 'samosa') !== false || strpos($name, 'bruschetta') !== false ||
            strpos($name, 'wings') !== false || strpos($name, 'stick') !== false
        ) {
            $categories['Appetizers'] += $item['revenue'];
        } elseif (strpos($name, 'salad') !== false || strpos($name, 'caesar') !== false) {
            $categories['Salads'] += $item['revenue'];
        } elseif (
            strpos($name, 'cake') !== false || strpos($name, 'ice cream') !== false ||
            strpos($name, 'pie') !== false || strpos($name, 'pudding') !== false ||
            strpos($name, 'brownie') !== false || strpos($name, 'parfait') !== false ||
            strpos($name, 'tiramisu') !== false || strpos($name, 'cheesecake') !== false
        ) {
            $categories['Desserts'] += $item['revenue'];
        } else {
            $categories['Main Dishes'] += $item['revenue'];
        }
    }

    $data['revenue_by_category'] = [];
    foreach ($categories as $category => $revenue) {
        $data['revenue_by_category'][] = [
            'category' => $category,
            'revenue' => $revenue
        ];
    }

    // 11. Peak Hours Analysis (Orders by hour)
    $stmt = $pdo->query("
        SELECT HOUR(created_at) as hour, COUNT(*) as order_count
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY HOUR(created_at)
        ORDER BY hour ASC
    ");
    $data['peak_hours'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 12. Payment Method Distribution
    $stmt = $pdo->query("
        SELECT payment_method, COUNT(*) as count
        FROM orders
        GROUP BY payment_method
    ");
    $data['payment_methods'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 13. Daily Orders (Last 30 days)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as order_count
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $data['daily_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
