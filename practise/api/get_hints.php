<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

if (!isset($_SESSION['scenario'])) {
    echo json_encode(["error" => "No active session"]);
    exit;
}

$scenario = $_SESSION['scenario'];
$language = $_SESSION['language'] ?? 'fr';

if ($language === 'fr') {
    $prompt = "Voici une annonce pour une mise en situation TEF Canada Section A :\n\n" . $scenario . "\n\n" .
              "Génère une liste de 15 questions pertinentes et variées que le candidat pourrait poser pour obtenir plus d'informations sur cette annonce.\n" .
              "Réponds UNIQUEMENT avec un tableau JSON de chaînes de caractères, sans texte avant ou après, sans backticks.";
} else {
    $prompt = "Here is an advertisement for a TEF Canada Section A roleplay scenario:\n\n" . $scenario . "\n\n" .
              "Generate a list of 15 relevant and varied questions that the candidate could ask to get more information about this advertisement.\n" .
              "Respond ONLY with a JSON array of strings, without text before or after, without backticks.";
}

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
        echo json_encode(["hints" => $hints]);
        exit;
    }
}

echo json_encode(["error" => "Could not generate hints", "raw" => $raw]);
