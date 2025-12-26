<?php
// practise/api/translate_help.php

// This is a stateless endpoint. It does not use sessions.
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$text = $_POST['text'] ?? '';

if (empty($text)) {
    http_response_code(400);
    echo json_encode(['error' => 'Text parameter is missing']);
    exit;
}

// Construct the prompt for OpenAI
$messages = [
    [
        'role' => 'system',
        'content' => 'You are a helpful translation assistant. Translate the following English text to French. Provide only the French translation, without any additional text, explanations, or quotation marks.'
    ],
    [
        'role' => 'user',
        'content' => $text
    ]
];

// Call the OpenAI chat function
$response = openai_chat($messages);
$translation = $response['content'] ?? '';

if (empty($translation)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get translation from the service.']);
    exit;
}

// Return the translation
echo json_encode(['translation' => $translation]);
