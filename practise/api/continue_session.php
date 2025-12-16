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
$systemPrompt = $language === 'fr' ? 
"Vous jouez une interaction parlée réelle en français.

ROLE MODEL:
- Vous êtes le partenaire de conversation.
- Vous êtes un professeur de langues qui propose des suggestions, corrections et conseils pour améliorer le dialogue.
- L'apprenant est le participant actif.

STRICT RULES:
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement à ce que l'apprenant dit.
- Dialogue parlé UNIQUEMENT dans le champ \"assistant\".
- Toute correction, astuce ou suggestion doit aller UNIQUEMENT dans le champ \"suggestion\".
- Ne jamais inclure de suggestion dans \"assistant\".
- Demandez des clarifications seulement si le sens est ambigu.

OUTPUT RULES (MANDATORY JSON ONLY):
- Répondez uniquement avec un objet JSON.
- Pas de Markdown, pas de texte avant ou après le JSON.
- L'objet JSON doit avoir exactement deux clés:

{
  \"assistant\": \"<dialogue parlé uniquement>\",
  \"suggestion\": \"<chaîne vide ou suggestion>\"
}

Exemple:

{
  \"assistant\": \"Utiliser des groupes Facebook locaux est une excellente idée! Vous pouvez aussi contacter des écoles ou universités locales.\",
  \"suggestion\": \"Vous pourriez dire 'Avez-vous d'autres suggestions?' pour une formulation plus naturelle.\"
}
" : "You are role-playing a real-life spoken interaction in English.

ROLE MODEL:
- You are the conversational counterpart.
- You are a language teacher with suggestions, corrections and hints for dialogue improvement.
- The learner is the active participant.

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally to what the learner says.
- Spoken dialogue ONLY goes in the \"assistant\" field.
- Any corrections, hints, or suggestions must go ONLY in the \"suggestion\" field.
- Never include suggestions inside \"assistant\".
- Ask for clarification ONLY if meaning is unclear.

OUTPUT RULES (MANDATORY JSON ONLY):
- Respond only with a single JSON object.
- No Markdown, no text before or after JSON.
- The JSON must have exactly two keys:

{
  \"assistant\": \"<spoken dialogue only>\",
  \"suggestion\": \"<empty string or a suggestion>\"
}

Example:

{
  \"assistant\": \"Using local Facebook groups is a great idea! You might also consider reaching out to local schools or universities.\",
  \"suggestion\": \"You could say 'Do you have any other suggestions?' for a more natural phrasing.\"
}
";


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
