<?php
/**
 * openai.php
 * Helper function to call OpenAI API safely.
 * Logs raw responses to /var/www/tefinitely.com/html/practise/api/logs/openai_called.log
 */

function openai_call(string $prompt): array
{
    // Log file inside project folder (ensure logs/ exists and is writable)
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $log_file = $log_dir . '/openai_called.log';

    // Get API key from environment
    $apiKey = getenv("OPENAI_API_KEY");
    if (!$apiKey) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: OPENAI_API_KEY not set\n", FILE_APPEND);
        return [];
    }

    // Prepare payload for OpenAI
    $payload = [
        "model" => "gpt-4o",   // adjust model as needed
        "messages" => [
            [
                "role" => "system",
                "content" => "You are a French language tutor. Speak only French. Be concise and adaptive."
            ],
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "temperature" => 0.5
    ];

    // Initialize cURL
    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Log raw response (do not log API key)
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - OpenAI raw response:\n$response\n\n", FILE_APPEND);

    if (!$response) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: cURL failed: {$curl_error}\n", FILE_APPEND);
        return [];
    }

    // Decode outer response
    $json = json_decode($response, true);
    $content = $json['choices'][0]['message']['content'] ?? '';

    if (!$content) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: No content returned from OpenAI\n", FILE_APPEND);
        return [];
    }

    // Robust JSON extraction: strip extra text around JSON object
    $content = trim($content);
    $json_start = strpos($content, '{');
    $json_end = strrpos($content, '}');

    if ($json_start !== false && $json_end !== false) {
        $json_text = substr($content, $json_start, $json_end - $json_start + 1);
        $parsed = json_decode($json_text, true);

        if ($parsed === null) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: Failed to parse JSON:\n$json_text\n", FILE_APPEND);
            return [];
        }

        return $parsed;
    } else {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: JSON braces not found in response:\n$content\n", FILE_APPEND);
        return [];
    }
}
