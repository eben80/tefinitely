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
            "You are role-playing a real-life spoken interaction in French.
             
             ROLE MODEL:
             - You are the conversational counterpart.
             - The learner is the active participant.
             
             STRICT RULES:
             - Never speak as the learner.
             - Never repeat the learner's sentence.
             - Reply naturally to what the learner says.
             - Spoken dialogue ONLY goes in DIALOGUE.
             - Feedback or corrections ONLY go in SUGGESTION.
             - Ask for clarification ONLY if meaning is unclear.
             
             FORMAT:
             DIALOGUE: <what you say>
             SUGGESTION: <optional>"
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

// Add user message
$_SESSION['conversation'][] = [
    "role" => "user",
    "content" => $userText
];

// Call OpenAI...

$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $dialogue
];
