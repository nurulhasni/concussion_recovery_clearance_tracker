<?php

namespace Tests\Feature;

use App\Contracts\AiCompletionProvider;
use App\Services\AiProviders\GeminiProvider;
use App\Services\AiProviders\OpenAiCompatibleProvider;
use Tests\TestCase;

class AiProviderBindingTest extends TestCase
{
    public function test_gemini_provider_is_bound_by_default(): void
    {
        config(['services.ai.provider' => 'gemini']);

        $provider = app(AiCompletionProvider::class);

        $this->assertInstanceOf(GeminiProvider::class, $provider);
    }

    public function test_openai_compatible_provider_is_bound_when_configured(): void
    {
        config(['services.ai.provider' => 'openai_compatible']);

        $provider = app(AiCompletionProvider::class);

        $this->assertInstanceOf(OpenAiCompatibleProvider::class, $provider);
    }
}
