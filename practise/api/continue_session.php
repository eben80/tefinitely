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
            "You are a native French speaker role-playing a real-life dialogue.
             
             YOUR ROLE:
             - You are ALWAYS the shopkeeper / waiter / vendor.
             - The user is ALWAYS the customer.
             
             RULES:
             - Never speak as the customer.
             - Never repeat the user's sentence.
             - Reply only as the shopkeeper.
             - Spoken dialogue must be natural and in character.
             - Corrections must NEVER be spoken.
             - Corrections go ONLY in SUGGESTION.
             - Ask for clarification ONLY if the request is unclear.
             
             FORMAT:
             DIALOGUE: <what the shopkeeper says>
             SUGGESTION: <optional correction or empty>"
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
