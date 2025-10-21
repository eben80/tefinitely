
import json

def generate_sql_script():
    """
    Reads the JSON data, generates the complete SQL script, and overwrites the
    target SQL file.
    """
    json_file_path = 'sql/SectionA_B1.json'
    output_sql_path = 'sql/update_flashcard_data.sql'

    with open(json_file_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    sql_content = """-- This script updates the flashcard data for Section A, Level B1.

-- Alter table structures
ALTER TABLE `phrases`
  ADD COLUMN `section` VARCHAR(255) NULL DEFAULT NULL AFTER `id`,
  ADD COLUMN `level` VARCHAR(50) NULL DEFAULT NULL AFTER `theme`,
  ADD COLUMN `topic_fr` VARCHAR(255) NULL DEFAULT NULL AFTER `english_translation`,
  ADD COLUMN `topic_en` VARCHAR(255) NULL DEFAULT NULL AFTER `topic_fr`;

ALTER TABLE `users`
  ADD COLUMN `last_section` VARCHAR(255) NULL DEFAULT NULL AFTER `last_topic`,
  ADD COLUMN `last_level` VARCHAR(50) NULL DEFAULT NULL AFTER `last_section`;

-- Clear old data
TRUNCATE TABLE `phrases`;
TRUNCATE TABLE `user_progress`;

-- Reset user progress to avoid foreign key issues and reflect new content
UPDATE `users` SET `last_topic` = NULL, `last_card_index` = NULL, `last_section` = NULL, `last_level` = NULL;

-- Insert new phrases for Section A / Level B1
"""

    for topic_data in data:
        topic_fr = topic_data['topic_fr'].replace("'", "''")
        topic_en = topic_data['topic_en'].replace("'", "''")
        theme = topic_en.lower().replace(' ', '_').replace("'", "").replace('`','').replace("/", "_").replace("-","").replace(",","")

        sql_content += f"\\n-- Topic: {topic_en}\\n"
        for question in topic_data['questions']:
            french_text = question['fr'].replace("'", "''")
            english_translation = question['en'].replace("'", "''")
            sql_content += (
                f"INSERT INTO `phrases` (`section`, `theme`, `level`, `french_text`, `english_translation`, `topic_fr`, `topic_en`) "
                f"VALUES ('Section A', '{theme}', 'B1', '{french_text}', '{english_translation}', '{topic_fr}', '{topic_en}');\\n"
            )

    with open(output_sql_path, 'w', encoding='utf-8') as f:
        f.write(sql_content)

if __name__ == "__main__":
    generate_sql_script()
    print(f"Successfully generated sql/update_flashcard_data.sql")
