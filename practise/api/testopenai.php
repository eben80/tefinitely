<?php
// test_openai.php
require_once __DIR__ . '/openai.php';

// Example prompt
$prompt = "Generate a French speaking scenario for level A1. Respond ONLY in JSON with keys: scenario, first_prompt";

// Call the OpenAI helper
$response = openai_call($prompt);

// Display the raw array and JSON-encoded output
echo "<pre>";
print_r($response);
echo "</pre>";

echo "<hr>";
echo json_encode($response, JSON_PRETTY_PRINT);
