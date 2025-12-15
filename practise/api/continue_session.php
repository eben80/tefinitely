<?php
session_start();
require_once __DIR__ . '/openai.php';

$userText = $_POST['text'] ?? '';

if (!$userText || !isset($_SESSION['conversation'])) {
    echo json_encode([]);
    exit;
}

// Add user message
$_SESSION['conversation'][] = [
    "role" => "user",
    "content" => $userText
];

// Build messages with context
$messages = [
    [
        "role" => "system",
        "content" =>
            "You are role-playing a real-life dialogue in French.
 Speak naturally and stay fully in character.
 
 RULES:
 - Spoken dialogue must ONLY contain natural conversational replies.
 - Do NOT include corrections or explanations in spoken dialogue.
 - If the learner makes a mistake, continue the dialogue naturally.
 - Provide corrections and suggestions ONLY in a separate section called SUGGESTION.
 - ONLY ask for clarification in spoken dialogue if you genuinely do not understand.
 
 FORMAT YOUR RESPONSE EXACTLY AS:
 DIALOGUE: <what you say in the conversation>
 SUGGESTION: <optional correction or improvement, or empty>"

    ],
    [
        "role" => "system",
        "content" => "Scenario: " . $_SESSION['scenario']
    ]
];

$messages = array_merge($messages, $_SESSION['conversation']);

$response = openai_chat($messages);
$raw = trim($response['content']);

$dialogue = '';
$suggestion = '';

if (preg_match('/DIALOGUE:(.*?)(SUGGESTION:|$)/s', $raw, $m)) {
    $dialogue = trim($m[1]);
}
if (preg_match('/SUGGESTION:(.*)$/s', $raw, $m)) {
    $suggestion = trim($m[1]);
}

$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $dialogue
];

echo json_encode([
    "assistant" => $dialogue,
    "suggestion" => $suggestion
]);
