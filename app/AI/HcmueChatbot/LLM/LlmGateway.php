<?php

namespace App\AI\HcmueChatbot\LLM;

class LlmGateway
{
    /**
     * Get the LLM provider instance based on configured driver.
     *
     * Reads 'LLM_PROVIDER' first (via config('ai.llm_provider')),
     * falls back to legacy 'AI_LLM_DRIVER'.
     * Supported: gemini, openrouter, ollama
     *
     * @param  string|null  $driver  Override provider name. Options: gemini, openai, openrouter, ollama
     * @param  string|null  $model  Custom model name override.
     */
    public static function driver(?string $driver = null, ?string $model = null): LlmProviderInterface
    {
        $driver = $driver ?: config('ai.llm_provider', 'gemini');

        return match ($driver) {
            'openai' => new OpenAiProvider(
                env('OPENAI_API_KEY'),
                $model ?: env('OPENAI_MODEL', 'gpt-4o-mini')
            ),
            'openrouter' => new OpenRouterProvider(
                config('ai.openrouter.api_key', env('OPENROUTER_API_KEY')),
                $model ?: config('ai.openrouter.model', 'google/gemini-2.0-flash')
            ),
            'ollama' => new OllamaProvider(
                config('ai.ollama.base_url'),
                $model ?: config('ai.ollama.chat_model'),
                config('ai.ollama.timeout'),
            ),
            default => new GeminiProvider(
                config('ai.gemini.api_key', env('GEMINI_API_KEY')),
                $model ?: config('ai.gemini.model', 'gemini-2.0-flash')
            ),
        };
    }

    /**
     * Get the LLM provider with automatic fallback support.
     *
     * If the primary provider is 'ollama' and it fails at call-time,
     * the caller must wrap the generate() call and invoke this method
     * again with the fallback driver — or use AnswerComposerService which
     * handles this transparently via the withFallback pattern.
     *
     * Returns an array with the provider instance and metadata.
     *
     * @return array{provider: LlmProviderInterface, primary: string, fallback_enabled: bool, fallback_provider: string}
     */
    public static function driverWithFallback(?string $driver = null, ?string $model = null): array
    {
        $primary = $driver ?: config('ai.llm_provider', 'gemini');
        $fallbackEnabled = $primary === 'ollama' && config('ai.ollama.fallback_enabled', true);
        $fallbackProvider = config('ai.ollama.fallback_provider', 'gemini');

        return [
            'provider' => self::driver($primary, $model),
            'primary' => $primary,
            'fallback_enabled' => $fallbackEnabled,
            'fallback_provider' => $fallbackProvider,
        ];
    }

    /**
     * Resolve the model name for the active provider (for logging).
     */
    public static function activeModelName(?string $driver = null): string
    {
        $driver = $driver ?: config('ai.llm_provider', 'gemini');

        return match ($driver) {
            'ollama' => config('ai.ollama.chat_model', 'gemma4:e2b'),
            'openrouter' => config('ai.openrouter.model', 'google/gemini-2.0-flash'),
            default => config('ai.gemini.model', 'gemini-2.0-flash'),
        };
    }
}
