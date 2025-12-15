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

// Add user message
$_SESSION['conversation'][] = [
    "role" => "user",
    "content" => $userText
];

// -------------------- Build system prompt based on language --------------------
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
        // Remove markdown/code block markers if present
        $suggestion = preg_replace('/^```suggestion\s*|\s*```$/', '', $suggestion);
    }
}

// 2️⃣ Fallback: extract suggestion from markdown in raw text
if (!$dialogue && $raw) {
    $dialogue = $raw;
}

if (!$suggestion) {
    if (preg_match('/```suggestion\s*(.*?)```/s', $raw, $match)) {
        $suggestion = trim($match[1]);
    } else {
        // default fallback text
        $suggestion = $language === 'fr' ? "Essayez de reformuler pour plus de clarté." : "Try to rephrase for clarity.";
    }
}

// -------------------- Add assistant reply to session --------------------
$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $dialogue
];

// -------------------- Return JSON --------------------
echo json_encode([
    "assistant" => $dialogue,
    "suggestion" => $suggestion
]);
