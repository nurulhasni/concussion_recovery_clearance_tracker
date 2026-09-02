<?php

namespace App\Contracts;

interface AiCompletionProvider
{
    /**
     * Perform a plain-text completion call.
     *
     * @param  string  $prompt  Full prompt including instructions
     * @return string Raw text response from the AI provider before JSON decoding
     */
    public function complete(string $prompt): string;
}
