<?php
session_start();
header('Content-Type: application/json');

// Connect to database
require_once('connect.php');
require_once('EmailService.php');
require_once('VerificationCodeManager.php');

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

// Check if email_verified column exists and verification_codes table exists
$email_verified_exists = false;
$verification_table_exists = false;

$columns_query = "SHOW COLUMNS FROM users LIKE 'email_verified'";
$column_result = $conn->query($columns_query);
if ($column_result && $column_result->num_rows > 0) {
    $email_verified_exists = true;
}

$table_query = "SHOW TABLES LIKE 'verification_codes'";
$table_result = $conn->query($table_query);
if ($table_result && $table_result->num_rows > 0) {
    $verification_table_exists = true;
}

// Insert new user
if ($email_verified_exists) {
    // With 2FA support
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, phone, password, role, status, email_verified, created_at) VALUES (?, ?, ?, ?, ?, 'user', 'active', 0, NOW())");
} else {
    // Without 2FA
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, phone, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'user', 'active', NOW())");
}
$stmt->bind_param("sssss", $full_name, $username, $email, $phone, $hashed_password);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    $stmt->close();

    // Only try 2FA if tables exist
    if ($email_verified_exists && $verification_table_exists && class_exists('VerificationCodeManager')) {
        // Generate and send verification code
        try {
            $codeManager = new VerificationCodeManager($conn);
            $verificationCode = $codeManager->createCode($user_id, $email, 'registration');

            if ($verificationCode) {
                $emailService = new EmailService();
                $emailSent = $emailService->sendVerificationCode($email, $full_name, $verificationCode, 'registration');

                if ($emailSent) {
                    // Store user info in session for verification page
                    $_SESSION['pending_verification_user_id'] = $user_id;
                    $_SESSION['pending_verification_email'] = $email;
                    $_SESSION['pending_verification_name'] = $full_name;

                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Please check your email for verification code.',
                        'requires_verification' => true,
                        'user_id' => $user_id
                    ]);
                } else {
                    // Email failed, but account created
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! However, we could not send the verification email. Please contact support.',
                        'requires_verification' => true,
                        'user_id' => $user_id,
                        'email_error' => true
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Registration successful but failed to generate verification code. Please contact support.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful but verification email failed: ' . $e->getMessage(),
                'requires_verification' => true,
                'user_id' => $user_id
            ]);
        }
    } else {
        // 2FA not available, account ready to use
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! You can now login.',
            'user_id' => $user_id
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed: ' . $stmt->error
    ]);
    $stmt->close();
}

$conn->close();
