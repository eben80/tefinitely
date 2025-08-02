-- Add columns to users table
ALTER TABLE users ADD COLUMN tour_completed BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE users ADD COLUMN last_topic VARCHAR(255);
ALTER TABLE users ADD COLUMN last_card_index INT;

-- Create user_progress table
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phrase_id INT NOT NULL,
    matching_quality DECIMAL(5, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (phrase_id) REFERENCES phrases(id)
);
