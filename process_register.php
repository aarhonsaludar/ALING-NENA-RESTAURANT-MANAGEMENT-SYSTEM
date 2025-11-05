<?php
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');

// Get POST data
$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($full_name) || empty($username) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate username length
if (strlen($username) < 3) {
    echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Check if username already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Hash password using bcrypt
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user with default role and status
$stmt = $conn->prepare("INSERT INTO users (full_name, username, email, phone, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'user', 'active', NOW())");
$stmt->bind_param("sssss", $full_name, $username, $email, $phone, $hashed_password);
$stmt->bind_param("sssss", $full_name, $username, $email, $phone, $hashed_password);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
}

$stmt->close();
$conn->close();
