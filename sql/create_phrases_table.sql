-- phrases_tef.sql
-- Drop table if exists
DROP TABLE IF EXISTS phrases;

-- Create table with utf8mb4 encoding
CREATE TABLE phrases (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  french_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  english_translation TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  theme VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
  section VARCHAR(50) COLLATE utf8mb4_general_ci DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert phrases for daily_life theme
INSERT INTO phrases (french_text, english_translation, theme, section) VALUES
('Bonjour, je vous appelle au sujet de l’annonce.', 'Hello, I am calling about the advertisement.', 'daily_life', 'section_a'),
('Pourriez-vous me donner plus d’informations ?', 'Could you give me more information?', 'daily_life', 'section_a'),
('Quels sont les horaires d’ouverture ?', 'What are the opening hours?', 'daily_life', 'section_a'),
('Est-ce que le lieu est accessible en transport en commun ?', 'Is the place accessible by public transport?', 'daily_life', 'section_a'),
('Y a-t-il un parking à proximité ?', 'Is there parking nearby?', 'daily_life', 'section_a'),
('Quel est le prix du service ?', 'What is the price of the service?', 'daily_life', 'section_a'),
('Est-ce que je dois prendre rendez-vous ?', 'Do I need to make an appointment?', 'daily_life', 'section_a'),
('Combien de temps dure la séance ?', 'How long does the session last?', 'daily_life', 'section_a'),
('Acceptez-vous les paiements par carte ?', 'Do you accept card payments?', 'daily_life', 'section_a'),
('À quelle adresse se trouve l’entreprise ?', 'What is the company\'s address?', 'daily_life', 'section_a'),
('Puis-je parler à la personne responsable ?', 'May I speak to the person in charge?', 'daily_life', 'section_a'),
('Y a-t-il des réductions pour les étudiants ?', 'Are there discounts for students?', 'daily_life', 'section_a'),
('Quels documents dois-je apporter ?', 'What documents should I bring?', 'daily_life', 'section_a'),
('Est-ce que c’est possible de changer la date ?', 'Is it possible to change the date?', 'daily_life', 'section_a'),
('Puis-je avoir un numéro de téléphone pour vous joindre ?', 'Can I have a phone number to reach you?', 'daily_life', 'section_a'),

-- Insert phrases for housing theme
('Bonjour, je suis intéressé par l’appartement à louer.', 'Hello, I am interested in the apartment for rent.', 'housing', 'section_a'),
('Combien de chambres y a-t-il ?', 'How many bedrooms are there?', 'housing', 'section_a'),
('Est-ce que le loyer inclut les charges ?', 'Does the rent include utilities?', 'housing', 'section_a'),
('Y a-t-il une cuisine équipée ?', 'Is there an equipped kitchen?', 'housing', 'section_a'),
('Est-ce que les animaux domestiques sont acceptés ?', 'Are pets allowed?', 'housing', 'section_a'),
('Quel est le montant de la caution ?', 'What is the security deposit amount?', 'housing', 'section_a'),
('Puis-je visiter l’appartement ?', 'Can I visit the apartment?', 'housing', 'section_a'),
('À quelle date l’appartement sera-t-il disponible ?', 'When will the apartment be available?', 'housing', 'section_a'),
('Y a-t-il un ascenseur dans l’immeuble ?', 'Is there an elevator in the building?', 'housing', 'section_a'),
('Comment puis-je déposer ma candidature ?', 'How can I submit my application?', 'housing', 'section_a'),
('Le logement est-il meublé ?', 'Is the accommodation furnished?', 'housing', 'section_a'),
('Est-ce que le quartier est calme ?', 'Is the neighborhood quiet?', 'housing', 'section_a'),
('Quel est le temps de trajet jusqu’au centre-ville ?', 'What is the commute time to downtown?', 'housing', 'section_a'),
('Le chauffage est-il inclus ?', 'Is heating included?', 'housing', 'section_a'),
('Y a-t-il un parking disponible ?', 'Is parking available?', 'housing', 'section_a'),

-- Insert phrases for courses theme
('Bonjour, je voudrais des informations sur les cours.', 'Hello, I would like information about the courses.', 'courses', 'section_a'),
('Quels sont les horaires des cours ?', 'What are the course schedules?', 'courses', 'section_a'),
('Combien coûtent les frais d’inscription ?', 'How much are the registration fees?', 'courses', 'section_a'),
('Les cours sont-ils en présentiel ou en ligne ?', 'Are the courses in person or online?', 'courses', 'section_a'),
('Y a-t-il un certificat à la fin du cours ?', 'Is there a certificate at the end of the course?', 'courses', 'section_a'),
('Puis-je suivre un cours d’essai ?', 'Can I attend a trial class?', 'courses', 'section_a'),
('Quels sont les prérequis pour s’inscrire ?', 'What are the prerequisites for enrollment?', 'courses', 'section_a'),
('Comment puis-je m’inscrire ?', 'How can I register?', 'courses', 'section_a'),
('Y a-t-il un nombre limité de places ?', 'Is there a limited number of spots?', 'courses', 'section_a'),
('Est-ce que les cours sont adaptés aux débutants ?', 'Are the courses suitable for beginners?', 'courses', 'section_a'),
('Quels sont les moyens de paiement acceptés ?', 'What payment methods are accepted?', 'courses', 'section_a'),
('Le matériel est-il fourni ?', 'Is the material provided?', 'courses', 'section_a'),
('Puis-je changer de groupe si nécessaire ?', 'Can I change groups if necessary?', 'courses', 'section_a'),
('Quelle est la durée totale du cours ?', 'What is the total duration of the course?', 'courses', 'section_a'),
('Y a-t-il des aides financières disponibles ?', 'Are financial aids available?', 'courses', 'section_a'),

-- Insert phrases for jobs theme
('Bonjour, je vous appelle concernant l’offre d’emploi.', 'Hello, I am calling about the job offer.', 'jobs', 'section_a'),
('Le poste est-il encore disponible ?', 'Is the position still available?', 'jobs', 'section_a'),
('Quelles sont les qualifications requises ?', 'What qualifications are required?', 'jobs', 'section_a'),
('Quel est le salaire proposé ?', 'What is the offered salary?', 'jobs', 'section_a'),
('Est-ce un contrat à durée déterminée ou indéterminée ?', 'Is it a fixed-term or permanent contract?', 'jobs', 'section_a'),
('Quelles sont les horaires de travail ?', 'What are the working hours?', 'jobs', 'section_a'),
('Y a-t-il des possibilités d’évolution ?', 'Are there opportunities for advancement?', 'jobs', 'section_a'),
('Où se situe le lieu de travail ?', 'Where is the workplace located?', 'jobs', 'section_a'),
('Quelles sont les responsabilités principales ?', 'What are the main responsibilities?', 'jobs', 'section_a'),
('Quand puis-je commencer ?', 'When can I start?', 'jobs', 'section_a'),
('Dois-je fournir des références ?', 'Do I need to provide references?', 'jobs', 'section_a'),
('Y a-t-il une période d’essai ?', 'Is there a trial period?', 'jobs', 'section_a'),
('Comment se déroule le processus de recrutement ?', 'How does the recruitment process work?', 'jobs', 'section_a'),
('Puis-je envoyer mon CV par email ?', 'Can I send my CV by email?', 'jobs', 'section_a'),
('À qui dois-je m’adresser pour plus d’informations ?', 'Who should I contact for more information?', 'jobs', 'section_a');
