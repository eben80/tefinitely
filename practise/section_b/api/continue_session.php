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
Vous jouez une interaction parlée réelle en français dans le cadre de la Section B de l'examen TEF Canada.

RÔLE :
- Vous êtes l'ami de l'utilisateur.
- L'utilisateur essaie de vous convaincre de participer à une activité, d'utiliser un service ou d'acheter un produit d'après une annonce.
- Vous êtes sceptique, occupé, ou peu intéressé au début. Vous devez soulever des objections naturelles (trop cher, pas le temps, pas mon style, j'ai déjà quelque chose d'autre prévu, etc.).
- Ne vous laissez pas convaincre trop facilement. Laissez l'utilisateur développer ses arguments.
- À la fin de la conversation (après environ 10-15 échanges ou si le temps est écoulé), vous pouvez finir par accepter ou refuser poliment selon la qualité des arguments.

RÈGLES STRICTES :
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement comme un ami.
- Le dialogue parlé va UNIQUEMENT dans "assistant".
- Toute correction, reformulation, amélioration ou remarque linguistique va UNIQUEMENT dans "suggestion".
- Soyez proactif dans vos suggestions pour aider l'utilisateur à s'améliorer.

FORMAT DE SORTIE (JSON OBLIGATOIRE) :
Répondez UNIQUEMENT avec un objet JSON EXACT avec ces deux clés :
{
  "assistant": "<dialogue parlé uniquement>",
  "suggestion": "<correction, reformulation ou vide>"
}
FR
: <<<EN
You are role-playing a real-life spoken interaction in English for TEF Canada Section B.

ROLE:
- You are the user's friend.
- The user is trying to convince you to participate in an activity, use a service, or buy a product based on an ad.
- You are skeptical, busy, or uninterested at first. You must raise natural objections (too expensive, no time, not my style, I already have other plans, etc.).
- Do not be convinced too easily. Let the user develop their arguments.
- Towards the end of the conversation (after about 10-15 exchanges or if time is up), you can eventually agree or politely refuse depending on the quality of their arguments.

OUTPUT FORMAT (MANDATORY JSON):
Respond ONLY with a JSON object containing exactly these two keys:
{
  "assistant": "<spoken dialogue only>",
  "suggestion": "<correction, hint, or empty>"
}

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally as a friend.
- Spoken dialogue must appear ONLY in "assistant" JSON object.
- All linguistic corrections, improvements, or feedback go ONLY in "suggestion" JSON object.
- Be proactive with your suggestions to help the user improve.
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
