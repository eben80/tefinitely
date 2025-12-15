<?php
session_start();
require_once __DIR__ . '/openai.php';

$level = $_POST['level'] ?? 'A1';

$messages = [
    [
        "role" => "system",
        "content" =>
            "You are a native French speaker role-playing a real-life spoken interaction.
             
             LANGUAGE RULE:
             - ALL output must be in French.
             
             ROLE MODEL:
             - You are the conversational counterpart.
             - The learner is the active participant.
             
             RULES:
             - You must NEVER speak as the learner.
             - You must ALWAYS start the conversation.
             - Keep language appropriate for level {$level}.
             
             OUTPUT FORMAT (JSON ONLY):
             {
               \"scenario\": \"Description du contexte et des rôles, en français\",
               \"assistant_opening\": \"Ta première réplique parlée, en français\"
             }"
    ],
    [
        "role" => "user",
        "content" =>
            "Crée un scénario court de conversation orale et la première réplique de l'assistant."
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
