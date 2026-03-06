<?php
require_once '../../api/session_init.php';
init_session();
require_once __DIR__ . '/../../practise/tef_canada/section_a/api/openai.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. You need an active subscription to take this test.']);
    exit;
}

$type = $_GET['type'] ?? 'vocabulary'; // 'vocabulary' or 'oral'

if ($type === 'vocabulary') {
    $systemPrompt = "You are an expert French language examiner. Your task is to generate 20 multiple-choice questions to determine a user's CEFR level (A1 to C2) in French VOCABULARY.

    The questions should be distributed as follows:
    - 4 questions for A1 (very basic)
    - 4 questions for A2 (elementary)
    - 4 questions for B1 (intermediate)
    - 3 questions for B2 (upper intermediate)
    - 3 questions for C1 (advanced)
    - 2 questions for C2 (mastery)

    Each question must have:
    - A clear question or a sentence with a blank.
    - 4 options (A, B, C, D).
    - Exactly one correct answer.
    - A difficulty level (A1, A2, B1, B2, C1, C2).

    Respond ONLY with a JSON array of 20 objects like this:
    [
      {
        \"id\": 1,
        \"question\": \"Question text here...\",
        \"options\": {
          \"A\": \"Option A\",
          \"B\": \"Option B\",
          \"C\": \"Option C\",
          \"D\": \"Option D\"
        },
        \"correct\": \"A\",
        \"level\": \"A1\"
      },
      ...
    ]";
} else {
    $systemPrompt = "You are an expert French language examiner. Your task is to generate 20 multiple-choice questions to determine a user's CEFR level (A1 to C2) in French ORAL EXPRESSION situations and pragmatics.

    The questions should focus on:
    - Choosing the appropriate response in a spoken dialogue.
    - Identifying the correct register (formal vs. informal).
    - Understanding spoken idioms and common conversational fillers.
    - Pragmatics: how to ask for things, apologize, or disagree naturally in French.

    The questions should be distributed as follows:
    - 4 questions for A1
    - 4 questions for A2
    - 4 questions for B1
    - 3 questions for B2
    - 3 questions for C1
    - 2 questions for C2

    Each question must have:
    - A description of a situation or a dialogue snippet.
    - 4 options (A, B, C, D) for what would be most appropriate to say.
    - Exactly one correct answer.
    - A difficulty level (A1, A2, B1, B2, C1, C2).

    Respond ONLY with a JSON array of 20 objects like this:
    [
      {
        \"id\": 1,
        \"question\": \"Situation: ... What do you say?\",
        \"options\": {
          \"A\": \"Option A\",
          \"B\": \"Option B\",
          \"C\": \"Option C\",
          \"D\": \"Option D\"
        },
        \"correct\": \"A\",
        \"level\": \"A1\"
      },
      ...
    ]";
}

$messages = [
    ["role" => "system", "content" => $systemPrompt],
    ["role" => "user", "content" => "Generate the 20 questions in French now."]
];

$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// Try to extract JSON from the response if it's wrapped in markers
if (preg_match('/\[.*\]/s', $raw, $matches)) {
    $jsonText = $matches[0];
    $questions = json_decode($jsonText, true);
    if ($questions && count($questions) === 20) {
        echo json_encode(['status' => 'success', 'questions' => $questions]);
        exit;
    }
}

// Fallback if JSON parsing fails or not 20 questions
http_response_code(500);
echo json_encode(['status' => 'error', 'message' => 'Failed to generate questions. Please try again.', 'debug' => $raw]);
