<?php
require_once __DIR__ . '/../db/db_config.php';

echo "Changing default value of celpip_enabled to 0...\n";

$sql = "ALTER TABLE users ALTER COLUMN celpip_enabled SET DEFAULT 0";
if ($conn->query($sql)) {
    echo "Successfully updated default value.\n";
} else {
    echo "Error updating default value: " . $conn->error . "\n";
}

$conn->close();
