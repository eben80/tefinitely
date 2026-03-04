-- Update users table to support email verification
ALTER TABLE users
ADD COLUMN email_verified BOOLEAN NOT NULL DEFAULT FALSE,
ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN pending_email VARCHAR(100) DEFAULT NULL;
