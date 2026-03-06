<?php
require_once '../session_init.php';
init_session();
require_once __DIR__ . '/../openai_helper.php';
require_once '../../db/db_config.php';
require_once 'audit_logger.php';

header('Content-Type: application/json; charset=utf-8');

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

set_time_limit(300);

$test_type = 'vocabulary';

$systemPrompt = "Expert French examiner. Generate a pool of 60 multiple-choice questions (10 per level A1-C2) for French VOCABULARY.

FORMAT:
1. Cloze tests (French sentence with a blank '_____').
2. Definitions or Synonyms (e.g., 'Quel est le synonyme de...' or 'Comment appelle-t-on...').
3. English-to-French translation (e.g., 'What is the French word for...').

LEVEL GUIDELINES:
- A1/A2: Common objects, actions, and simple descriptive words (e.g., 'ranger', 'cloche', 'balayer').
- B1/B2: Professional, social, and abstract vocabulary (e.g., 'embaucher', 'déception', 'auparavant').
- C1/C2: Nuanced Gallicisms, obscure but relevant roots, and sophisticated idioms (e.g., 'déclic', 'rabrouer', 'mitigé', 'villégiature').

CRITICAL RULES:
- The target French word (the correct answer) MUST NOT appear in the 'question' text.
- For translation questions, the source word must be in English.
- ABSOLUTELY NO English/French cognates (endings like -tion, -ssion, -ité, -té, -able, -ible, -ent, -ant, -al, -el, -isme, -iste, -ure, -ence, -ance are BANNED).
- No Latin-root words used in English (e.g., avoid 'famille', 'possible'). Use Gallic roots instead.
- Use variety in 'options' to ensure they are plausible but distinct.

Each object in the JSON array:
{
  \"question\": \"The sentence with blank, the definition, or the English translation prompt\",
  \"options\": {\"A\": \"...\", \"B\": \"...\", \"C\": \"...\", \"D\": \"...\"},
  \"correct\": \"A/B/C/D\",
  \"level\": \"A1-C2\"
}

Return ONLY the JSON array of 60 objects.";

$messages = [
    ["role" => "system", "content" => $systemPrompt],
    ["role" => "user", "content" => "Generate the 60 vocabulary questions in French now."]
];

$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

if (preg_match('/\[.*\]/s', $raw, $matches)) {
    $jsonText = $matches[0];
    $questions = json_decode($jsonText, true);
    if ($questions && count($questions) >= 40) {

        $conn->begin_transaction();
        try {
            // Clear existing vocabulary questions
            $conn->query("DELETE FROM level_test_questions WHERE test_type = 'vocabulary'");

            $stmt = $conn->prepare("INSERT INTO level_test_questions (question, option_a, option_b, option_c, option_d, correct_option, level, test_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'vocabulary')");

            foreach ($questions as $q) {
                $stmt->bind_param("sssssss",
                    $q['question'],
                    $q['options']['A'],
                    $q['options']['B'],
                    $q['options']['C'],
                    $q['options']['D'],
                    $q['correct'],
                    $q['level']
                );
                $stmt->execute();
            }
            $stmt->close();

            logAdminAction($conn, $_SESSION['user_id'], 'update_level_test_questions', null, "Refreshed vocabulary pool with " . count($questions) . " questions");

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Vocabulary question pool updated successfully with ' . count($questions) . ' questions.']);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
}

http_response_code(500);
echo json_encode(['status' => 'error', 'message' => 'Failed to generate questions or invalid format received from AI.', 'debug' => $raw]);
