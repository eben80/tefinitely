<?php
// This script should be run via a cron job
// It deletes accounts that have not been verified for more than 24 hours.

require_once '../../db/db_config.php';

// Only allow execution from CLI or a specific secret key
if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || $_GET['key'] !== 'YOUR_CLEANUP_SECRET_KEY')) {
    http_response_code(403);
    die("Unauthorized.");
}

$stmt = $conn->prepare("DELETE FROM users WHERE email_verified = FALSE AND role = 'user' AND created_at < NOW() - INTERVAL 24 HOUR");

if ($stmt->execute()) {
    $deleted_rows = $stmt->affected_rows;
    echo "Successfully deleted $deleted_rows unverified users.";
} else {
    echo "Error deleting unverified users: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
