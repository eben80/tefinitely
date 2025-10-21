<?php
// A simple script to execute a .sql file
// Usage: php execute_sql_file.php <filename.sql>

require_once __DIR__ . '/../db/db_config.php';

// The SQL file to be executed
$sqlFile = __DIR__ . '/../sql/fix_progress_tracking.sql';

// Check if the file exists
if (!file_exists($sqlFile)) {
    die("<h2>Error: SQL file not found at: " . htmlspecialchars($sqlFile) . "</h2>");
}

echo "<h2>Executing SQL File: " . htmlspecialchars(basename($sqlFile)) . "</h2>";

// Read the SQL file
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die("<h2>Error: Unable to read the SQL file.</h2>");
}

// Execute the multi-query
if ($conn->multi_query($sql)) {
    $i = 0;
    do {
        $i++;
        if ($conn->more_results()) {
            if (!$conn->next_result()) {
                echo "<h3>Error moving to next result: " . $conn->error . "</h3>";
                break;
            }
        }
    } while ($conn->more_results());
    echo "<h3>Successfully executed all queries.</h3>";
} else {
    echo "<h2>Error executing SQL: " . $conn->error . "</h2>";
}

$conn->close();
?>
