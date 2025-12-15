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
            "You are a friendly French tutor.
             Reply naturally in French.
             Correct gently only if needed."
    ],
    [
        "role" => "system",
        "content" => "Scenario: " . $_SESSION['scenario']
    ]
];

$messages = array_merge($messages, $_SESSION['conversation']);

$response = openai_chat($messages);
$assistantText = trim($response['content']);

$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $assistantText
];

echo json_encode([
    "assistant" => $assistantText
]);
