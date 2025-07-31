-- phrases_tef.sql
-- Drop table if exists
DROP TABLE IF EXISTS phrases;

-- Create table with utf8mb4 encoding
CREATE TABLE phrases (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  french_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  english_translation TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  mode VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
  main_topic VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
  sub_topic VARCHAR(100) COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert phrases for daily_life theme
INSERT INTO phrases (french_text, english_translation, mode, main_topic, sub_topic) VALUES
('Bonjour, je vous appelle au sujet de l’annonce.', 'Hello, I am calling about the advertisement.', 'Practice', 'Oral Expression', 'Section A'),
('Pourriez-vous me donner plus d’informations ?', 'Could you give me more information?', 'Practice', 'Oral Expression', 'Section A'),
('Quels sont les horaires d’ouverture ?', 'What are the opening hours?', 'Practice', 'Oral Expression', 'Section A'),
('Est-ce que le lieu est accessible en transport en commun ?', 'Is the place accessible by public transport?', 'Practice', 'Oral Expression', 'Section A'),
('Y a-t-il un parking à proximité ?', 'Is there parking nearby?', 'Practice', 'Oral Expression', 'Section A'),
('Quel est le prix du service ?', 'What is the price of the service?', 'Practice', 'Oral Expression', 'Section A'),
('Est-ce que je dois prendre rendez-vous ?', 'Do I need to make an appointment?', 'Practice', 'Oral Expression', 'Section A'),
('Combien de temps dure la séance ?', 'How long does the session last?', 'Practice', 'Oral Expression', 'Section A'),
('Acceptez-vous les paiements par carte ?', 'Do you accept card payments?', 'Practice', 'Oral Expression', 'Section A'),
('À quelle adresse se trouve l’entreprise ?', 'What is the company\'s address?', 'Practice', 'Oral Expression', 'Section A'),
('Puis-je parler à la personne responsable ?', 'May I speak to the person in charge?', 'Practice', 'Oral Expression', 'Section A'),
('Y a-t-il des réductions pour les étudiants ?', 'Are there discounts for students?', 'Practice', 'Oral Expression', 'Section A'),
('Quels documents dois-je apporter ?', 'What documents should I bring?', 'Practice', 'Oral Expression', 'Section A'),
('Est-ce que c’est possible de changer la date ?', 'Is it possible to change the date?', 'Practice', 'Oral Expression', 'Section A'),
('Puis-je avoir un numéro de téléphone pour vous joindre ?', 'Can I have a phone number to reach you?', 'Practice', 'Oral Expression', 'Section A'),

-- Insert phrases for housing theme
('Bonjour, je suis intéressé par l’appartement à louer.', 'Hello, I am interested in the apartment for rent.', 'Practice', 'Oral Expression', 'Section B'),
('Combien de chambres y a-t-il ?', 'How many bedrooms are there?', 'Practice', 'Oral Expression', 'Section B'),
('Est-ce que le loyer inclut les charges ?', 'Does the rent include utilities?', 'Practice', 'Oral Expression', 'Section B'),
('Y a-t-il une cuisine équipée ?', 'Is there an equipped kitchen?', 'Practice', 'Oral Expression', 'Section B'),
('Est-ce que les animaux domestiques sont acceptés ?', 'Are pets allowed?', 'Practice', 'Oral Expression', 'Section B'),
('Quel est le montant de la caution ?', 'What is the security deposit amount?', 'Practice', 'Oral Expression', 'Section B'),
('Puis-je visiter l’appartement ?', 'Can I visit the apartment?', 'Practice', 'Oral Expression', 'Section B'),
('À quelle date l’appartement sera-t-il disponible ?', 'When will the apartment be available?', 'Practice', 'Oral Expression', 'Section B'),
('Y a-t-il un ascenseur dans l’immeuble ?', 'Is there an elevator in the building?', 'Practice', 'Oral Expression', 'Section B'),
('Comment puis-je déposer ma candidature ?', 'How can I submit my application?', 'Practice', 'Oral Expression', 'Section B'),
('Le logement est-il meublé ?', 'Is the accommodation furnished?', 'Practice', 'Oral Expression', 'Section B'),
('Est-ce que le quartier est calme ?', 'Is the neighborhood quiet?', 'Practice', 'Oral Expression', 'Section B'),
('Quel est le temps de trajet jusqu’au centre-ville ?', 'What is the commute time to downtown?', 'Practice', 'Oral Expression', 'Section B'),
('Le chauffage est-il inclus ?', 'Is heating included?', 'Practice', 'Oral Expression', 'Section B'),
('Y a-t-il un parking disponible ?', 'Is parking available?', 'Practice', 'Oral Expression', 'Section B'),

-- Insert phrases for courses theme
('Bonjour, je voudrais des informations sur les cours.', 'Hello, I would like information about the courses.', 'Practice', 'Written Expression', 'Section A'),
('Quels sont les horaires des cours ?', 'What are the course schedules?', 'Practice', 'Written Expression', 'Section A'),
('Combien coûtent les frais d’inscription ?', 'How much are the registration fees?', 'Practice', 'Written Expression', 'Section A'),
('Les cours sont-ils en présentiel ou en ligne ?', 'Are the courses in person or online?', 'Practice', 'Written Expression', 'Section A'),
('Y a-t-il un certificat à la fin du cours ?', 'Is there a certificate at the end of the course?', 'Practice', 'Written Expression', 'Section A'),
('Puis-je suivre un cours d’essai ?', 'Can I attend a trial class?', 'Practice', 'Written Expression', 'Section A'),
('Quels sont les prérequis pour s’inscrire ?', 'What are the prerequisites for enrollment?', 'Practice', 'Written Expression', 'Section A'),
('Comment puis-je m’inscrire ?', 'How can I register?', 'Practice', 'Written Expression', 'Section A'),
('Y a-t-il un nombre limité de places ?', 'Is there a limited number of spots?', 'Practice', 'Written Expression', 'Section A'),
('Est-ce que les cours sont adaptés aux débutants ?', 'Are the courses suitable for beginners?', 'Practice', 'Written Expression', 'Section A'),
('Quels sont les moyens de paiement acceptés ?', 'What payment methods are accepted?', 'Practice', 'Written Expression', 'Section A'),
('Le matériel est-il fourni ?', 'Is the material provided?', 'Practice', 'Written Expression', 'Section A'),
('Puis-je changer de groupe si nécessaire ?', 'Can I change groups if necessary?', 'Practice', 'Written Expression', 'Section A'),
('Quelle est la durée totale du cours ?', 'What is the total duration of the course?', 'Practice', 'Written Expression', 'Section A'),
('Y a-t-il des aides financières disponibles ?', 'Are financial aids available?', 'Practice', 'Written Expression', 'Section A'),

-- Insert phrases for jobs theme
('Bonjour, je vous appelle concernant l’offre d’emploi.', 'Hello, I am calling about the job offer.', 'Practice', 'Written Expression', 'Section B'),
('Le poste est-il encore disponible ?', 'Is the position still available?', 'Practice', 'Written Expression', 'Section B'),
('Quelles sont les qualifications requises ?', 'What qualifications are required?', 'Practice', 'Written Expression', 'Section B'),
('Quel est le salaire proposé ?', 'What is the offered salary?', 'Practice', 'Written Expression', 'Section B'),
('Est-ce un contrat à durée déterminée ou indéterminée ?', 'Is it a fixed-term or permanent contract?', 'Practice', 'Written Expression', 'Section B'),
('Quelles sont les horaires de travail ?', 'What are the working hours?', 'Practice', 'Written Expression', 'Section B'),
('Y a-t-il des possibilités d’évolution ?', 'Are there opportunities for advancement?', 'Practice', 'Written Expression', 'Section B'),
('Où se situe le lieu de travail ?', 'Where is the workplace located?', 'Practice', 'Written Expression', 'Section B'),
('Quelles sont les responsabilités principales ?', 'What are the main responsibilities?', 'Practice', 'Written Expression', 'Section B'),
('Quand puis-je commencer ?', 'When can I start?', 'Practice', 'Written Expression', 'Section B'),
('Dois-je fournir des références ?', 'Do I need to provide references?', 'Practice', 'Written Expression', 'Section B'),
('Y a-t-il une période d’essai ?', 'Is there a trial period?', 'Practice', 'Written Expression', 'Section B'),
('Comment se déroule le processus de recrutement ?', 'How does the recruitment process work?', 'Practice', 'Written Expression', 'Section B'),
('Puis-je envoyer mon CV par email ?', 'Can I send my CV by email?', 'Practice', 'Written Expression', 'Section B'),
('À qui dois-je m’adresser pour plus d’informations ?', 'Who should I contact for more information?', 'Practice', 'Written Expression', 'Section B');
