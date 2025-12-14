<?php

function openai_call($prompt) {
    $apiKey = "YOUR_OPENAI_API_KEY";

    $payload = [
        "model" => "gpt-4o",
        "messages" => [
            ["role" => "system", "content" =>
                "You are a French language tutor. Speak only French.
                 Be concise. Adapt difficulty. Correct gently."
            ],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.5
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return json_decode($data['choices'][0]['message']['content'], true);
}
