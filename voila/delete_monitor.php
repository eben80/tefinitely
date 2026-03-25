<?php
require_once 'config.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        // Fetch last screenshot before deleting record
        $stmt = $pdo->prepare("SELECT last_screenshot FROM monitors WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $monitor = $stmt->fetch();

        if ($monitor && $monitor['last_screenshot']) {
            $filepath = __DIR__ . '/screenshots/' . $monitor['last_screenshot'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM monitors WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    }
}

redirect('index.php');
?>
