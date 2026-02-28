<?php
/**
 * Shared helper for logging administrative actions.
 */
function logAdminAction($conn, $admin_id, $action, $target_id = null, $details = null) {
    $stmt = $conn->prepare("INSERT INTO admin_audit_logs (admin_id, action, target_id, details) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isis", $admin_id, $action, $target_id, $details);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false;
}
?>
