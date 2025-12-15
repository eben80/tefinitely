<?php
session_start();
require_once __DIR__ . "/openai.php";

header("Content-Type: application/json");

$level = $_POST['level'] ?? "A1";

$_SESSION['state'] = [
    "level" => $level,
    "difficulty" => "normal",
    "success_streak" => 0
];

$prompt = "
Generate a French speaking practice scenario.
Level: {$level}

Respond ONLY in JSON:
{
  \"scenario\": \"title\",
  \"first_prompt\": \"spoken French question\"
}
";

$response = openai_call($prompt);
if (empty($response)) {
    echo json_encode(["error" => "No response from OpenAI"]);
} else {
    echo json_encode($response);
}
