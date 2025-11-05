<?php
session_start();
header('Content-Type: application/json');

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
require_once('EmailService.php');

$userId = $_SESSION['pending_verification_user_id'];
$email = $_SESSION['pending_verification_email'] ?? '';
$name = $_SESSION['pending_verification_name'] ?? '';

// Check if email exists
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email not found in session'
    ]);
    exit;
}

// Generate new code
$codeManager = new VerificationCodeManager($conn);
$code = $codeManager->createCode($userId, $email, 'registration');

if (!$code) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate verification code'
    ]);
    exit;
}

// Send email with new code
$emailService = new EmailService();

try {
    $emailService->sendVerificationCode($email, $name, $code, 'registration');

    // Get new expiry info
    $expiryInfo = $codeManager->getCodeExpiry($userId, 'registration');

    echo json_encode([
        'success' => true,
        'message' => 'Verification code resent successfully',
        'expiry' => $expiryInfo ? $expiryInfo['expires_at'] : null,
        'remaining_seconds' => $expiryInfo ? $expiryInfo['remaining_seconds'] : 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send verification email: ' . $e->getMessage()
    ]);
}

$conn->close();
