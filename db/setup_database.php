<?php
require_once 'db_config.php';

// SQL files to execute
$sql_files = [
    __DIR__ . '/../sql/create_phrases_table.sql',
    __DIR__ . '/../sql/create_user_management_tables.sql',
    __DIR__ . '/../sql/create_password_reset_table.sql',
    __DIR__ . '/../sql/update_user_tracking.sql',
    __DIR__ . '/../sql/create_training_tables.sql',
    __DIR__ . '/../sql/alter_roleplay_scenarios.sql',
    __DIR__ . '/../sql/update_roleplay_scenarios_level2.sql',
    __DIR__ . '/../sql/add_more_drills.sql',
    __DIR__ . '/../sql/update_subscription_schema.sql',
    __DIR__ . '/../sql/create_dialogue_progress_table.sql',
    __DIR__ . '/../sql/create_openai_calls_log_table.sql',
    __DIR__ . '/../sql/create_subscription_payments_table.sql',
    __DIR__ . '/../sql/create_admin_audit_logs_table.sql',
    __DIR__ . '/../sql/create_login_history_table.sql',
    __DIR__ . '/../sql/create_support_tickets_table.sql',
    __DIR__ . '/../sql/create_payment_plans_table.sql'
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
