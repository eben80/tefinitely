-- Connect to the correct database
USE french_practice;

-- Create the table only if it doesn't exist
CREATE TABLE IF NOT EXISTS phrases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    french_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    english_translation TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    theme VARCHAR(50),
    section VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert phrases
INSERT INTO phrases (french_text, english_translation, theme, section) VALUES

-- EVENTS
('Combien coûte l’entrée pour une personne adulte ?', 'How much is entry for an adult?', 'events', 'section_a'),
('Est-ce que l’événement a lieu en intérieur ou en extérieur ?', 'Is the event held indoors or outdoors?', 'events', 'section_a'),
('Est-ce qu’il y a un parking à proximité ?', 'Is there parking nearby?', 'events', 'section_a'),
('Peut-on venir avec un animal de compagnie ?', 'Can we bring a pet?', 'events', 'section_a'),
('L’entrée est-elle gratuite pour les enfants ?', 'Is entrance free for children?', 'events', 'section_a'),
('Y aura-t-il de la restauration sur place ?', 'Will there be food available on-site?', 'events', 'section_a'),
('Combien de temps dure l’événement ?', 'How long does the event last?', 'events', 'section_a'),
('Faut-il imprimer les billets ou peut-on les présenter sur téléphone ?', 'Do we need to print the tickets or can we show them on a phone?', 'events', 'section_a'),
('Y a-t-il des activités prévues pour les familles ?', 'Are there activities planned for families?', 'events', 'section_a'),
('Quel est le programme de l’événement ?', 'What is the event schedule?', 'events', 'section_a'),
('Faut-il réserver à l’avance ?', 'Do we need to book in advance?', 'events', 'section_a'),
('Combien de personnes peut-on inscrire ?', 'How many people can be registered?', 'events', 'section_a'),
('L’événement est-il accessible aux personnes handicapées ?', 'Is the event accessible to people with disabilities?', 'events', 'section_a'),
('Quel est l’adresse exacte de l’événement ?', 'What is the exact address of the event?', 'events', 'section_a'),
('Peut-on obtenir un remboursement en cas d’annulation ?', 'Can we get a refund in case of cancellation?', 'events', 'section_a'),

-- VOLUNTEERING
('Le bénévolat est-il ponctuel ou régulier ?', 'Is the volunteering temporary or regular?', 'volunteering', 'section_a'),
('Est-ce qu’on peut choisir ses horaires ?', 'Can we choose our schedule?', 'volunteering', 'section_a'),
('Où se déroule l’activité de bénévolat ?', 'Where does the volunteering take place?', 'volunteering', 'section_a'),
('Combien de bénévoles recherchez-vous ?', 'How many volunteers are you looking for?', 'volunteering', 'section_a'),
('Quel âge minimum faut-il avoir pour participer ?', 'What is the minimum age required to participate?', 'volunteering', 'section_a'),
('Y a-t-il une assurance pour les bénévoles ?', 'Is there insurance for volunteers?', 'volunteering', 'section_a'),
('Quelles compétences sont utiles pour cette activité ?', 'What skills are helpful for this activity?', 'volunteering', 'section_a'),
('Doit-on apporter du matériel personnel ?', 'Do we need to bring any personal equipment?', 'volunteering', 'section_a'),
('Est-ce que les frais de déplacement sont remboursés ?', 'Are travel expenses reimbursed?', 'volunteering', 'section_a'),
('Faut-il signer un contrat ou une convention ?', 'Do we need to sign a contract or agreement?', 'volunteering', 'section_a'),
('Y a-t-il une formation prévue ?', 'Is there a training session planned?', 'volunteering', 'section_a'),
('Les repas sont-ils fournis ?', 'Are meals provided?', 'volunteering', 'section_a'),
('Combien d’heures par semaine doit-on s’engager ?', 'How many hours per week are required?', 'volunteering', 'section_a'),
('Puis-je choisir l’activité qui me plaît ?', 'Can I choose the activity I like?', 'volunteering', 'section_a'),
('Est-ce que c’est ouvert aux étudiants étrangers ?', 'Is it open to foreign students?', 'volunteering', 'section_a'),

-- SECOND-HAND ITEMS
('Depuis combien de temps possédez-vous cet objet ?', 'How long have you owned this item?', 'second_hand', 'section_a'),
('Y a-t-il des défauts ou des réparations à prévoir ?', 'Are there any defects or repairs needed?', 'second_hand', 'section_a'),
('Pourquoi le vendez-vous ?', 'Why are you selling it?', 'second_hand', 'section_a'),
('Est-ce que l’article est encore sous garantie ?', 'Is the item still under warranty?', 'second_hand', 'section_a'),
('Peut-on payer en espèces ?', 'Can we pay in cash?', 'second_hand', 'section_a'),
('Faites-vous une livraison ou faut-il venir le chercher ?', 'Do you deliver or must it be picked up?', 'second_hand', 'section_a'),
('Est-ce que le prix est ferme ou ouvert à négociation ?', 'Is the price firm or negotiable?', 'second_hand', 'section_a'),
('Est-ce que l’article a beaucoup servi ?', 'Has the item been used a lot?', 'second_hand', 'section_a'),
('Y a-t-il des accessoires inclus ?', 'Are there any accessories included?', 'second_hand', 'section_a'),
('Est-il possible de venir le voir aujourd’hui ?', 'Is it possible to come see it today?', 'second_hand', 'section_a'),
('Quel est l’état général de l’objet ?', 'What is the general condition of the item?', 'second_hand', 'section_a'),
('Peut-on tester l’objet avant l’achat ?', 'Can we test the item before buying?', 'second_hand', 'section_a'),
('Acceptez-vous les paiements par virement ?', 'Do you accept bank transfers?', 'second_hand', 'section_a'),
('L’objet est-il encore disponible ?', 'Is the item still available?', 'second_hand', 'section_a'),
('Avez-vous la facture originale ?', 'Do you have the original invoice?', 'second_hand', 'section_a');

-- Insert phrases for General theme
INSERT INTO phrases (french_text, english_translation, theme, section) VALUES
-- Introductions
('Bonjour, [Votre nom] à l''appareil.', 'Hello, [Your Name] speaking.', 'General', 'section_a'),
('Je vous appelle au sujet de...', 'I am calling you about...', 'General', 'section_a'),
('Serait-il possible de parler à [Nom de la personne]?', 'Would it be possible to speak to [Person''s Name]?', 'General', 'section_a'),

-- Asking about the ad
('Est-ce que je parle bien à la personne qui a posté l''annonce ?', 'Am I speaking with the person who posted the ad?', 'General', 'section_a'),
('C''est bien vous qui avez publié l''annonce pour [sujet de l''annonce]?', 'Are you the one who published the ad for [subject of the ad]?', 'General', 'section_a'),

-- Asking for time
('Auriez-vous quelques instants à m''accorder pour quelques questions ?', 'Would you have a few moments for a few questions?', 'General', 'section_a'),
('Seriez-vous disponible pour répondre à quelques questions ?', 'Would you be available to answer a few questions?', 'General', 'section_a'),
('Cela ne vous prendra que quelques minutes.', 'It will only take a few minutes.', 'General', 'section_a'),

-- Interjections
('D''accord, je vois.', 'Okay, I see.', 'General', 'section_a'),
('C''est noté.', 'Noted.', 'General', 'section_a'),
('Parfait.', 'Perfect.', 'General', 'section_a'),
('Intéressant.', 'Interesting.', 'General', 'section_a'),
('Absolument.', 'Absolutely.', 'General', 'section_a'),
('Ça a l''air bien.', 'That sounds good.', 'General', 'section_a'),
('Ça semble intéressant.', 'That seems interesting.', 'General', 'section_a'),


-- Closing the call
('Je vous remercie pour votre temps et pour les informations.', 'Thank you for your time and for the information.', 'General', 'section_a'),
('Merci beaucoup pour votre aide.', 'Thank you very much for your help.', 'General', 'section_a'),
('Je vais étudier la question et je vous recontacterai.', 'I will consider the matter and get back to you.', 'General', 'section_a'),
('Passez une excellente journée.', 'Have an excellent day.', 'General', 'section_a'),
('Au revoir, Madame/Monsieur.', 'Goodbye, Madam/Sir.', 'General', 'section_a');
