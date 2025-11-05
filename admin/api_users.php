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
            // Get all users with filters
            $role = $_GET['role'] ?? 'all';
            $status = $_GET['status'] ?? 'all';
            $search = $_GET['search'] ?? '';

            $sql = "SELECT id, username, email, phone, role, status, address, created_at, last_login 
                    FROM users WHERE 1=1";
            $params = [];

            if ($role !== 'all') {
                $sql .= " AND role = ?";
                $params[] = $role;
            }

            if ($status !== 'all') {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            if (!empty($search)) {
                $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $users]);
            break;

        case 'get_stats':
            // Get user statistics
            $stats = [];

            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Active users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $stats['active'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Admin users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $stats['admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // New users today
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
            $stats['new_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        case 'get_one':
            // Get single user
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("SELECT id, username, email, phone, role, status, address, created_at, last_login 
                                   FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $user]);
            break;

        case 'add':
            // Add new user
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $status = $_POST['status'] ?? 'active';
            $address = $_POST['address'] ?? '';

            if (empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Username and password are required']);
                break;
            }

            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                break;
            }

            // Check if email already exists
            if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Email already exists']);
                    break;
                }
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, phone, password, role, status, address, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$username, $email, $phone, $hashedPassword, $role, $status, $address]);

            echo json_encode([
                'success' => true,
                'message' => 'User added successfully',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'update':
            // Update user
            $id = $_POST['id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $status = $_POST['status'] ?? 'active';
            $address = $_POST['address'] ?? '';

            if (empty($username)) {
                echo json_encode(['success' => false, 'message' => 'Username is required']);
                break;
            }

            // Check if username already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $id]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                break;
            }

            // Check if email already exists (excluding current user)
            if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Email already exists']);
                    break;
                }
            }

            // Update with or without password
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, phone = ?, password = ?, role = ?, status = ?, address = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $phone, $hashedPassword, $role, $status, $address, $id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, phone = ?, role = ?, status = ?, address = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $phone, $role, $status, $address, $id]);
            }

            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            break;

        case 'delete':
            // Delete user
            $id = $_POST['id'] ?? 0;

            // Check if user has orders
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
            $stmt->execute([$id]);
            $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($orderCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => "Cannot delete user with existing orders ($orderCount orders). Consider setting status to inactive instead."
                ]);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            break;

        case 'reset_password':
            // Reset user password
            $id = $_POST['id'] ?? 0;
            $password = $_POST['password'] ?? '';

            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Password is required']);
                break;
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                break;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $id]);

            echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
