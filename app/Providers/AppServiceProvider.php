<?php

namespace App\Providers;

use App\Contracts\AiCompletionProvider;
use App\Services\AiProviders\GeminiProvider;
use App\Services\AiProviders\OpenAiCompatibleProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dynamic AI Completion Provider binding:
        // Switching AI_PROVIDER to "openai_compatible" and setting AI_API_KEY, AI_BASE_URL, and AI_MODEL
        // is enough to switch providers — e.g. for Groq set AI_BASE_URL=https://api.groq.com/openai/v1,
        // for OpenRouter set AI_BASE_URL=https://openrouter.ai/api/v1, for Together set AI_BASE_URL=https://api.together.xyz/v1.
        $this->app->bind(AiCompletionProvider::class, function () {
            $provider = config('services.ai.provider', 'gemini');

            return match ($provider) {
                'openai_compatible' => new OpenAiCompatibleProvider,
                default => new GeminiProvider,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
