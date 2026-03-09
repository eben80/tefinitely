-- Cleanup existing duplicates in user_progress, keeping only the latest entry (highest ID) per user/phrase
DELETE t1 FROM user_progress t1
INNER JOIN user_progress t2
WHERE t1.id < t2.id
AND t1.user_id = t2.user_id
AND t1.phrase_id = t2.phrase_id;

-- Add UNIQUE constraint to prevent future duplicates
ALTER TABLE user_progress ADD UNIQUE KEY unique_user_phrase (user_id, phrase_id);
