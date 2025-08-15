-- This script updates the database schema to support recurring PayPal subscriptions.

-- Modify the existing subscriptions table for the new recurring model
-- It changes the transaction ID column to a subscription ID column, adds a status,
-- and ensures a user can only have one subscription record.
ALTER TABLE `subscriptions`
    CHANGE COLUMN `paypal_transaction_id` `paypal_subscription_id` VARCHAR(255) NOT NULL,
    ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'inactive' AFTER `paypal_subscription_id`,
    ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD UNIQUE INDEX `user_id_unique` (`user_id`);

-- Create a new table to log individual recurring payments from the webhook.
CREATE TABLE IF NOT EXISTS `subscription_payments` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    paypal_transaction_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    payment_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
