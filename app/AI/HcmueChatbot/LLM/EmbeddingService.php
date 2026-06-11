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

        $driverName = config('ai.embedding.provider', 'gemini');
        $modelName = config('ai.embedding.model', 'gemini-embedding-001');

        $provider = LlmGateway::driver($driverName, $modelName);

        return $provider->embed($cleanText);
    }

    /**
     * Generate embedding vectors for multiple texts.
     *
     * @param  array<string>  $texts  Array of texts to embed.
     * @return array<array<float>> Array of embedding vectors.
     */
    public function batchEmbed(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $driverName = config('ai.embedding.provider', 'gemini');
        $modelName = config('ai.embedding.model', 'gemini-embedding-001');

        $provider = LlmGateway::driver($driverName, $modelName);

        if (method_exists($provider, 'batchEmbed')) {
            $cleanTexts = array_map(function ($text) {
                return str_replace(["\r", "\t"], [' ', ' '], $text);
            }, $texts);

            return $provider->batchEmbed($cleanTexts);
        }

        // Fallback to sequential
        $vectors = [];
        foreach ($texts as $text) {
            $vectors[] = $this->embed($text);
        }

        return $vectors;
    }
}
