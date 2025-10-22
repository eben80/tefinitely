<?php
// scripts/import_section_b_data.php

// This script is intended to be run from the command line.
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

require_once __DIR__ . '/../api/services/config.php';
require_once __DIR__ . '/../api/services/Database.php';

function generateThemeSlug($topic_en) {
    // A simple function to create a URL-friendly slug
    $clean = preg_replace('/[^a-zA-Z0-9\s-]/', '', $topic_en);
    $clean = strtolower(trim($clean));
    $slug = preg_replace('/[\s-]+/', '-', $clean);
    return 'B1-' . $slug;
}

echo "Starting import of Section B data...\n";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json_path = __DIR__ . '/../sql/SectionB_B1.json';
    if (!file_exists($json_path)) {
        throw new Exception("JSON file not found at: " . $json_path);
    }

    $json_data = file_get_contents($json_path);
    $topics_data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON: " . json_last_error_msg());
    }

    $pdo->beginTransaction();

    $section = 'Section_B';
    $level = '1'; // Level is part of the theme for this structure

    $insert_phrase_stmt = $pdo->prepare(
        "INSERT INTO phrases (french_text, english_translation, theme, section, topic_en, topic_fr, level) VALUES (:french_text, :english_translation, :theme, :section, :topic_en, :topic_fr, :level)"
    );

    foreach ($topics_data as $topic) {
        $topic_en = $topic['topic_en'];
        $topic_fr = $topic['topic_fr'];
        // The theme now includes the level
        $theme = generateThemeSlug($topic_en);

        echo "Processing topic: {$topic_en}\n";

        foreach ($topic['sentences'] as $sentence) {
            $insert_phrase_stmt->execute([
                ':french_text' => $sentence['fr'],
                ':english_translation' => $sentence['en'],
                ':theme' => $theme,
                ':section' => $section,
                ':topic_en' => $topic_en,
                ':topic_fr' => $topic_fr,
                ':level' => $level
            ]);
        }
        echo " -> Inserted " . count($topic['sentences']) . " phrases for theme '{$theme}'.\n";
    }

    $pdo->commit();

    echo "\nImport completed successfully!\n";

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\nERROR: An error occurred during the import.\n";
    echo "Message: " . $e->getMessage() . "\n";
    exit(1);
}
