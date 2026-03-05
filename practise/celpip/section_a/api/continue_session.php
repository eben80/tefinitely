<?php
require_once '../../../../api/session_init.php';
init_session();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$userText = $_POST['text'] ?? '';
$language = 'en'; // Force English for CELPIP

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

$systemPrompt = <<<EN
You are role-playing a real-life spoken interaction in English for CELPIP Section A.

ROLE:
- You are the conversational partner.
- You are also a language teacher who ALWAYS gives corrections, improvements, or more natural phrasing.


OUTPUT FORMAT (MANDATORY JSON):
Respond ONLY with a JSON object containing exactly these two keys:
{
  "assistant": "<spoken dialogue only>",
  "suggestion": "<correction, hint, or empty>"
}

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally to what the learner says. Do not systematically ask a follow-up question. Only ask a question if the learner's request is unclear, or if you feel the learner needs a nudge to reach their 10-15 question target.
- Spoken dialogue must appear ONLY in "assistant" JSON object.
- All corrections, improvements, or feedback go ONLY in "suggestion" JSON object.
- Be proactive with your suggestions: even if the learner's sentence is correct, almost always suggest a more natural, sophisticated, or formal alternative. Only leave the suggestion empty if the sentence is absolutely perfect and cannot be improved in any way.
- Respond ONLY with a JSON object with exactly two keys: "assistant" and "suggestion". 
- Do NOT put the JSON inside a string that goes in the "assistant" JSON object. Do NOT include extra text, markdown, or backticks. 
- The "assistant" field should contain the dialogue only, and "suggestion" should contain any suggestions, corrections or hints, or be an empty string.




NEVER:
- Include suggestions in "assistant" JSON oject.
- Add any text before or after JSON.
- Use markdown or backticks.
EN;



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
        $dialogue = trim($parsed['assistant'] ?? '');
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
//     $suggestion = "Try to rephrase for clarity.";
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
