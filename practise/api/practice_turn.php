<?php
session_start();
require "openai.php";

if (isset($_POST['manual_difficulty'])) {
    $_SESSION['state']['difficulty'] = $_POST['manual_difficulty'];
    exit;
}

$state = $_SESSION['state'];
$student = $_POST['transcript'];

$prompt = "
You are a French tutor.

Level: {$state['level']}
Difficulty: {$state['difficulty']}
Success streak: {$state['success_streak']}

Student said:
\"$student\"

Respond ONLY in JSON:
{
  \"feedback\": \"short correction\",
  \"next_prompt\": \"next spoken question\",
  \"success\": true/false,
  \"suggested_difficulty\": \"increase|maintain|decrease\"
}
";

$response = openai_call($prompt);

if ($response['success']) {
    $_SESSION['state']['success_streak']++;
} else {
    $_SESSION['state']['success_streak'] = 0;
}

if ($response['suggested_difficulty'] === "increase") {
    $_SESSION['state']['difficulty'] = "harder";
}
if ($response['suggested_difficulty'] === "decrease") {
    $_SESSION['state']['difficulty'] = "easier";
}

echo json_encode($response);
