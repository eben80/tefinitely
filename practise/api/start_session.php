<?php
session_start();
require_once __DIR__ . '/openai.php';

$level = $_POST['level'] ?? 'A1';

$messages = [
    [
        "role" => "system",
        "content" => "You are a French language tutor. Speak only French."
    ],
    [
        "role" => "user",
        "content" =>
            "Create a short speaking scenario for a {$level} learner.
             Respond ONLY in JSON with keys:
             scenario, first_prompt."
    ]
];

$response = openai_chat($messages);

// Parse JSON from assistant
$content = trim($response['content']);
$start = strpos($content, '{');
$end = strrpos($content, '}');

$data = [];
if ($start !== false && $end !== false) {
    $data = json_decode(substr($content, $start, $end - $start + 1), true);
}

if (!$data) {
    echo json_encode([]);
    exit;
}

// Initialize conversation state
$_SESSION['scenario'] = $data['scenario'];
$_SESSION['conversation'] = [
    [
        "role" => "assistant",
        "content" => $data['first_prompt']
    ]
];

echo json_encode([
    "scenario" => $data['scenario'],
    "assistant" => $data['first_prompt']
]);
