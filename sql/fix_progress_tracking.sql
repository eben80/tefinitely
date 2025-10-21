-- This script fixes the progress tracking functionality by ensuring each user has only
-- one progress record per phrase.

-- Step 1: Temporarily disable foreign key checks to allow truncation.
SET FOREIGN_KEY_CHECKS=0;

-- Step 2: Clear any existing progress data to start fresh.
TRUNCATE TABLE `user_progress`;

-- Step 3: Add a unique constraint to prevent duplicate progress entries.
-- This ensures that each user can only have one `matching_quality` score per phrase.
-- If a record for a `user_id` and `phrase_id` already exists, the new score will
-- update the existing record instead of creating a new one (handled in the backend).
ALTER TABLE `user_progress` ADD UNIQUE `unique_user_phrase`(`user_id`, `phrase_id`);

-- Step 4: Re-enable foreign key checks.
SET FOREIGN_KEY_CHECKS=1;
