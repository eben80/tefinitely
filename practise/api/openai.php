<?php
/**
 * openai.php
 * Provides a helper function to call OpenAI API safely.
 * Logs raw responses to /tmp/openai_called.log for debugging.
 */

function openai_call(string $prompt): array
{
    // Get API key from environment
    $apiKey = getenv("OPENAI_API_KEY");
    if (!$apiKey) {
        file_put_contents('/tmp/openai_called.log', date('Y-m-d H:i:s') . " - ERROR: OPENAI_API_KEY not set\n", FILE_APPEND);
        return [];
    }

    // Prepare payload
    $payload = [
        "model" => "gpt-4o",   // change model as needed
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

    // Log raw response for debugging (no API key)
    file_put_contents('/tmp/openai_called.log', date('Y-m-d H:i:s') . " - OpenAI raw response:\n$response\n\n", FILE_APPEND);

    if (!$response) {
        file_put_contents('/tmp/openai_called.log', date('Y-m-d H:i:s') . " - ERROR: cURL failed: {$curl_error}\n", FILE_APPEND);
        return [];
    }

    // Decode outer response
    $json = json_decode($response, true);
    $content = $json['choices'][0]['message']['content'] ?? '';

    if (!$content) {
        file_put_contents('/tmp/openai_called.log', date('Y-m-d H:i:s') . " - ERROR: No content returned from OpenAI\n", FILE_APPEND);
        return [];
    }

    // Robust JSON extraction: strip any extra text around the JSON object
    $content = trim($content);
    $json_start = strpos($content, '{');
    $json_end = strrpos($content, '}');

    if ($json_start !== false && $json_end !== false) {
        $json_text = substr($content, $json_start, $json_end - $json_start + 1);
        $parsed = json_decode($json_text, true);

        if ($parsed === null) {
            file_put_contents('/tmp/openai_called.log', date('Y-m-d H:i:s') . " - ERROR: Failed to parse JSON:\n$json_text\n", FILE_APPEND);
            return [];
        }

        return $parsed;
    } else {
        file_put_contents('/tmp/openai_called.log', date('Y-m-d H:i:s') . " - ERROR: JSON braces not found in response:\n$content\n", FILE_APPEND);
        return [];
    }
}
