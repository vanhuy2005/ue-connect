<?php

namespace App\AI\HcmueChatbot\LLM;

interface LlmProviderInterface
{
    /**
     * Generate content from a prompt.
     *
     * @param  array  $options  Configuration options (temperature, json mode, etc.)
     * @return array{text: string, raw: array, usage: array{input_tokens: int, output_tokens: int, total_tokens: int}}
     */
    public function generate(string $prompt, array $options = []): array;

    /**
     * Generate embeddings for a given text.
     *
     * @return array<float>
     */
    public function embed(string $text): array;
}
