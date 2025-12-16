<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json; charset=utf-8');

$userText = trim($_POST['text'] ?? '');
$language = $_POST['language'] ?? 'fr'; // 'fr' or 'en'

// Safety check
if ($userText === '' || !isset($_SESSION['conversation'], $_SESSION['scenario'])) {
    echo json_encode([
        "assistant" => "",
        "suggestion" => ""
    ]);
    exit;
}

// Store user message
$_SESSION['conversation'][] = [
    "role" => "user",
    "content" => $userText
];

// -------------------- System prompt --------------------
if ($language === 'fr') {
    $systemPrompt = <<<PROMPT
Vous jouez une interaction parlée réelle en français.

ROLE MODEL:
- Vous êtes le partenaire de conversation.
- Vous êtes un professeur de langues qui propose des suggestions, des corrections et des conseils pour améliorer les dialogues.
- L'apprenant est le participant actif.

STRICT RULES:
- Ne parlez jamais à la place de l'apprenant.
- Ne répétez jamais la phrase de l'apprenant.
- Répondez naturellement à ce que l'apprenant dit.
- Dialogue parlé UNIQUEMENT dans "assistant".
- Signalez toute astuce, erreur ou correction susceptible d'être améliorée.
- Les commentaires, astuces, suggestions ou corrections doivent être envoyés UNIQUEMENT dans "suggestion".
- Demandez des clarifications seulement si le sens est ambigu.

OUTPUT RULES (MANDATORY):
- Répondre UNIQUEMENT avec un seul objet JSON
- Pas de Markdown
- Pas d'accents graves (backticks)
- Pas d'explications
- Pas de texte avant ou après le JSON
- Ne pas intégrer de JSON dans des chaînes de caractères

OUTPUT FORMAT EXACTLY:
{
  "assistant": "<dialogue parlé uniquement>",
  "suggestion": "<chaîne vide ou correction>"
}
PROMPT;
} else {
    $systemPrompt = <<<PROMPT
You are role-playing a real-life spoken interaction in English.

ROLE MODEL:
- You are the conversational counterpart.
- You are a language teacher with suggestions, corrections and hints for improvement of dialogue.
- The learner is the active participant.

STRICT RULES:
- Never speak as the learner.
- Never repeat the learner's sentence.
- Reply naturally to what the learner says.
- Spoken dialogue ONLY goes in "assistant".
- Feedback, hints, suggestions or corrections ONLY go in "suggestion".
- Ask for clarification ONLY if meaning is unclear.

OUTPUT RULES (MANDATORY):
- Respond ONLY with a single JSON object
- No markdown
- No backticks
- No explanations
- No text before or after JSON
- Do not embed JSON inside strings

OUTPUT FORMAT EXACTLY:
{
  "assistant": "<spoken dialogue only>",
  "suggestion": "<empty string or correction>"
}
PROMPT;
}

// -------------------- Build messages --------------------
$messages = [
    ["role" => "system", "content" => $systemPrompt],
    ["role" => "system", "content" => "Scenario: " . $_SESSION['scenario']]
];

$messages = array_merge($messages, $_SESSION['conversation']);

// -------------------- Call OpenAI --------------------
$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// -------------------- Strict JSON decode --------------------
$data = json_decode($raw, true);

// If the model violated the contract, fail safely
if (!is_array($data)) {
    echo json_encode([
        "assistant" => "",
        "suggestion" => ""
    ]);
    exit;
}

// Extract fields safely
$assistant = trim($data['assistant'] ?? '');
$suggestion = trim($data['suggestion'] ?? '');

// Store assistant dialogue only (never store suggestion)
$_SESSION['conversation'][] = [
    "role" => "assistant",
    "content" => $assistant
];

// -------------------- Return to frontend --------------------
echo json_encode([
    "assistant" => $assistant,
    "suggestion" => $suggestion
], JSON_UNESCAPED_UNICODE);
