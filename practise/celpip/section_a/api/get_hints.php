<?php
require_once '../../../../api/session_init.php';
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
$language = 'en'; // Force English for CELPIP

$prompt = "Here is the content of an advertisement poster for a CELPIP Section A roleplay scenario:\n\n" . $advertisement . "\n\n" .
          "Generate a list of 15 VERY SPECIFIC questions based ONLY on the details (present or missing) of this particular advertisement. " .
          "The questions should focus on concrete information the candidate would want to obtain when calling about THIS specific ad.\n" .
          "Respond ONLY with a JSON array of strings, without text before or after, without backticks.";

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
