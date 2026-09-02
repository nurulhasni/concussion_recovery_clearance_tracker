<?php

namespace App\Services\AiProviders;

use App\Contracts\AiCompletionProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiCompatibleProvider implements AiCompletionProvider
{
    /**
     * Perform a completion request using OpenAI or OpenAI-compatible APIs (Groq, OpenRouter, Together, DeepSeek, etc.).
     */
    public function complete(string $prompt): string
    {
        $apiKey = config('services.ai.openai_compatible.key');
        $baseUrl = rtrim(config('services.ai.openai_compatible.base_url', 'https://api.openai.com/v1'), '/');
        $model = config('services.ai.openai_compatible.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            Log::warning('OpenAI-compatible API key is not configured in services.ai.openai_compatible.key');
            throw new RuntimeException('OpenAI-compatible API key is missing.');
        }

        $endpoint = "{$baseUrl}/chat/completions";

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI-compatible API error: '.$response->body());
            throw new RuntimeException('OpenAI-compatible API request failed with status: '.$response->status());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;

        if ($content === null) {
            Log::error('OpenAI-compatible response missing message content: '.json_encode($data));
            throw new RuntimeException('OpenAI-compatible response format invalid or empty.');
        }

        return $content;
    }
}
