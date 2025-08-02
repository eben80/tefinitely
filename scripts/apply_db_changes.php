<?php
require_once __DIR__ . '/../db/db_config.php';

$sql = file_get_contents(__DIR__ . '/../sql/update_user_tracking.sql');

if ($conn->multi_query($sql)) {
    echo "SQL script executed successfully.\n";
} else {
    echo "Error executing SQL script: " . $conn->error . "\n";
}

$conn->close();
?>
