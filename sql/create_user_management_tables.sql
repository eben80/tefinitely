-- Drop tables if they exist to start fresh
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    subscription_status ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create subscriptions table
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paypal_transaction_id VARCHAR(255) NOT NULL,
    subscription_start_date DATETIME NOT NULL,
    subscription_end_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a default admin user
-- The password is 'adminpass'
INSERT INTO users (username, email, password, role, subscription_status) VALUES
('admin', 'admin@example.com', '$2y$10$I0jS6..L.pL4h3Y/G8k.S.V0hJz.uV0g6wzJ.o6wzJ.o6wzJ.o6w', 'admin', 'active');
