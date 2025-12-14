<?php
session_start();
require "openai.php";

$level = $_POST['level'];

$_SESSION['state'] = [
    "level" => $level,
    "difficulty" => "normal",
    "success_streak" => 0
];

$prompt = "
Generate a spoken French practice scenario.
Level: $level

Return JSON:
{
  \"scenario\": \"title\",
  \"first_prompt\": \"spoken French question\"
}
";

$response = openai_call($prompt);

echo json_encode($response);
