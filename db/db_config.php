<?php
$host = 'localhost';               // MySQL server
$db   = 'french_practice';         // Your database name
$user = 'tefuser';                 // Your MySQL username
$pass = 'tefpass123';              // Your MySQL password

// Create a new MySQL connection
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$conn->set_charset("utf8mb4");  // IMPORTANT: Set charset to utf8mb4 here

// --- Debug Logging Function ---
// function debug_log($message) {
//     $log_file = __DIR__ . '/logs/php_debug.log';
//    $timestamp = date('Y-m-d H:i:s');
//     $log_message = "[$timestamp] " . print_r($message, true) . "\n";
//     file_put_contents($log_file, $log_message, FILE_APPEND);
// }
?>
