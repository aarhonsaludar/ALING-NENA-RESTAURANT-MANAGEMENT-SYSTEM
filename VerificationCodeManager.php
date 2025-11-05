<?php

/**
 * Verification Code Manager
 * Handles creation and validation of 2FA codes
 */

require_once 'connect.php';
require_once 'email_config.php';

class VerificationCodeManager
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Generate a random verification code
     */
    public function generateCode($length = CODE_LENGTH)
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    /**
     * Create and store a new verification code
     */
    public function createCode($userId, $email, $type = 'registration')
    {
        // Invalidate any existing unused codes for this user and type
        $stmt = $this->conn->prepare("
            UPDATE verification_codes 
            SET used = 1, used_at = NOW() 
            WHERE user_id = ? AND type = ? AND used = 0
        ");
        $stmt->bind_param("is", $userId, $type);
        $stmt->execute();
        $stmt->close();

        // Generate new code
        $code = $this->generateCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . CODE_EXPIRY_MINUTES . ' minutes'));

        // Store the code
        $stmt = $this->conn->prepare("
            INSERT INTO verification_codes (user_id, email, code, type, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $userId, $email, $code, $type, $expiresAt);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            return $code;
        }
        return false;
    }

    /**
     * Verify a code
     */
    public function verifyCode($userId, $code, $type = 'registration')
    {
        $stmt = $this->conn->prepare("
            SELECT id, expires_at, used 
            FROM verification_codes 
            WHERE user_id = ? AND code = ? AND type = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bind_param("iss", $userId, $code, $type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Invalid verification code'];
        }

        $verification = $result->fetch_assoc();
        $stmt->close();

        // Check if already used
        if ($verification['used'] == 1) {
            return ['success' => false, 'message' => 'This code has already been used'];
        }

        // Check if expired
        if (strtotime($verification['expires_at']) < time()) {
            return ['success' => false, 'message' => 'This code has expired. Please request a new one.'];
        }

        // Mark as used
        $stmt = $this->conn->prepare("
            UPDATE verification_codes 
            SET used = 1, used_at = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $verification['id']);
        $stmt->execute();
        $stmt->close();

        return ['success' => true, 'message' => 'Code verified successfully'];
    }

    /**
     * Clean up expired codes
     */
    public function cleanupExpiredCodes()
    {
        $stmt = $this->conn->prepare("
            DELETE FROM verification_codes 
            WHERE expires_at < NOW() AND used = 0
        ");
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        return $deleted;
    }

    /**
     * Get remaining time for a code
     */
    public function getCodeExpiry($userId, $type = 'registration')
    {
        $stmt = $this->conn->prepare("
            SELECT expires_at 
            FROM verification_codes 
            WHERE user_id = ? AND type = ? AND used = 0 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bind_param("is", $userId, $type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            $expiresAt = strtotime($row['expires_at']);
            $now = time();
            $remaining = $expiresAt - $now;

            if ($remaining > 0) {
                return [
                    'expires_at' => $row['expires_at'],
                    'remaining_seconds' => $remaining,
                    'remaining_minutes' => ceil($remaining / 60)
                ];
            }
        }

        $stmt->close();
        return null;
    }
}
