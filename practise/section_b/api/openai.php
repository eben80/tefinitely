<?php
/**
 * openai.php
 * OpenAI chat helper with conversation support
 */

function openai_chat(array $messages): array
{
    $log_file = __DIR__ . '/logs/openai_called.log';

    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) {
        error_log("OPENAI_API_KEY not set");
        return ['content' => ''];
    }

    $payload = [
        "model" => "gpt-4o",
        "messages" => $messages,
        "temperature" => 0.6
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$apiKey}",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    file_put_contents($log_file, date('Y-m-d H:i:s') . "\n$response\n\n", FILE_APPEND);

    if (!$response) {
        error_log("cURL error: $error");
        return ['content' => ''];
    }

    $json = json_decode($response, true);
    return [
        'content' => $json['choices'][0]['message']['content'] ?? ''
    ];
}
