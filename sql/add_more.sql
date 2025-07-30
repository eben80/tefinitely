USE french_practice;

INSERT INTO phrases (french_text, english_translation, theme, section)
VALUES
-- Events
('Bonjour, je vous appelle concernant l’annonce pour le concert. Est-ce qu’il reste des places disponibles ?', 'Hello, I’m calling about the concert ad. Are there still tickets available?', 'events', 'section_a'),
('À quelle heure commence l’événement exactement ?', 'What time does the event start exactly?', 'events', 'section_a'),
('Faut-il réserver à l’avance ou peut-on acheter les billets sur place ?', 'Do we need to book in advance or can we buy tickets at the venue?', 'events', 'section_a'),
('L’événement est-il adapté aux enfants ?', 'Is the event suitable for children?', 'events', 'section_a'),
('Est-ce que le lieu est facilement accessible en transport en commun ?', 'Is the location easily accessible by public transport?', 'events', 'section_a'),

-- Volunteering
('Bonjour, je souhaite avoir des renseignements sur l’annonce de bénévolat.', 'Hello, I’d like information about the volunteering ad.', 'volunteering', 'section_a'),
('Quelles sont les missions exactes pour les bénévoles ?', 'What are the exact duties for volunteers?', 'volunteering', 'section_a'),
('Faut-il une expérience particulière pour participer ?', 'Is any specific experience required to take part?', 'volunteering', 'section_a'),
('Quels sont les horaires de travail pour les bénévoles ?', 'What are the working hours for volunteers?', 'volunteering', 'section_a'),
('Y a-t-il une formation prévue avant de commencer ?', 'Is any training provided before starting?', 'volunteering', 'section_a'),

-- Second-Hand Items
('Je vous appelle à propos de votre annonce pour le vélo d’occasion.', 'I’m calling about your ad for the second-hand bike.', 'second_hand', 'section_a'),
('L’objet est-il encore disponible ?', 'Is the item still available?', 'second_hand', 'section_a'),
('Dans quel état est l’article exactement ?', 'What condition is the item in exactly?', 'second_hand', 'section_a'),
('Le prix est-il négociable ?', 'Is the price negotiable?', 'second_hand', 'section_a'),
('Où peut-on venir voir ou récupérer l’objet ?', 'Where can we come to see or pick up the item?', 'second_hand', 'section_a');
