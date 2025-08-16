-- Table to store user progress on dialogue lines for Phase 1
CREATE TABLE IF NOT EXISTS `dialogue_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `dialogue_id` int(11) NOT NULL,
  `line_id` int(11) NOT NULL,
  `score` float NOT NULL DEFAULT '0',
  `attempts` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_line` (`user_id`, `line_id`),
  KEY `user_id` (`user_id`),
  KEY `dialogue_id` (`dialogue_id`),
  KEY `line_id` (`line_id`),
  CONSTRAINT `dialogue_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dialogue_progress_ibfk_2` FOREIGN KEY (`dialogue_id`) REFERENCES `dialogues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dialogue_progress_ibfk_3` FOREIGN KEY (`line_id`) REFERENCES `dialogue_lines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
