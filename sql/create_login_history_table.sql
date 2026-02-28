CREATE TABLE IF NOT EXISTS `login_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `status` ENUM('success', 'failed') NOT NULL,
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`email`),
    INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
