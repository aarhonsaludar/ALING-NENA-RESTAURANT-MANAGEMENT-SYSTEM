<?php

/**
 * Email Configuration for 2FA
 * Configure your SMTP settings here
 */

// Email settings - UPDATE THESE VALUES!
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // 587 for TLS, 465 for SSL
define('SMTP_USERNAME', 'jeysi.aguilan143@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'eghz twqx vviy cijv'); // Gmail App Password (not regular password)
define('FROM_EMAIL', 'noreply@alingnena.com');
define('FROM_NAME', 'Aling Nena Kitchen');

// Verification settings
define('CODE_LENGTH', 6); // Length of verification code
define('CODE_EXPIRY_MINUTES', 15); // How long codes are valid
define('MAX_LOGIN_ATTEMPTS', 5); // Max failed login attempts before lockout
define('LOCKOUT_TIME_MINUTES', 30); // How long account is locked after max attempts

// Application settings
define('APP_NAME', 'Aling Nena Kitchen');
define('APP_URL', 'http://localhost/LABORATORY%20EXAM');
define('SUPPORT_EMAIL', 'support@alingnena.com');

/**
 * GMAIL SETUP INSTRUCTIONS:
 * 
 * 1. Enable 2-Step Verification on your Google Account:
 *    https://myaccount.google.com/security
 * 
 * 2. Generate App Password:
 *    - Go to: https://myaccount.google.com/apppasswords
 *    - Select "Mail" and your device
 *    - Copy the 16-character password
 *    - Use it as SMTP_PASSWORD above (without spaces)
 * 
 * 3. Make sure "Less secure app access" is OFF (use App Password instead)
 * 
 * 4. Update SMTP_USERNAME with your Gmail address
 */
