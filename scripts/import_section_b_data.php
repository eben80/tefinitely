<?php
// scripts/import_section_b_data.php

// This script is intended to be run from the command line.
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

// Use the same database connection method as other CLI scripts
require_once __DIR__ . '/../db/db_config.php';

function generateThemeSlug($topic_en) {
    // A simple function to create a URL-friendly slug
    $clean = preg_replace('/[^a-zA-Z0-9\s-]/', '', $topic_en);
    $clean = strtolower(trim($clean));
    $slug = preg_replace('/[\s-]+/', '-', $clean);
    // Truncate to a reasonable length
    $parts = explode('-', $slug);
    $short_slug = implode('-', array_slice($parts, 0, 5));
    return 'B1-' . $short_slug;
}

echo "Starting import of Section B data...\n";

try {
    // The $conn (mysqli) variable should be available from db_config.php
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed. Check db/db_config.php. Error: " . ($conn->connect_error ?? 'Unknown'));
    }

    $json_path = __DIR__ . '/../sql/SectionB_B1.json';
    if (!file_exists($json_path)) {
        throw new Exception("JSON file not found at: " . $json_path);
    }

    $json_data = file_get_contents($json_path);
    $topics_data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON: " . json_last_error_msg());
    }

    $conn->begin_transaction();

    $section = 'Section_B';
    $level = 1; // Use integer for level as it's likely an INT column

    $insert_phrase_stmt = $conn->prepare(
        "INSERT INTO phrases (french_text, english_translation, theme, section, topic_en, topic_fr, level) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$insert_phrase_stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    foreach ($topics_data as $topic) {
        $topic_en = $topic['topic_en'];
        $topic_fr = $topic['topic_fr'];
        $theme = generateThemeSlug($topic_en);

        echo "Processing topic: {$topic_en}\n";

        foreach ($topic['sentences'] as $sentence) {
            $insert_phrase_stmt->bind_param("ssssssi",
                $sentence['fr'],
                $sentence['en'],
                $theme,
                $section,
                $topic_en,
                $topic_fr,
                $level
            );
            $insert_phrase_stmt->execute();
        }
        echo " -> Inserted " . count($topic['sentences']) . " phrases for theme '{$theme}'.\n";
    }

    $conn->commit();
    echo "\nImport completed successfully!\n";

} catch (Exception $e) {
    if (isset($conn) && $conn->errno) { // Check for mysqli error number
        $conn->rollback();
    }
    echo "\nERROR: An error occurred during the import.\n";
    echo "Message: " . $e->getMessage() . "\n";
    exit(1);

} finally {
    if (isset($insert_phrase_stmt)) {
        $insert_phrase_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
