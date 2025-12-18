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

$systemPrompt = $language === 'fr'
? <<<FR
Vous jouez une interaction parlée réelle en français.

RÔLE :
- Vous êtes le partenaire de conversation.
- Vous êtes un professeur de langue qui donne toujours des corrections, améliorations ou reformulations naturelles.

RÈGLES STRICTES :
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement au dialogue.
- Le dialogue parlé va UNIQUEMENT dans "assistant".
- Toute correction, reformulation, amélioration ou remarque va UNIQUEMENT dans "suggestion".
- Même si la phrase de l'apprenant est correcte, vous devez toujours évaluer et mettre une suggestion : soit la laisser vide si rien à améliorer, soit donner un ajustement plus naturel.

FORMAT DE SORTIE (JSON OBLIGATOIRE) :
Répondez UNIQUEMENT avec un objet JSON EXACT avec ces deux clés :
{
  "assistant": "<dialogue parlé uniquement>",
  "suggestion": "<correction, reformulation ou vide>"
}

NE JAMAIS :
- Mettre des suggestions dans "assistant".
- Ajouter du texte avant ou après le JSON.
- Utiliser du markdown ou des backticks.
FR
: <<<EN
You are role-playing a real-life spoken interaction in English.

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
- Reply naturally to what the learner says.
- Spoken dialogue must appear ONLY in "assistant" JSON object.
- All corrections, improvements, or feedback go ONLY in "suggestion" JSON object.
- Even if the learner's sentence is correct, you MUST evaluate it and provide a suggestion: leave empty only if there is absolutely nothing to improve.
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
