<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$userText = $_POST['text'] ?? '';
$language = $_POST['language'] ?? 'fr'; // 'fr' or 'en'

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

// -------------------- Build messages with language context --------------------
if ($language === 'fr') {
    $systemPrompt = "Vous jouez une interaction parlée réelle en français.

ROLE MODEL:
- Vous êtes le partenaire de conversation.
- L'apprenant est le participant actif.

STRICT RULES:
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement à ce que l'apprenant dit.
- Dialogue parlé UNIQUEMENT en DIALOGUE.
- Feedback ou corrections UNIQUEMENT en SUGGESTION.
- Demandez des clarifications seulement si le sens est ambigu.

OUTPUT FORMAT (JSON or plain text):
{
    \"dialogue\": \"<ce que vous dites>\",
    \"suggestion\": \"<optionnel>\"
}";
} else {
    $systemPrompt = "You are role-playing a real-life spoken interaction in English.

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

OUTPUT FORMAT (JSON or plain text):
{
    \"dialogue\": \"<what you say>\",
    \"suggestion\": \"<optional>\"
}";
}

// -------------------- Build messages --------------------
$messages = [
    [
        "role" => "system",
        "content" => $systemPrompt
    ],
    [
        "role" => "system",
        "content" => "Scenario: " . $_SESSION['scenario']
    ]
];

$messages = array_merge($messages, $_SESSION['conversation']);

// -------------------- Call OpenAI --------------------
$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// -------------------- Robust extraction --------------------
$dialogue = '';
$suggestion = '';

// 1️⃣ Try to extract JSON first
if (preg_match('/\{(?:[^{}]|(?R))*\}/', $raw, $matches)) {
    $jsonText = $matches[0];
    $parsed = json_decode($jsonText, true);
    if ($parsed) {
        $dialogue = trim($parsed['dialogue'] ?? '');
        $suggestion = trim($parsed['suggestion'] ?? '');
    }
}

// 2️⃣ Fallback: if JSON failed or dialogue empty, use full raw text as dialogue
if (!$dialogue && $raw) {
    $dialogue = $raw;
}

// Add assistant reply to conversation
$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $dialogue
];

// -------------------- Return JSON to frontend --------------------
echo json_encode([
    "assistant" => $dialogue,
    "suggestion" => $suggestion
]);
