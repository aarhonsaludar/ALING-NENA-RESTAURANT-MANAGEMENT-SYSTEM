-- ============================================
-- TWO-FACTOR AUTHENTICATION (2FA) TABLES
-- ============================================

-- Add email_verified columns to users table (skip if already exists)
-- If you get "Duplicate column name" error, that's OK - columns already exist!
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'users' 
                   AND COLUMN_NAME = 'email_verified');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER password', 
    'SELECT "Column email_verified already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'email_verified_at');

SET @sql2 = IF(@col_exists2 = 0, 
    'ALTER TABLE users ADD COLUMN email_verified_at DATETIME NULL AFTER email_verified', 
    'SELECT "Column email_verified_at already exists" AS message');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Create verification codes table
CREATE TABLE IF NOT EXISTS `verification_codes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `type` ENUM('registration', 'login', 'password_reset') DEFAULT 'registration',
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `used_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `code` (`code`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `verification_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create login attempts table for security
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `successful` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `ip_address` (`ip_address`),
  KEY `attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create email configuration table
CREATE TABLE IF NOT EXISTS `email_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com',
  `smtp_port` INT(11) NOT NULL DEFAULT 587,
  `smtp_username` VARCHAR(255) NOT NULL,
  `smtp_password` VARCHAR(255) NOT NULL,
  `from_email` VARCHAR(255) NOT NULL,
  `from_name` VARCHAR(255) NOT NULL DEFAULT 'Aling Nena Kitchen',
  `is_active` TINYINT(1) DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default email configuration (UPDATE THESE VALUES!)
INSERT INTO `email_config` (`smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `from_email`, `from_name`) 
VALUES ('smtp.gmail.com', 587, 'jeysi.aguilan143@gmail.com', 'eghz twqx vviy cijv', 'noreply@alingnena.com', 'Aling Nena Kitchen');

-- Clean up expired verification codes (run this periodically)
-- DELETE FROM verification_codes WHERE expires_at < NOW() AND used = 0;
