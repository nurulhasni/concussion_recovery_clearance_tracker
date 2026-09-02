<?php

namespace App\Services\AiProviders;

use App\Contracts\AiCompletionProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiProvider implements AiCompletionProvider
{
    /**
     * Perform a completion request using Google AI Studio / Gemini API.
     */
    public function complete(string $prompt): string
    {
        $apiKey = config('services.ai.gemini.key');
        $model = config('services.ai.gemini.model', 'gemma-4-26b-a4b-it');

        if (empty($apiKey)) {
            Log::warning('Gemini API key is not configured in services.ai.gemini.key');
            throw new RuntimeException('Gemini API key is missing.');
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(30)->post($endpoint, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->failed()) {
            Log::error('Gemini API error: '.$response->body());
            throw new RuntimeException('Gemini API request failed with status: '.$response->status());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null) {
            Log::error('Gemini API response missing candidates text: '.json_encode($data));
            throw new RuntimeException('Gemini response format invalid or empty.');
        }

        return $text;
    }
}
