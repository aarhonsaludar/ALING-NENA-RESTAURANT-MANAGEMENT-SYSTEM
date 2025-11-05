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

$userId = $_SESSION['pending_verification_user_id'];
$email = $_SESSION['pending_verification_email'] ?? '';
$name = $_SESSION['pending_verification_name'] ?? '';

// Get code expiry
$codeManager = new VerificationCodeManager($conn);
$expiryInfo = $codeManager->getCodeExpiry($userId, 'registration');

echo json_encode([
    'success' => true,
    'user_id' => $userId,
    'email' => $email,
    'name' => $name,
    'expiry' => $expiryInfo ? $expiryInfo['expires_at'] : null,
    'remaining_seconds' => $expiryInfo ? $expiryInfo['remaining_seconds'] : 0
]);

$conn->close();
