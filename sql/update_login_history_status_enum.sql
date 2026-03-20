-- Update status ENUM in login_history to include 'unverified'
ALTER TABLE login_history MODIFY COLUMN status ENUM('success', 'failed', 'unverified') NOT NULL;
