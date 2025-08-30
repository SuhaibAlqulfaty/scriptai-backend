<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "API Key from env: " . env('OPENAI_API_KEY') . PHP_EOL;
echo "API Key length: " . strlen(env('OPENAI_API_KEY')) . PHP_EOL;
echo "First 10 chars: " . substr(env('OPENAI_API_KEY'), 0, 10) . PHP_EOL;
echo "Last 10 chars: " . substr(env('OPENAI_API_KEY'), -10) . PHP_EOL;

// Test direct API call
$apiKey = env('OPENAI_API_KEY');
echo "Testing API key validity..." . PHP_EOL;

$data = json_encode([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, just testing the API key']
    ],
    'max_tokens' => 10
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . PHP_EOL;
if ($httpCode !== 200) {
    echo "Error Response: " . $response . PHP_EOL;
} else {
    echo "API Key is valid!" . PHP_EOL;
}

