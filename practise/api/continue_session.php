<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$userText = $_POST['text'] ?? '';

if (!$userText || !isset($_SESSION['conversation'])) {
    echo json_encode([
        "assistant" => "",
        "suggestion" => ""
    ]);
    exit;
}

// Add user message once
$_SESSION['conversation'][] = [
    "role" => "user",
    "content" => $userText
];

// Build messages with context and role enforcement
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

             OUTPUT FORMAT (JSON ONLY):
             {
                 \"dialogue\": \"<what you say>\",
                 \"suggestion\": \"<optional>\"
             }"
    ],
    [
        "role" => "system",
        "content" => "Scenario: " . $_SESSION['scenario']
    ]
];

$messages = array_merge($messages, $_SESSION['conversation']);

// Call OpenAI
$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// Robust JSON extraction
preg_match('/\{(?:[^{}]|(?R))*\}/', $raw, $matches);
if (!empty($matches)) {
    $jsonText = $matches[0];
    $parsed = json_decode($jsonText, true);
    $dialogue = $parsed['dialogue'] ?? '';
    $suggestion = $parsed['suggestion'] ?? '';
} else {
    $dialogue = '';
    $suggestion = '';
}

// Add assistant reply to conversation
$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $dialogue
];

// Return JSON to frontend
echo json_encode([
    "assistant" => $dialogue,
    "suggestion" => $suggestion
]);
