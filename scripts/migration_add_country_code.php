<?php
require_once __DIR__ . '/../db/db_config.php';

$sql = "ALTER TABLE `login_history` ADD COLUMN `country_code` VARCHAR(2) DEFAULT NULL AFTER `ip_address`;";

if ($conn->query($sql) === TRUE) {
    echo "Column country_code added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
