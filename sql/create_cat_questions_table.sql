CREATE TABLE IF NOT EXISTS cat_questions (
    id VARCHAR(50) PRIMARY KEY,
    competency VARCHAR(100) NOT NULL,
    cefr_target VARCHAR(10) NOT NULL,
    estimated_difficulty FLOAT NOT NULL,
    stem TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL,
    rationale TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
