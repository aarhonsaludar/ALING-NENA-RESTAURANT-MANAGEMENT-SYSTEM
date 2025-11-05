<?php
session_start();
header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in response
ini_set('log_errors', 1);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$code = $input['code'] ?? '';

// Log the request
error_log("Verification attempt with code: " . $code);

// Validate input
if (empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Verification code is required'
    ]);
    exit;
}

// Check if user has pending verification
if (!isset($_SESSION['pending_verification_user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No pending verification found'
    ]);
    exit;
}

require_once('connect.php');
require_once('VerificationCodeManager.php');

$userId = $_SESSION['pending_verification_user_id'];

// Verify the code
$codeManager = new VerificationCodeManager($conn);
$result = $codeManager->verifyCode($userId, $code, 'registration');

if ($result['success']) {
    // Update user as verified
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        // Clear verification session
        unset($_SESSION['pending_verification_user_id']);
        unset($_SESSION['pending_verification_email']);
        unset($_SESSION['pending_verification_name']);

        // Send success response immediately (don't wait for welcome email)
        echo json_encode([
            'success' => true,
            'message' => 'Email verified successfully! You can now login.'
        ]);

        // Optionally send welcome email (in background, don't block response)
        try {
            require_once('EmailService.php');
            $emailService = new EmailService();

            // Get user details
            $userStmt = $conn->prepare("SELECT email, full_name FROM users WHERE id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();

            if ($user) {
                $emailService->sendWelcomeEmail($user['email'], $user['full_name']);
            }
        } catch (Exception $e) {
            // Log error but don't fail the verification
            error_log("Failed to send welcome email: " . $e->getMessage());
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update verification status'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
}

$conn->close();
