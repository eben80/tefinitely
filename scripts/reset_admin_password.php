<?php
// This script should be run from the command line.
// Usage: php scripts/reset_admin_password.php your_new_password

// We need to adjust the path to the db config since this script is in a different directory
require_once __DIR__ . '/../db/db_config.php';

if ($argc < 2) {
    echo "Usage: php scripts/reset_admin_password.php <new_password>\n";
    exit(1);
}

$new_password = $argv[1];
$admin_username = 'sbmail246@gmail.com';

if (strlen($new_password) < 8) {
    echo "Error: Password must be at least 8 characters long.\n";
    exit(1);
}

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Prepare the statement to update the password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $admin_username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Successfully updated the password for user '{$admin_username}'.\n";
    } else {
        echo "Error: Could not find user '{$admin_username}'. No changes were made.\n";
    }
} else {
    echo "Error executing database update: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>
