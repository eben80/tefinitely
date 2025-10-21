<?php
require_once __DIR__ . '/../db/db_config.php';

// SQL file to execute
$sql_file = __DIR__ . '/../sql/update_flashcard_data.sql';

echo "<h2>Executing SQL File: " . basename($sql_file) . "</h2>";

$sql = file_get_contents($sql_file);
if ($conn->multi_query($sql)) {
    // Must consume all results from multi_query
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Successfully executed " . basename($sql_file) . ".<br><br>";
} else {
    echo "Error executing " . basename($sql_file) . ": " . $conn->error . "<br><br>";
    // Stop execution if the file fails
    exit;
}

echo "<h3>Execution complete!</h3>";
$conn->close();
?>
