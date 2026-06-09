<?php

namespace App\AI\HcmueChatbot\LLM;

class EmbeddingService
{
    /**
     * Generate an embedding vector for a given text.
     *
     * @param  string  $text  Text to embed.
     * @return array<float> Array of floats representing the embedding vector.
     */
    public function embed(string $text): array
    {
        // Clean text to avoid issues with formatting
        $cleanText = str_replace(["\r", "\t"], [' ', ' '], $text);

        $driverName = env('AI_LLM_DRIVER', 'gemini');
        $modelName = null;

        if ($driverName === 'openai') {
            $modelName = config('ai.embedding.model', 'text-embedding-3-small');
        }

        $provider = LlmGateway::driver($driverName, $modelName);

        return $provider->embed($cleanText);
    }
}
