<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$level = $_POST['level'] ?? 'A2';
$language = $_POST['language'] ?? 'fr'; // 'fr' or 'en'

// ------------------ Broader scenario categories ------------------
$categories = [
    "café", "boulangerie", "marché", "restaurant", "pharmacie",
    "poste", "banque", "cinéma", "musée", "bibliothèque",
    "gare", "bus", "supermarché", "boutique de vêtements",
    "piscine", "parc", "médecin", "dentiste", "fête ou rencontre"
];

$categories_en = [
    "coffee shop", "bakery", "market", "restaurant", "pharmacy",
    "post office", "bank", "cinema", "museum", "library",
    "train station", "bus stop", "supermarket", "clothing store",
    "swimming pool", "park", "doctor", "dentist", "party or meeting"
];

// Pick a random category
if ($language === 'fr') {
    $chosen = $categories[array_rand($categories)];
    $scenario_instructions = "Crée un court scénario de conversation en français dans un lieu comme un $chosen et donne la première réplique parlée de l'assistant.";
    $system_prompt = "Vous êtes un locuteur natif français jouant une interaction parlée réelle.\n\nLANGUAGE RULE:\n- Tout doit être en français.\n\nROLE MODEL:\n- Vous êtes le partenaire de conversation.\n- L'apprenant est le participant actif.\n\nRULES:\n- Ne parlez jamais à la place de l'apprenant.\n- Lorsque le scénario implique client/fournisseur, l'apprenant est toujours le client.\n- Commencez toujours la conversation.\n- Niveau adapté: {$level}.\n- Dialogue parlé uniquement en DIALOGUE.\n- Corrections ou suggestions uniquement en SUGGESTION.\n- Demandez des clarifications seulement si le sens est ambigu.\n\nOUTPUT FORMAT (JSON ONLY):\n{\n  \"scenario\": \"Description du contexte et des rôles en français\",\n  \"assistant_opening\": \"La première réplique de l'assistant en français\"\n}";
} else {
    $chosen = $categories_en[array_rand($categories_en)];
    $scenario_instructions = "Create a short conversation scenario in English in a place like a $chosen and provide the assistant's first spoken line.";
    $system_prompt = "You are a native English speaker role-playing a real-life spoken interaction.\n\nLANGUAGE RULE:\n- All output must be in English.\n\nROLE MODEL:\n- You are the conversational counterpart.\n- The learner is the active participant.\n\nRULES:\n- Never speak as the learner.\n- Where the scenario involves customer/client, the learner is always the customer/client.\n- Always start the conversation.\n- Keep language appropriate for level {$level}.\n- Spoken dialogue ONLY goes in DIALOGUE.\n- Corrections or suggestions ONLY go in SUGGESTION.\n- Ask for clarification ONLY if meaning is unclear.\n\nOUTPUT FORMAT (JSON ONLY):\n{\n  \"scenario\": \"Description of the context and roles in English\",\n  \"assistant_opening\": \"The assistant's first spoken line in English\"\n}";
}

// ------------------ SYSTEM prompt ------------------
$messages = [
    [
        "role" => "system",
        "content" => $system_prompt
    ],
    [
        "role" => "user",
        "content" => $scenario_instructions
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
