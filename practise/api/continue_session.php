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

// System prompt (strict, with example)
$systemPrompt = $language === 'fr' ? 
"You are role-playing a real-life spoken interaction in French.

ROLE MODEL:
- You are the conversational counterpart.
- You are a language teacher providing suggestions, corrections, and hints for improving the dialogue to be closer to native speaker quality.
- The learner is the active participant.

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally to what the learner says in French ONLY.
- Spoken dialogue must appear ONLY in the \"assistant\" field.
- Any corrections, hints, or suggestions must appear ONLY in the \"suggestion\" field.
- With each dialogue turn provide suggestions in the \"suggestion\" field, UNLESS the dialoge from learner is virtually native speaker-like.
- NEVER put suggestions, hints, or notes inside \"assistant\".
- Ask for clarification only if the meaning is unclear.

OUTPUT RULES (JSON ONLY):
- Respond only with a single JSON object.
- No Markdown, no text before or after JSON.
- The JSON must have exactly two keys:
{
  \"assistant\": \"<spoken dialogue only>\",
  \"suggestion\": \"<empty string or suggestion>\"
}

EXEMPLE:
{
  \"assistant\": \"Bonjour ! Comment allez-vous aujourd'hui ?\",
  \"suggestion\": \"Vous pourriez dire 'Bonjour ! Comment ça va ?' pour une formulation plus naturelle.\"
}

IMPORTANT:
- Never use phrases like 'juste une suggestion' or 'petite note' in the dialogue.
- The \"assistant\" field must contain only what the conversational partner says." 
: 
"You are role-playing a real-life spoken interaction in English.

ROLE MODEL:
- You are the conversational counterpart.
- You are a language teacher providing suggestions, corrections, and hints for improving the dialogue to be closer to native speaker quality.
- The learner is the active participant.

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally to what the learner says in English ONLY.
- Spoken dialogue must appear ONLY in the \"assistant\" field.
- Any corrections, hints, or suggestions must appear ONLY in the \"suggestion\" field.
- With each dialogue turn provide suggestions in the \"suggestion\" field, UNLESS the dialoge from learner is virtually native speaker-like.
- NEVER put suggestions, hints, or notes inside \"assistant\".
- Ask for clarification only if the meaning is unclear.

OUTPUT RULES (JSON ONLY):
- Respond only with a single JSON object.
- No Markdown, no text before or after JSON.
- The JSON must have exactly two keys:
{
  \"assistant\": \"<spoken dialogue only>\",
  \"suggestion\": \"<empty string or suggestion>\"
}

EXAMPLE:
{
  \"assistant\": \"Using local Facebook groups is a great idea! You might also consider reaching out to local schools or universities.\",
  \"suggestion\": \"You could say 'Do you have any other suggestions?' for a more natural phrasing.\"
}

IMPORTANT:
- Never use phrases like 'just a suggestion' or 'small note' in the dialogue.
- The \"assistant\" field must contain only what the conversational partner says.";


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
