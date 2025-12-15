<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$level = $_POST['level'] ?? 'A2';

// ------------------ Broader scenario categories ------------------
$categories = [
    "café", "boulangerie", "marché", "restaurant", "pharmacie",
    "poste", "banque", "cinéma", "musée", "bibliothèque",
    "gare", "bus", "supermarché", "boutique de vêtements",
    "piscine", "parc", "médecin", "dentiste", "fête ou rencontre"
];

// Pick a random category
$chosen = $categories[array_rand($categories)];

// ------------------ SYSTEM prompt ------------------
$messages = [
    [
        "role" => "system",
        "content" =>
            "You are a native French speaker role-playing a real-life spoken interaction.

             LANGUAGE RULE:
             - All output must be in French.

             ROLE MODEL:
             - You are the conversational counterpart.
             - The learner is the active participant.

             RULES:
             - You must NEVER speak as the learner.
             - Where the scenario involves customer/client, the learner is always the customer/client.
             - You must ALWAYS start the conversation.
             - Keep language appropriate for level {$level}.
             - Spoken dialogue ONLY goes in DIALOGUE.
             - Corrections or suggestions ONLY go in SUGGESTION.
             - Ask for clarification ONLY if meaning is unclear.

             OUTPUT FORMAT (JSON ONLY):
             {
               \"scenario\": \"Description du contexte et des rôles en français\",
               \"assistant_opening\": \"La première réplique de l'assistant en français\"
             }"
    ],
    [
        "role" => "user",
        "content" =>
            "Crée un court scénario de conversation en français dans un lieu comme un $chosen et donne la première réplique parlée de l'assistant."
    ]
];

// ------------------ Call OpenAI ------------------
$response = openai_chat($messages);
$raw = $response['content'] ?? '';

// ------------------ Robust JSON extraction ------------------
preg_match('/\{(?:[^{}]|(?R))*\}/', $raw, $matches);

if (empty($matches)) {
    echo json_encode([
        "error" => "Could not find JSON in OpenAI response",
        "raw" => $raw
    ]);
    exit;
}

$jsonText = $matches[0];
$data = json_decode($jsonText, true);

if (!$data || !isset($data['scenario'], $data['assistant_opening'])) {
    echo json_encode([
        "error" => "Failed to parse OpenAI JSON",
        "raw" => $raw
    ]);
    exit;
}

// ------------------ Initialize session conversation ------------------
$_SESSION['scenario'] = $data['scenario'];
$_SESSION['conversation'] = [
    [
        "role" => "assistant",
        "content" => $data['assistant_opening']
    ]
];

// ------------------ Return to frontend ------------------
echo json_encode([
    "scenario" => $data['scenario'],
    "assistant" => $data['assistant_opening']
]);
