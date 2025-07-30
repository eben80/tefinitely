CREATE TABLE phrases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  french_text TEXT NOT NULL,
  english_translation TEXT NOT NULL,
  theme VARCHAR(100) NOT NULL
);

INSERT INTO phrases (french_text, english_translation, theme) VALUES
('Je me r�veille � sept heures.', 'I wake up at 7 a.m.', 'daily_life'),
('Est-ce que le logement est meubl� ?', 'Is the housing furnished?', 'housing'),
('Combien co�te l�inscription ?', 'How much does the registration cost?', 'courses');
