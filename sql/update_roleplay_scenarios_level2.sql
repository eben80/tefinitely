-- Add column to store the English prompt scripts as JSON
ALTER TABLE `roleplay_scenarios`
ADD COLUMN `english_prompts_json` JSON NULL AFTER `cloze_script_json`;

-- Update existing scenarios with sample English prompt scripts
UPDATE `roleplay_scenarios`
SET `english_prompts_json` = JSON_OBJECT(
  'title', 'Fruit Picking Inquiry (Prompts)',
  'prompts', JSON_ARRAY(
    JSON_OBJECT('prompt', 'Greet the person and say you are calling about their ad for fruit picking.', 'keywords', JSON_ARRAY('bonjour', 'annonce', 'cueillette')),
    JSON_OBJECT('prompt', 'Ask where they are located.', 'keywords', JSON_ARRAY('où', 'situé', 'adresse', 'se trouve')),
    JSON_OBJECT('prompt', 'Ask about the opening hours.', 'keywords', JSON_ARRAY('quand', 'heures', 'ouvert')),
    JSON_OBJECT('prompt', 'Ask if there is a place to park.', 'keywords', JSON_ARRAY('parking', 'stationner', 'garer')),
    JSON_OBJECT('prompt', 'Thank them for the information.', 'keywords', JSON_ARRAY('merci', 'remercie', 'informations'))
  )
)
WHERE `id` = 1;

UPDATE `roleplay_scenarios`
SET `english_prompts_json` = JSON_OBJECT(
  'title', 'Babysitting Inquiry (Prompts)',
  'prompts', JSON_ARRAY(
    JSON_OBJECT('prompt', 'Greet the person and state the reason for your call (their ad for a babysitter).', 'keywords', JSON_ARRAY('bonjour', 'annonce', 'baby-sitting', 'garder')),
    JSON_OBJECT('prompt', 'Ask how many children they have.', 'keywords', JSON_ARRAY('combien', 'enfants')),
    JSON_OBJECT('prompt', 'Ask for the ages of the children.', 'keywords', JSON_ARRAY('âge', 'quel âge')),
    JSON_OBJECT('prompt', 'Ask what the main tasks would be.', 'keywords', JSON_ARRAY('tâches', 'faire', 'occuper')),
    JSON_OBJECT('prompt', 'Ask if the school is nearby.', 'keywords', JSON_ARRAY('école', 'proche', 'près', 'loin'))
  )
)
WHERE `id` = 2;
