-- Add column to store the cloze script as JSON
ALTER TABLE `roleplay_scenarios`
ADD COLUMN `cloze_script_json` JSON NULL AFTER `description`;

-- Update existing scenarios with sample cloze scripts
-- Note: Using JSON_OBJECT to construct valid JSON.

UPDATE `roleplay_scenarios`
SET `cloze_script_json` = JSON_OBJECT(
  'title', 'Calling the Fruit Farm',
  'lines', JSON_ARRAY(
    JSON_OBJECT('speaker', 'Owner', 'line', 'Bonjour! Ferme de la Rivière, que puis-je pour vous?'),
    JSON_OBJECT('speaker', 'Student', 'line', 'Bonjour, je vous appelle au sujet de votre annonce pour la cueillette de fruits. Est-ce que c''est toujours d''actualité?', 'is_blank', TRUE, 'expected', 'Bonjour, je vous appelle au sujet de votre annonce pour la cueillette de fruits. Est-ce que c''est toujours d''actualité?'),
    JSON_OBJECT('speaker', 'Owner', 'line', 'Oui, tout à fait! La saison des fraises bat son plein.'),
    JSON_OBJECT('speaker', 'Student', 'line', 'Super! J''aurais quelques questions. Premièrement, quels sont vos horaires?', 'is_blank', TRUE, 'expected', 'Super! J''aurais quelques questions. Premièrement, quels sont vos horaires?'),
    JSON_OBJECT('speaker', 'Owner', 'line', 'Nous sommes ouverts de 9h à 18h, tous les jours sauf le lundi.'),
    JSON_OBJECT('speaker', 'Student', 'line', 'D''accord. Et combien ça coûte?', 'is_blank', TRUE, 'expected', 'D''accord. Et combien ça coûte?'),
    JSON_OBJECT('speaker', 'Owner', 'line', 'L''entrée est gratuite, vous ne payez que les fruits que vous cueillez, au poids.'),
    JSON_OBJECT('speaker', 'Student', 'line', 'Parfait, merci beaucoup pour les informations.', 'is_blank', TRUE, 'expected', 'Parfait, merci beaucoup pour les informations.')
  )
)
WHERE `id` = 1;


UPDATE `roleplay_scenarios`
SET `cloze_script_json` = JSON_OBJECT(
  'title', 'Inquiring about Babysitting',
  'lines', JSON_ARRAY(
    JSON_OBJECT('speaker', 'Parent', 'line', 'Allô, bonjour.'),
    JSON_OBJECT('speaker', 'Student', 'line', 'Bonjour Madame/Monsieur, je me permets de vous appeler concernant votre annonce pour du baby-sitting.', 'is_blank', TRUE, 'expected', 'Bonjour Madame/Monsieur, je me permets de vous appeler concernant votre annonce pour du baby-sitting.'),
    JSON_OBJECT('speaker', 'Parent', 'line', 'Ah oui, bonjour. Oui, c''est bien moi. Vous êtes intéressé(e)?'),
    JSON_OBJECT('speaker', 'Student', 'line', 'Oui, tout à fait. Pourriez-vous me dire combien d''enfants vous avez?', 'is_blank', TRUE, 'expected', 'Oui, tout à fait. Pourriez-vous me dire combien d''enfants vous avez?'),
    JSON_OBJECT('speaker', 'Parent', 'line', 'J''ai deux enfants, un garçon de 5 ans et une fille de 8 ans.'),
    JSON_OBJECT('speaker', 'Student', 'line', 'D''accord. Et quelles seraient les tâches principales?', 'is_blank', TRUE, 'expected', 'D''accord. Et quelles seraient les tâches principales?'),
    JSON_OBJECT('speaker', 'Parent', 'line', 'Il faudrait aller les chercher à l''école, leur préparer le goûter et les aider avec leurs devoirs.'),
    JSON_OBJECT('speaker', 'Student', 'line', 'Entendu. Je vous remercie.', 'is_blank', TRUE, 'expected', 'Entendu. Je vous remercie.')
  )
)
WHERE `id` = 2;
