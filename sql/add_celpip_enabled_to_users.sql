-- Add celpip_enabled column to users table
ALTER TABLE users ADD COLUMN celpip_enabled BOOLEAN NOT NULL DEFAULT TRUE;
