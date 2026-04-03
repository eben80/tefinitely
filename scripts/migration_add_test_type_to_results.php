<?php
require_once __DIR__ . '/../db/db_config.php';

function log_msg($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
}

log_msg("Starting migration: Add 'test_type' to 'level_test_results' table...");

// Check if column exists
$check_query = "SHOW COLUMNS FROM level_test_results LIKE 'test_type'";
$result = $conn->query($check_query);

if ($result && $result->num_rows == 0) {
    log_msg("Column 'test_type' does not exist. Adding it...");
    $alter_query = "ALTER TABLE level_test_results ADD COLUMN test_type VARCHAR(50) NOT NULL DEFAULT 'vocabulary' AFTER user_id";
    if ($conn->query($alter_query)) {
        log_msg("Successfully added 'test_type' column.");

        // Also add the index
        $index_query = "ALTER TABLE level_test_results ADD INDEX idx_user_type (user_id, test_type)";
        if ($conn->query($index_query)) {
            log_msg("Successfully added index 'idx_user_type'.");
        } else {
            log_msg("Error adding index: " . $conn->error);
        }
    } else {
        log_msg("Error adding column: " . $conn->error);
    }
} else {
    log_msg("Column 'test_type' already exists. Skipping.");
}

log_msg("Migration complete.");
?>
