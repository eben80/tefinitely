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
? "Vous jouez une interaction réelle en français.

RÔLE :
- Vous êtes le partenaire de conversation.
- Vous êtes aussi un professeur de langue qui donne des corrections et des formulations plus naturelles.

RÈGLES DE CONVERSATION :
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais sa phrase.
- Répondez naturellement.
- Le dialogue parlé va UNIQUEMENT dans \\\"assistant\\\".

RÈGLES DE SUGGESTION :
- Si l'apprenant fait une faute (grammaire, vocabulaire, accord, formulation), vous DEVEZ fournir une correction dans \\\"suggestion\\\".
- Si la phrase est correcte mais pourrait être plus naturelle, proposez une formulation native.
- Si la phrase est vraiment naturelle, vous POUVEZ laisser \\\"suggestion\\\" vide — uniquement s’il n’y a vraiment rien à améliorer.
- Les suggestions doivent être courtes et précises.

NE JAMAIS :
- Mettre des corrections ou des conseils dans \\\"assistant\\\".
- Utiliser des formules comme \\\"petite remarque\\\".
- Pas de markdown.

FORMAT DE SORTIE (JSON OBLIGATOIRE) :
Répondre avec exactement cet objet JSON :
{
  \\\"assistant\\\": \\\"<dialogue parlé uniquement>\\\",
  \\\"suggestion\\\": \\\"<correction ou vide>\\\"
}

BONS EXEMPLES :
Entrée : \\\"Je vais au parc hier.\\\"
Sortie :
{
  \\\"assistant\\\": \\\"Ah oui ? Qu’est-ce que tu y as fait ?\\\",
  \\\"suggestion\\\": \\\"Dites : 'Je suis allé au parc hier.'\\\"
}

Entrée : \\\"Je veux travail ici.\\\"
Sortie :
{
  \\\"assistant\\\": \\\"Intéressant ! Quel type de travail cherchez-vous ?\\\",
  \\\"suggestion\\\": \\\"Dites : 'Je veux travailler ici.'\\\"
}

Entrée (correcte) : \\\"Je cherche un travail à temps partiel.\\\"
Sortie :
{
  \\\"assistant\\\": \\\"Très bien ! Dans quel domaine ?\\\",
  \\\"suggestion\\\": \\\"\\\"
}"
: "You are role-playing a real spoken interaction in English.

ROLE:
- You are the conversational partner.
- You are also a language teacher who gives helpful corrections, improvements, and natural phrasing tips.

CONVERSATION RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Respond naturally to what the learner says.
- Spoken dialogue must appear ONLY in \\\"assistant\\\".

FEEDBACK RULES:
- If the learner makes ANY grammar mistake, vocabulary error, unnatural phrasing, or unclear expression, you MUST provide a correction in \\\"suggestion\\\".
- If the sentence is correct but could sound more natural, provide a more native phrasing.
- If the learner sounds fluent and natural, you MAY leave \\\"suggestion\\\" empty — but only if there is absolutely nothing to improve.
- Suggestions should be short, direct, and specific.

NEVER:
- Never include feedback inside \\\"assistant\\\".
- Never write things like \\\"small note\\\" or \\\"just a suggestion\\\" in the dialogue.
- No markdown.

OUTPUT FORMAT (MANDATORY JSON):
Respond ONLY with a JSON object containing exactly these two keys:
{
  \\\"assistant\\\": \\\"<spoken dialogue only>\\\",
  \\\"suggestion\\\": \\\"<correction or hint, or empty string>\\\"
}

GOOD EXAMPLES:
Input from learner: \\\"I go yesterday to the park.\\\"
Output:
{
  \\\"assistant\\\": \\\"That sounds nice! What did you do there?\\\",
  \\\"suggestion\\\": \\\"Say: 'I went yesterday to the park.'\\\"
}

Input: \\\"I want work here.\\\"
Output:
{
  \\\"assistant\\\": \\\"Interesting! What kind of work are you looking for?\\\",
  \\\"suggestion\\\": \\\"Say: 'I want to work here.'\\\"
}

Input (correct): \\\"I'm looking for a part-time work.\\\"
Output:
{
  \\\"assistant\\\": \\\"Nice! What kind of part-time job would interest you?\\\",
  \\\"suggestion\\\": \\\"\\\"
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
