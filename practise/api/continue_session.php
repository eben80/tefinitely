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

// System prompt
$systemPrompt = $language === 'fr' ? "Vous jouez une interaction parlée réelle en français.

ROLE MODEL:
- Vous êtes le partenaire de conversation.
- Vous êtes un professeur de langues qui propose des suggestions, des corrections et des conseils pour améliorer les dialogues.
- L'apprenant est le participant actif.

STRICT RULES:
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement à ce que l'apprenant dit.
- Dialogue parlé UNIQUEMENT en DIALOGUE.
- Signalez toute astuces, erreur ou correction susceptible d'être améliorée.
- Les commentaires, astuces, suggestions ou corrections doivent être envoyés UNIQUEMENT dans la section SUGGESTION.
- Demandez des clarifications seulement si le sens est ambigu.

OUTPUT FORMAT (JSON or plain text):
{
    \"dialogue\": \"<ce que vous dites>\",
    \"suggestion\": \"<optionnel>\"
}" : "You are role-playing a real-life spoken interaction in English.

ROLE MODEL:
- You are the conversational counterpart.
- You are a language teacher with suggestions, corrections and hints for improvement of dialogue. 
- The learner is the active participant.

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally to what the learner says.
- Spoken dialogue ONLY goes in DIALOGUE.
- Give feedback, hints or correction when there is a mistake or room for improvement.
- Feedback, hints, suggestions or corrections ONLY go in SUGGESTION.
- Ask for clarification ONLY if meaning is unclear.

OUTPUT FORMAT (JSON or plain text):
{
    \"dialogue\": \"<what you say>\",
    \"suggestion\": \"<optional>\"
}";

// Build messages
$messages = [
    ["role" => "system", "content" => $systemPrompt],
    ["role" => "system", "content" => "Scenario: " . $_SESSION['scenario']]
];

$messages = array_merge($messages, $_SESSION['conversation']);

// Call OpenAI
$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// Initialize output
$dialogue = '';
$suggestion = '';

// 1️⃣ Try JSON extraction
if (preg_match('/\{(?:[^{}]|(?R))*\}/', $raw, $matches)) {
    $jsonText = $matches[0];
    $parsed = json_decode($jsonText, true);
    if ($parsed) {
        $dialogue = trim($parsed['dialogue'] ?? '');
        $suggestion = trim($parsed['suggestion'] ?? '');
    }
}

// 2️⃣ If dialogue is empty, fallback to raw text
if (!$dialogue) {
    $dialogue = $raw;
}

// 3️⃣ Extract ```suggestion``` block from raw text (if present)
if (preg_match('/```suggestion\s*(.*?)```/s', $dialogue, $match)) {
    $suggestion = trim($match[1]);
    // Remove it from dialogue
    $dialogue = preg_replace('/```suggestion\s*.*?```/s', '', $dialogue);
}

// 4️⃣ Trim dialogue and suggestion
$dialogue = trim($dialogue);
$suggestion = trim($suggestion);

// 5️⃣ Fallback suggestion if none
// if (!$suggestion) {
//     $suggestion = $language === 'fr' ? "Essayez de reformuler pour plus de clarté." : "Try to rephrase for clarity.";
// }

// Save assistant response in session
$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $dialogue
];

// Return JSON
echo json_encode([
    "assistant" => $dialogue,
    "suggestion" => $suggestion
]);
