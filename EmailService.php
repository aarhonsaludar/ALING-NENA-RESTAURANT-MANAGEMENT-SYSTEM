<?php

/**
 * Email Service using PHPMailer
 * Handles sending verification codes and notifications
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/email_config.php';

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = SMTP_PORT;

            // Default sender
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);

            // Encoding
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }

    /**
     * Send verification code email
     */
    public function sendVerificationCode($email, $fullName, $code, $type = 'registration')
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $fullName);

            $this->mailer->isHTML(true);

            switch ($type) {
                case 'registration':
                    $subject = 'Verify Your Email - ' . APP_NAME;
                    $body = $this->getRegistrationEmailTemplate($fullName, $code);
                    break;
                case 'login':
                    $subject = 'Your Login Verification Code - ' . APP_NAME;
                    $body = $this->getLoginEmailTemplate($fullName, $code);
                    break;
                case 'password_reset':
                    $subject = 'Password Reset Code - ' . APP_NAME;
                    $body = $this->getPasswordResetTemplate($fullName, $code);
                    break;
                default:
                    $subject = 'Verification Code - ' . APP_NAME;
                    $body = $this->getGenericTemplate($fullName, $code);
            }

            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Registration Email Template
     */
    private function getRegistrationEmailTemplate($name, $code)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b35 0%, #d32f2f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .code-box { background: white; border: 2px dashed #ff6b35; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #ff6b35; letter-spacing: 5px; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 30px; background: #ff6b35; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🍽️ Aling Nena's Kitchen</h1>
                    <p>Welcome to Our Family!</p>
                </div>
                <div class='content'>
                    <h2>Hello {$name}! 👋</h2>
                    <p>Thank you for registering with Aling Nena's Kitchen! We're excited to serve you delicious Filipino cuisine.</p>
                    
                    <p>To complete your registration, please verify your email address using the code below:</p>
                    
                    <div class='code-box'>
                        <div>Your Verification Code:</div>
                        <div class='code'>{$code}</div>
                    </div>
                    
                    <p><strong>⏰ This code will expire in " . CODE_EXPIRY_MINUTES . " minutes.</strong></p>
                    
                    <p>If you didn't create an account with us, please ignore this email or contact our support team.</p>
                    
                    <p>Once verified, you can:</p>
                    <ul>
                        <li>Browse our delicious menu</li>
                        <li>Place orders for delivery</li>
                        <li>Save your favorite dishes</li>
                        <li>Track your order history</li>
                    </ul>
                    
                    <p>Need help? Contact us at <a href='mailto:" . SUPPORT_EMAIL . "'>" . SUPPORT_EMAIL . "</a></p>
                </div>
                <div class='footer'>
                    <p><strong>Aling Nena's Kitchen</strong></p>
                    <p>Serving authentic Filipino home cooking since 1995</p>
                    <p>&copy; 2025 Aling Nena's Kitchen. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Login Email Template
     */
    private function getLoginEmailTemplate($name, $code)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b35 0%, #d32f2f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .code-box { background: white; border: 2px dashed #ff6b35; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #ff6b35; letter-spacing: 5px; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Login Verification</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$name}!</h2>
                    <p>We noticed a login attempt to your Aling Nena's Kitchen account.</p>
                    
                    <p>Please use the following code to complete your login:</p>
                    
                    <div class='code-box'>
                        <div>Your Login Code:</div>
                        <div class='code'>{$code}</div>
                    </div>
                    
                    <p><strong>⏰ This code will expire in " . CODE_EXPIRY_MINUTES . " minutes.</strong></p>
                    
                    <div class='warning'>
                        <strong>⚠️ Security Notice:</strong><br>
                        If you didn't attempt to login, please change your password immediately and contact our support team.
                    </div>
                    
                    <p>Stay safe and enjoy your meal! 🍽️</p>
                </div>
                <div class='footer'>
                    <p><strong>Aling Nena's Kitchen</strong></p>
                    <p>&copy; 2025 Aling Nena's Kitchen. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Password Reset Template
     */
    private function getPasswordResetTemplate($name, $code)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b35 0%, #d32f2f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .code-box { background: white; border: 2px dashed #ff6b35; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #ff6b35; letter-spacing: 5px; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔑 Password Reset</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$name}!</h2>
                    <p>We received a request to reset your password for Aling Nena's Kitchen account.</p>
                    
                    <p>Use this code to reset your password:</p>
                    
                    <div class='code-box'>
                        <div>Your Reset Code:</div>
                        <div class='code'>{$code}</div>
                    </div>
                    
                    <p><strong>⏰ This code will expire in " . CODE_EXPIRY_MINUTES . " minutes.</strong></p>
                    
                    <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
                </div>
                <div class='footer'>
                    <p><strong>Aling Nena's Kitchen</strong></p>
                    <p>&copy; 2025 Aling Nena's Kitchen. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Generic Template
     */
    private function getGenericTemplate($name, $code)
    {
        return "
        <!DOCTYPE html>
        <html>
        <body>
            <h2>Hello {$name}!</h2>
            <p>Your verification code is: <strong>{$code}</strong></p>
            <p>This code will expire in " . CODE_EXPIRY_MINUTES . " minutes.</p>
        </body>
        </html>
        ";
    }

    /**
     * Send welcome email after successful verification
     */
    public function sendWelcomeEmail($email, $fullName)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $fullName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to ' . APP_NAME . '!';
            $this->mailer->Body = $this->getWelcomeTemplate($fullName);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Welcome email error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    private function getWelcomeTemplate($name)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b35 0%, #d32f2f 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Welcome to the Family!</h1>
                </div>
                <div class='content'>
                    <h2>Congratulations {$name}!</h2>
                    <p>Your email has been successfully verified! You're now part of the Aling Nena's Kitchen family.</p>
                    
                    <p>Start exploring our menu and enjoy authentic Filipino home cooking delivered to your door!</p>
                    
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='" . APP_URL . "/badges_lab.html' style='display: inline-block; padding: 15px 30px; background: #ff6b35; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Browse Our Menu</a>
                    </p>
                    
                    <p>Happy eating! 🍽️</p>
                </div>
                <div class='footer'>
                    <p><strong>Aling Nena's Kitchen</strong></p>
                    <p>Serving authentic Filipino home cooking since 1995</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
