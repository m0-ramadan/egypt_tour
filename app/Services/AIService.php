<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function generateText($prompt, $language = 'ar', $maxTokens = 1000)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => "أنت كاتب محترف باللغة {$language}."],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ]);

        return $response->json()['choices'][0]['message']['content'] ?? null;
    }

    // المزيد من الدوال للترجمة والتحسين...
}
