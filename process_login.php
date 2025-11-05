<?php
session_start();
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');

// Get POST data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Missing username or password']);
    exit;
}

// Check if email_verified column exists
$email_verified_exists = false;
$columns_query = "SHOW COLUMNS FROM users LIKE 'email_verified'";
$column_result = $conn->query($columns_query);
if ($column_result && $column_result->num_rows > 0) {
    $email_verified_exists = true;
}

// Query the database - get user by username first
if ($email_verified_exists) {
    $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, password, role, status, email_verified FROM users WHERE username = ?");
} else {
    $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, password, role, status FROM users WHERE username = ?");
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Set default role if not set
    $user_role = $user['role'] ?? 'user';
    $user_status = $user['status'] ?? 'active';
    $email_verified = $email_verified_exists ? ($user['email_verified'] ?? 0) : 1;

    // Check if account is active
    if ($user_status !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Account is not active']);
        exit;
    }

    // Check if email is verified (only if column exists)
    if ($email_verified_exists && $email_verified != 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Please verify your email before logging in. Check your inbox for the verification code.',
            'requires_verification' => true,
            'email' => $user['email']
        ]);
        exit;
    }

    // Verify password using password_verify() for bcrypt hashes
    if (password_verify($password, $user['password'])) {
        // Update last login time
        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = 'registered';
        $_SESSION['role'] = $user_role;

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'phone' => $user['phone'],
                'role' => $user_role
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

$stmt->close();
$conn->close();
