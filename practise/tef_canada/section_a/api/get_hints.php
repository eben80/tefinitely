<?php
require_once '../../../../api/session_init.php';
init_session();
require_once __DIR__ . '/../../../../api/openai_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['advertisement'])) {
    echo json_encode(["error" => "No active session"]);
    exit;
}

if (isset($_SESSION['hints'])) {
    echo json_encode(["hints" => $_SESSION['hints']]);
    exit;
}

$advertisement = $_SESSION['advertisement'];
$language = 'fr'; // Force French for TEF Canada

$prompt = "Voici le contenu d'une affiche publicitaire pour une mise en situation TEF Canada Section A :\n\n" . $advertisement . "\n\n" .
          "Génère une liste de 15 questions TRÈS SPÉCIFIQUES basées UNIQUEMENT sur les détails (présents ou manquants) de cette annonce précise. " .
          "Les questions doivent porter sur des informations concrètes que le candidat souhaiterait obtenir en appelant suite à CETTE annonce.\n" .
          "Réponds UNIQUEMENT avec un tableau JSON de chaînes de caractères, sans texte avant ou après, sans backticks.";

$messages = [
    ["role" => "system", "content" => "You are a helpful assistant that generates practice questions for language exams."],
    ["role" => "user", "content" => $prompt]
];

$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// Robust JSON extraction
if (preg_match('/\[.*\]/s', $raw, $matches)) {
    $jsonText = $matches[0];
    $hints = json_decode($jsonText, true);
    if ($hints && is_array($hints)) {
        $_SESSION['hints'] = $hints;
        echo json_encode(["hints" => $hints]);
        exit;
    }
}

echo json_encode(["error" => "Could not generate hints", "raw" => $raw]);
