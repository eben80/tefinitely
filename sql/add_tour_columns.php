<?php
require_once __DIR__ . '/../db/db_config.php';

$sql = "ALTER TABLE users
        ADD COLUMN tour_section_a_completed BOOLEAN NOT NULL DEFAULT FALSE,
        ADD COLUMN tour_section_b_completed BOOLEAN NOT NULL DEFAULT FALSE";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully";
} else {
    echo "Error adding columns: " . $conn->error;
}

$conn->close();
?>
