<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$level = $_POST['level'] ?? 'A2';

/**
 * SYSTEM prompt — generalized for any learner scenario
 * LANGUAGE: French
 * ROLE: AI = counterpart, user = learner
 * OUTPUT: JSON ONLY
 */
$messages = [
    [
        "role" => "system",
        "content" =>
            "You are a native French speaker role-playing a real-life spoken interaction.

             LANGUAGE RULE:
             - All output must be in French.

             ROLE MODEL:
             - You are the conversational counterpart.
             - The learner is the active participant.

             RULES:
             - You must NEVER speak as the learner.
             - Where the scenario is one of customer/client to a vendor/waiter/seller, the learner should be in role of customer or client.
             - You must ALWAYS start the conversation.
             - Keep language appropriate for level {$level}.
             - Spoken dialogue ONLY goes in DIALOGUE.
             - Corrections or suggestions ONLY go in SUGGESTION.
             - Ask for clarification ONLY if meaning is unclear.

             OUTPUT FORMAT (JSON ONLY):
             {
               \"scenario\": \"Description du contexte et des rôles en français\",
               \"assistant_opening\": \"La première réplique de l'assistant en français\"
             }"
    ],
    [
        "role" => "user",
        "content" =>
            "Crée un court scénario de conversation et la première réplique parlée de l'assistant."
    ]
];

/**
 * Call OpenAI helper
 */
$response = openai_chat($messages);
$raw = $response['content'] ?? '';

/**
 * 🔒 Robust JSON extraction (handles extra text)
 */
preg_match('/\{(?:[^{}]|(?R))*\}/', $raw, $matches);

if (empty($matches)) {
    echo json_encode([
        "error" => "Could not find JSON in OpenAI response",
        "raw" => $raw
    ]);
    exit;
}

$jsonText = $matches[0];
$data = json_decode($jsonText, true);

if (!$data || !isset($data['scenario'], $data['assistant_opening'])) {
    echo json_encode([
        "error" => "Failed to parse OpenAI JSON",
        "raw" => $raw
    ]);
    exit;
}

/**
 * Initialize session conversation
 */
$_SESSION['scenario'] = $data['scenario'];
$_SESSION['conversation'] = [
    [
        "role" => "assistant",
        "content" => $data['assistant_opening']
    ]
];

/**
 * Return to frontend
 */
echo json_encode([
    "scenario" => $data['scenario'],
    "assistant" => $data['assistant_opening']
]);
