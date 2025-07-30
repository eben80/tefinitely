<?php
require_once 'db_config.php';

// SQL files to execute
$sql_files = [
    __DIR__ . '/../sql/create_phrases_table.sql',
    __DIR__ . '/../sql/create_user_management_tables.sql'
];

echo "<h2>Database Setup</h2>";

foreach ($sql_files as $file) {
    echo "Executing $file...<br>";
    $sql = file_get_contents($file);
    if ($conn->multi_query($sql)) {
        // Must consume all results from multi_query
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        echo "Successfully executed $file.<br><br>";
    } else {
        echo "Error executing $file: " . $conn->error . "<br><br>";
        // Stop execution if one file fails
        exit;
    }
}

echo "<h3>Database setup complete!</h3>";
$conn->close();
?>
