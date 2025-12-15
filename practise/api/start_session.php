<?php
session_start();
require_once __DIR__ . '/openai.php';

$level = $_POST['level'] ?? 'A1';

$messages = [
    [
        "role" => "system",
        "content" =>
            "You are a native French speaker role-playing a real-life spoken interaction.
             
             ROLE MODEL:
             - You are the conversational counterpart.
             - The learner is the active participant.
             
             RULES:
             - You must NEVER speak as the learner.
             - You must NEVER say what the learner should say.
             - You must ALWAYS start the conversation.
             - Keep language appropriate for level {$level}.
             
             OUTPUT FORMAT (JSON ONLY):
             {
               \"scenario\": \"Describe the situation and roles\",
               \"assistant_opening\": \"Your first spoken line\"
             }"
    ],
    [
        "role" => "user",
        "content" =>
            "Create a short speaking scenario where the learner must respond naturally."
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
