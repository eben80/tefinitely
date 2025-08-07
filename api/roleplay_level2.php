<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../db/db_config.php';

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response_data = [];

switch ($action) {
    case 'start':
        $scenario_id = $_POST['scenario_id'] ?? 0;
        if ($scenario_id == 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Scenario ID is required.']);
            exit;
        }

        // Fetch the scenario from the DB
        $stmt = $conn->prepare("SELECT english_prompts_json FROM roleplay_scenarios WHERE id = ?");
        $stmt->bind_param("i", $scenario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $script = json_decode($row['english_prompts_json'], true);

            // Initialize session for the roleplay
            $_SESSION['roleplay_level2'] = [
                'scenario_id' => $scenario_id,
                'title' => $script['title'],
                'prompts' => $script['prompts'],
                'current_prompt_index' => 0,
                'score' => 0
            ];

            // Send back the first prompt
            $response_data = [
                'status' => 'success',
                'message' => 'Roleplay started.',
                'prompt' => $script['prompts'][0]['prompt'],
                'progress' => '1 / ' . count($script['prompts'])
            ];
        } else {
            http_response_code(404);
            $response_data = ['status' => 'error', 'message' => 'Scenario not found.'];
        }
        $stmt->close();
        break;

    case 'respond':
        // This will be implemented next
        $response_data = ['status' => 'pending', 'message' => 'Response handling not yet implemented.'];
        break;

    default:
        http_response_code(400);
        $response_data = ['status' => 'error', 'message' => 'Invalid action.'];
        break;
}

echo json_encode($response_data, JSON_UNESCAPED_UNICODE);
$conn->close();
?>
