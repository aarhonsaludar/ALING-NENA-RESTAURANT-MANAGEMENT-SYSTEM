<?php
require_once('connect.php');

$order_number = $_GET['order_number'] ?? null;

if (!$order_number) {
    header('Location: guest_menu.html');
    exit;
}

// Get order details
$stmt = $conn->prepare("
    SELECT * FROM orders WHERE order_number = ?
");
$stmt->bind_param("s", $order_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: guest_menu.html');
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order items
$itemStmt = $conn->prepare("
    SELECT * FROM order_items WHERE order_id = ?
");
$itemStmt->bind_param("i", $order['id']);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

$items = [];
while ($item = $itemResult->fetch_assoc()) {
    $items[] = $item;
}
$itemStmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Aling Nena's Kitchen</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 15px;
        }

        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
        }

        .success-icon svg {
            width: 100%;
            height: 100%;
        }

        .success-title {
            color: #28a745;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .order-number {
            font-size: 24px;
            color: #ff6b35;
            font-weight: 600;
            margin: 20px 0;
        }

        .order-details {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .detail-section {
            margin-bottom: 30px;
        }

        .detail-section h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff6b35;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .order-total {
            font-size: 20px;
            font-weight: 700;
            color: #ff6b35;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
        }

        .btn-continue {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="confirmation-container">
        <div class="success-card">
            <div class="success-icon">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                    <circle class="success-circle" fill="none" stroke="#28a745" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1" />
                    <polyline class="success-check" fill="none" stroke="#28a745" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5" />
                </svg>
            </div>
            <h1 class="success-title">Order Confirmed!</h1>
            <p style="color: #666; font-size: 16px;">Thank you for your order. We'll start preparing your food right away!</p>
            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
            <p style="color: #999; font-size: 14px;">
                Order placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
            </p>
        </div>

        <div class="order-details">
            <div class="detail-section">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <?php if ($order['customer_email']): ?>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                <?php endif; ?>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
            </div>

            <div class="detail-section">
                <h3>Delivery Address</h3>
                <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
            </div>

            <div class="detail-section">
                <h3>Order Items</h3>
                <?php foreach ($items as $item): ?>
                    <div class="detail-row">
                        <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['food_name']); ?></span>
                        <span>₱<?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="order-total">
                    <div class="detail-row">
                        <span>Total Amount:</span>
                        <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($order['notes']): ?>
                <div class="detail-section">
                    <h3>Special Instructions</h3>
                    <p><?php echo htmlspecialchars($order['notes']); ?></p>
                </div>
            <?php endif; ?>

            <div style="text-align: center;">
                <a href="guest_menu.html" class="btn-continue">Continue Shopping</a>
            </div>
        </div>
    </div>
</body>

</html>