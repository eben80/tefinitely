<?php
require_once 'config.php';

if (!is_logged_in()) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $is_paused = filter_input(INPUT_POST, 'is_paused', FILTER_VALIDATE_INT);

    if ($id !== false && ($is_paused === 0 || $is_paused === 1)) {
        $stmt = $pdo->prepare("UPDATE monitors SET is_paused = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$is_paused, $id, $_SESSION['user_id']]);
    }
}

redirect('index.php');
?>
