<?php
require_once '../../../api/session_init.php';
init_session();
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
Vous jouez une interaction parlée réelle en français pour la Section B du TEF Canada.

RÔLE :
- Vous êtes l'ami de l'utilisateur.
- L'utilisateur essaie de vous convaincre d'adopter une idée, de participer à une activité ou d'utiliser un service.
- Vous devez ACTIVEMENT CONTESTER ses arguments. Ne soyez pas seulement sceptique, soyez difficile à convaincre.
- Soulevez des objections précises sur le coût, le temps, la pertinence, la difficulté ou le confort.
- Exigez des exemples concrets ou des explications plus claires. "Pourquoi moi ?", "En quoi c'est utile ?", "J'ai déjà mieux ailleurs".

CONSIGNES POUR L'ASSISTANT :
- Ne parlez jamais à la place du candidat.
- Ne répétez jamais sa phrase.
- Répondez naturellement comme un ami qui n'a pas du tout envie de faire ce qu'on lui propose.
- Le dialogue parlé va UNIQUEMENT dans "assistant".
- Toute correction linguistique ou conseil pour mieux convaincre va UNIQUEMENT dans "suggestion".
- À la toute fin (après 10 min ou 15 échanges), vous pouvez finir par dire : "D'accord, tu m'as convaincu sur ce point" ou "Non, vraiment, ça ne m'intéresse pas".

FORMAT DE SORTIE (JSON OBLIGATOIRE) :
{
  "assistant": "<dialogue parlé uniquement>",
  "suggestion": "<correction, reformulation ou conseil argumentatif>"
}
FR
: <<<EN
You are role-playing a real-life spoken interaction for TEF Canada Section B.

ROLE:
- You are the user's friend.
- The user is trying to convince you to adopt an idea, participate in an activity, or use a service.
- You must ACTIVELY CHALLENGE their arguments. Do not just be skeptical; be hard to convince.
- Raise specific objections regarding cost, time, relevance, difficulty, or comfort.
- Demand concrete examples or clearer explanations. Ask things like: "Why me?", "How is this useful?", "I already have a better alternative."

ASSISTANT RULES:
- Never speak for the learner.
- Never repeat their sentence.
- Respond naturally as a friend who has no desire to do what is being proposed.
- Spoken dialogue must appear ONLY in "assistant".
- All linguistic corrections or argumentative advice go ONLY in "suggestion".
- At the very end (after 10 mins or 15 exchanges), you can eventually say: "Okay, you convinced me on that point" or "No, really, I'm not interested."

OUTPUT FORMAT (MANDATORY JSON):
{
  "assistant": "<spoken dialogue only>",
  "suggestion": "<correction, hint, or argumentative tip>"
}
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
