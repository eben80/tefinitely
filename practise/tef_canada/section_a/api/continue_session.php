<?php
require_once '../../../../api/session_init.php';
init_session();
require_once __DIR__ . '/../../../../api/openai_helper.php';

header('Content-Type: application/json');

$userText = $_POST['text'] ?? '';
$language = 'fr'; // Force French for TEF Canada

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

$systemPrompt = <<<FR
Vous jouez une interaction parlée réelle en français.

RÔLE :
- Vous êtes le partenaire de conversation.
- Vous êtes un professeur de langue qui donne toujours des corrections, améliorations ou reformulations naturelles.

RÈGLES STRICTES :
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement au dialogue. L'utilisateur doit mener la conversation en posant des questions sur l'annonce. NE POSEZ PAS de questions de suivi. Contentez-vous de répondre aux questions de l'utilisateur pour le laisser prendre l'initiative. Ne posez une question que si l'utilisateur semble bloqué ou si sa demande est totalement incompréhensible.
- Si l'utilisateur vous demande quelles questions il peut poser, NE RÉPONDEZ PAS en donnant des exemples. Dites-lui plutôt de regarder l'annonce pour trouver des idées ou d'utiliser le bouton d'aide (l'icône d'ampoule) s'il est vraiment bloqué.
- Le dialogue parlé va UNIQUEMENT dans "assistant".
- Toute correction, reformulation, amélioration ou remarque va UNIQUEMENT dans "suggestion".
- Soyez proactif dans vos suggestions : même si la phrase de l'apprenant est correcte, proposez presque toujours une alternative plus naturelle, plus élégante ou plus formelle. Ne laissez la suggestion vide que si la phrase est absolument parfaite et ne peut pas être améliorée.

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
FR;



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
//     $suggestion = "Essayez de reformuler pour plus de clarté.";
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
