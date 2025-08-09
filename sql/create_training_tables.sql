-- New tables for the training modules

-- For Phase 1: Shadowing Practice
CREATE TABLE `dialogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialogue_name` varchar(255) NOT NULL,
  `theme` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dialogue_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialogue_id` int(11) NOT NULL,
  `speaker` varchar(100) NOT NULL,
  `line_text` text NOT NULL,
  `line_order` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `dialogue_id` (`dialogue_id`),
  CONSTRAINT `dialogue_lines_ibfk_1` FOREIGN KEY (`dialogue_id`) REFERENCES `dialogues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- For Phase 2: Controlled Question Practice
CREATE TABLE `question_drills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `english_question` text NOT NULL,
  `french_vocab_hints` text NOT NULL,
  `expected_answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- For Phase 3: Semi-Guided Roleplays
CREATE TABLE `roleplay_scenarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- For Phase 5: Feedback & Reuse
CREATE TABLE `user_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `script_name` varchar(255) NOT NULL,
  `script_content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  UNIQUE KEY `unique_user_script_name` (`user_id`, `script_name`),
  CONSTRAINT `user_scripts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data for Demonstration

-- Phase 1: Dialogue for "Fruit Picking"
INSERT INTO `dialogues` (`dialogue_name`, `theme`, `section`) VALUES ('Cueillette de fruits', 'la_vie_de_tous_les_jours', 'section_a');

-- Get the ID of the dialogue we just inserted
SET @dialogue_id = LAST_INSERT_ID();

INSERT INTO `dialogue_lines` (`dialogue_id`, `speaker`, `line_text`, `line_order`) VALUES
(@dialogue_id, 'Student', 'Bonjour, je viens de lire votre annonce concernant la cueillette de fruits.', 1),
(@dialogue_id, 'Owner', 'Bonjour! Oui, absolument. Que souhaitez-vous savoir?', 2),
(@dialogue_id, 'Student', 'J’aurais quelques questions. D\'abord, où êtes-vous situé exactement?', 3),
(@dialogue_id, 'Owner', 'Nous sommes situés à 15 minutes de la ville, sur la route de campagne 12.', 4),
(@dialogue_id, 'Student', 'Parfait. Et est-ce que les enfants peuvent participer?', 5),
(@dialogue_id, 'Owner', 'Bien sûr, c\'est une activité familiale!', 6);

-- Phase 2: Question Drills
INSERT INTO `question_drills` (`theme`, `section`, `english_question`, `french_vocab_hints`, `expected_answer`) VALUES
('la_vie_de_tous_les_jours', 'section_a', 'Where exactly are you located?', 'adresse, où, situé', 'Où êtes-vous situé exactement?'),
('la_vie_de_tous_les_jours', 'section_a', 'Is there parking available?', 'parking, sur place, y a-t-il', 'Y a-t-il un parking sur place?'),
('le_travail', 'section_a', 'What are the working hours?', 'horaires, garde, quels sont', 'Quels sont les horaires de garde?');

-- Phase 3: Roleplay Scenarios
INSERT INTO `roleplay_scenarios` (`name`, `description`) VALUES
('Fruit Picking Ad', 'You are calling a farm about a fruit-picking activity you saw advertised.'),
('Babysitter Ad', 'You are calling a parent who is looking for a babysitter.');
