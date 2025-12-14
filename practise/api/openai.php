<?php
function openai_call(string $prompt): array
{
    $apiKey = "YOUR_OPENAI_API_KEY";

    $payload = [
        "model" => "gpt-4o",
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
    curl_close($ch);

    if (!$response) {
        return [];
    }

    $json = json_decode($response, true);
    $content = $json['choices'][0]['message']['content'] ?? "";

    $parsed = json_decode($content, true);
    return is_array($parsed) ? $parsed : [];
}
