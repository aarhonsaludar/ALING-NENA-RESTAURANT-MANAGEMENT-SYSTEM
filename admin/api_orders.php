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

try {
    switch ($action) {
        case 'get_all':
            // Get all orders with filters
            $status = $_GET['status'] ?? 'all';
            $payment = $_GET['payment'] ?? 'all';
            $search = $_GET['search'] ?? '';

            $sql = "SELECT * FROM orders WHERE 1=1";
            $params = [];

            if ($status !== 'all') {
                $sql .= " AND order_status = ?";
                $params[] = $status;
            }

            if ($payment !== 'all') {
                $sql .= " AND payment_status = ?";
                $params[] = $payment;
            }

            if ($search) {
                $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $orders]);
            break;

        case 'get_details':
            // Get order details with items
            $orderId = $_GET['order_id'] ?? 0;

            // Get order info
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                break;
            }

            // Get order items
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $order]);
            break;

        case 'update_status':
            // Update order status
            $orderId = $_POST['order_id'] ?? 0;
            $newStatus = $_POST['status'] ?? '';

            $validStatuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                break;
            }

            $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);

            // If delivered, mark payment as paid
            if ($newStatus === 'delivered') {
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status = 'pending'");
                $stmt->execute([$orderId]);
            }

            echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
            break;

        case 'update_payment_status':
            // Update payment status
            $orderId = $_POST['order_id'] ?? 0;
            $paymentStatus = $_POST['payment_status'] ?? '';

            $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$paymentStatus, $orderId]);

            echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
            break;

        case 'get_statistics':
            // Get order statistics
            $stats = [];

            // Total orders
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
            $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Pending orders
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
            $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total revenue
            $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
            $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Today's orders
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
            $stats['today_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
