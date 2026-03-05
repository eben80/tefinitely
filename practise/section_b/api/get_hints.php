<?php
require_once '../../../api/session_init.php';
init_session();
require_once __DIR__ . '/openai.php';

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
$language = $_SESSION['language'] ?? 'fr';

if ($language === 'fr') {
    $prompt = "Voici le contenu d'une affiche publicitaire pour une mise en situation TEF Canada Section B :\n\n" . $advertisement . "\n\n" .
              "Génère une liste de 10 arguments convaincants ou points forts basés sur cette annonce que le candidat pourrait utiliser pour convaincre son ami d'y participer ou de l'utiliser.\n" .
              "Réponds UNIQUEMENT avec un tableau JSON de chaînes de caractères, sans texte avant ou après, sans backticks.";
} else {
    $prompt = "Here is the content of an advertisement poster for a TEF Canada Section B roleplay scenario:\n\n" . $advertisement . "\n\n" .
              "Generate a list of 10 convincing arguments or highlights based on this advertisement that the candidate could use to persuade their friend to join or use it.\n" .
              "Respond ONLY with a JSON array of strings, without text before or after, without backticks.";
}

$messages = [
    ["role" => "system", "content" => "You are a helpful assistant that generates persuasive arguments for language exams."],
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

echo json_encode(["error" => "Could not generate arguments", "raw" => $raw]);
