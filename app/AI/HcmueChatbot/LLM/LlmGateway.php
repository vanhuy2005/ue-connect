<?php

namespace App\AI\HcmueChatbot\LLM;

class LlmGateway
{
    /**
     * Get the LLM provider instance based on configured driver.
     *
     * @param  string|null  $driver  Default configured driver. Options: gemini, openai, openrouter
     * @param  string|null  $model  Custom model name override.
     */
    public static function driver(?string $driver = null, ?string $model = null): LlmProviderInterface
    {
        $driver = $driver ?: env('AI_LLM_DRIVER', 'gemini');

        return match ($driver) {
            'openai' => new OpenAiProvider(
                env('OPENAI_API_KEY'),
                $model ?: env('OPENAI_MODEL', 'gpt-4o-mini')
            ),
            'openrouter' => new OpenRouterProvider(
                env('OPENROUTER_API_KEY'),
                $model ?: env('OPENROUTER_MODEL', 'google/gemini-2.0-flash')
            ),
            'gemini' => new GeminiProvider(
                config('services.gemini.key', env('GEMINI_API_KEY')),
                $model ?: env('GEMINI_MODEL', 'gemini-2.0-flash')
            ),
            default => new GeminiProvider(
                config('services.gemini.key', env('GEMINI_API_KEY')),
                $model ?: 'gemini-2.0-flash'
            )
        };
    }
}
