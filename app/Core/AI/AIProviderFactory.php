<?php

namespace App\Core\AI;

use App\Core\AI\Adapters\ClaudeAdapter;
use App\Core\AI\Adapters\GeminiAdapter;
use App\Core\AI\Adapters\OpenAIAdapter;
use App\Core\AI\Contracts\AIProviderInterface;
use App\Enums\AIProvider;
use InvalidArgumentException;

/**
 * Factory for creating AI provider adapters.
 */
class AIProviderFactory
{
    /**
     * Create an AI provider adapter.
     *
     * @param AIProvider|string $provider
     * @param string|null $model Optional model override
     * @return AIProviderInterface
     * @throws InvalidArgumentException
     */
    public static function make(AIProvider|string $provider, ?string $model = null): AIProviderInterface
    {
        if (is_string($provider)) {
            $provider = AIProvider::from($provider);
        }

        $config = config("ai.providers.{$provider->value}");

        if (empty($config['api_key'])) {
            throw new InvalidArgumentException(
                "API key not configured for provider: {$provider->value}"
            );
        }

        $adapter = match($provider) {
            AIProvider::OPENAI => new OpenAIAdapter($config),
            AIProvider::CLAUDE => new ClaudeAdapter($config),
            AIProvider::GEMINI => new GeminiAdapter($config),
        };

        if ($model) {
            $adapter->setModel($model);
        }

        return $adapter;
    }

    /**
     * Get the default provider adapter.
     *
     * @return AIProviderInterface
     */
    public static function default(): AIProviderInterface
    {
        $defaultProvider = config('ai.default', 'openai');

        return self::make($defaultProvider);
    }

    /**
     * Get all available providers that have API keys configured.
     *
     * @return array<AIProvider>
     */
    public static function availableProviders(): array
    {
        $available = [];

        foreach (AIProvider::cases() as $provider) {
            $config = config("ai.providers.{$provider->value}");

            if (!empty($config['api_key'])) {
                $available[] = $provider;
            }
        }

        return $available;
    }

    /**
     * Check if a provider is available (has API key configured).
     *
     * @param AIProvider|string $provider
     * @return bool
     */
    public static function isAvailable(AIProvider|string $provider): bool
    {
        if (is_string($provider)) {
            try {
                $provider = AIProvider::from($provider);
            } catch (\ValueError $e) {
                return false;
            }
        }

        $config = config("ai.providers.{$provider->value}");

        return !empty($config['api_key']);
    }
}
