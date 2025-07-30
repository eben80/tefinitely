<?php
require_once 'db_config.php';

$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "<h3>Tables in database '{$db}':</h3>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error showing tables: " . $conn->error;
}

$conn->close();
?>
