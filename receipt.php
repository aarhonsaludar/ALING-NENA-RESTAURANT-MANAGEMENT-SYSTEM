<?php
require_once('connect.php');

if (!isset($_POST['user_id'])) {
    header('Location: badges_lab.html');
    exit;
}

$user_id = $_POST['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT username, email, phone, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get cart items
$query = "SELECT c.quantity, c.food_id, f.name, f.price, (f.price * c.quantity) as total 
          FROM cart c 
          JOIN food_items f ON c.food_id = f.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$grand_total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $grand_total += $row['total'];
}

// Generate order number
$order_number = 'ORD-' . date('Ymd') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT) . rand(100, 999);

// Prepare customer information
$customer_name = $user['full_name'] ?? $user['username'];
$customer_email = $user['email'] ?? '';
$customer_phone = $user['phone'] ?? '';
$delivery_address = 'Pickup / Dine-in'; // Default, can be enhanced with address selection

// Insert into orders table
$insert_order = $conn->prepare("
    INSERT INTO orders (user_id, order_number, customer_name, customer_email, customer_phone, 
                        delivery_address, total_amount, order_status, payment_status, 
                        payment_method) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'Cash on Delivery')
");
$insert_order->bind_param(
    "isssssd",
    $user_id,
    $order_number,
    $customer_name,
    $customer_email,
    $customer_phone,
    $delivery_address,
    $grand_total
);
$insert_order->execute();
$order_id = $conn->insert_id;

// Insert into order_items table with food_name and subtotal
foreach ($items as $item) {
    $insert_item = $conn->prepare("
        INSERT INTO order_items (order_id, food_id, food_name, quantity, price, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $subtotal = $item['price'] * $item['quantity'];
    $insert_item->bind_param(
        "iisidd",
        $order_id,
        $item['food_id'],
        $item['name'],
        $item['quantity'],
        $item['price'],
        $subtotal
    );
    $insert_item->execute();
}

// Clear the cart after saving order
$clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$clear_stmt->bind_param("i", $user_id);
$clear_stmt->execute();

$date = date('Y-m-d H:i:s');
$receipt_no = 'RCP-' . date('Ymd') . '-' . $user_id . rand(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #ccc;
        }

        .receipt-details {
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .total-section {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
        }

        .thank-you {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 10px;
        }

        .logo {
            display: block;
            margin: 0 auto 1.5rem;
            max-width: 150px;
            height: auto;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background-color: white;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1>Order Receipt</h1>
            <img src="img/aling_nena_logo.png" alt="logo" class="logo">
            <p>Tindahan Ni Aling Nena</p>
            <p>Cabuyao, Laguna</p>
            <p>+639486265</p>
        </div>


        <div class="receipt-details">
            <p><strong>Order No:</strong> <?php echo $order_number; ?></p>
            <p><strong>Receipt No:</strong> <?php echo $receipt_no; ?></p>
            <p><strong>Date:</strong> <?php echo $date; ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Status:</strong> <span style="color: orange;">Pending</span></p>
            <p><strong>Payment:</strong> Cash on Delivery (Pending)</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>₱<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <p>Grand Total: ₱<?php echo number_format($grand_total, 2); ?></p>
        </div>

        <div class="thank-you">
            <p>Thank you for your order!</p>
            <p>Please come again</p>
        </div>

        <div class="actions no-print">
            <button onclick="window.print()" class="btn">Print Receipt</button>
            <a href="badges_lab.html" class="btn">Back to Menu</a>
        </div>
    </div>
</body>

</html>