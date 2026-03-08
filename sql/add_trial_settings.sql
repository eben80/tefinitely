-- Add trial_used column to users table
ALTER TABLE users ADD COLUMN trial_used BOOLEAN NOT NULL DEFAULT FALSE;

-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initialize trial settings
INSERT INTO settings (setting_key, setting_value) VALUES ('trial_enabled', '1') ON DUPLICATE KEY UPDATE setting_value = '1';
INSERT INTO settings (setting_key, setting_value) VALUES ('trial_days', '3') ON DUPLICATE KEY UPDATE setting_value = '3';
