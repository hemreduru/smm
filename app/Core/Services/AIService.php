<?php

namespace App\Core\Services;

use App\Core\AI\AIProviderFactory;
use App\Core\AI\Contracts\AIProviderInterface;
use App\Core\AI\Contracts\AIResponse;
use App\Enums\AIProvider;
use Illuminate\Support\Facades\Log;

/**
 * Service for AI-powered topic generation.
 */
class AIService extends BaseService
{
    protected ?AIProviderInterface $provider = null;

    /**
     * Set the AI provider to use.
     *
     * @param AIProvider|string $provider
     * @param string|null $model
     * @return self
     */
    public function useProvider(AIProvider|string $provider, ?string $model = null): self
    {
        $this->provider = AIProviderFactory::make($provider, $model);

        return $this;
    }

    /**
     * Get the current provider (or default).
     *
     * @return AIProviderInterface
     */
    protected function getProvider(): AIProviderInterface
    {
        if (!$this->provider) {
            $this->provider = AIProviderFactory::default();
        }

        return $this->provider;
    }

    /**
     * Generate topic suggestions for a niche.
     *
     * @param string $niche
     * @param array $keywords
     * @param string|null $customPrompt
     * @return AIResponse
     */
    public function generateTopics(string $niche, array $keywords = [], ?string $customPrompt = null): AIResponse
    {
        Log::info('[AIService] Generating topics', [
            'niche' => $niche,
            'keywords' => $keywords,
            'provider' => $this->getProvider()->getProviderName(),
            'model' => $this->getProvider()->getModel(),
        ]);

        $response = $this->getProvider()->generateTopics($niche, $keywords, $customPrompt);

        if (!$response->success) {
            Log::warning('[AIService] Topic generation failed', [
                'error' => $response->error,
            ]);
        }

        return $response;
    }

    /**
     * Generate a single detailed topic.
     *
     * @param string $niche
     * @param string|null $context
     * @return AIResponse
     */
    public function generateSingleTopic(string $niche, ?string $context = null): AIResponse
    {
        Log::info('[AIService] Generating single topic', [
            'niche' => $niche,
            'provider' => $this->getProvider()->getProviderName(),
            'model' => $this->getProvider()->getModel(),
        ]);

        $response = $this->getProvider()->generateSingleTopic($niche, $context);

        if (!$response->success) {
            Log::warning('[AIService] Single topic generation failed', [
                'error' => $response->error,
            ]);
        }

        return $response;
    }

    /**
     * Test connection to a specific provider.
     *
     * @param AIProvider|string $provider
     * @return bool
     */
    public function testProviderConnection(AIProvider|string $provider): bool
    {
        try {
            $adapter = AIProviderFactory::make($provider);
            return $adapter->testConnection();
        } catch (\Exception $e) {
            Log::warning('[AIService] Provider connection test failed', [
                'provider' => is_string($provider) ? $provider : $provider->value,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get all available AI providers.
     *
     * @return array
     */
    public function getAvailableProviders(): array
    {
        return AIProviderFactory::availableProviders();
    }

    /**
     * Get available models for a provider.
     *
     * @param AIProvider|string $provider
     * @return array
     */
    public function getAvailableModels(AIProvider|string $provider): array
    {
        if (is_string($provider)) {
            $provider = AIProvider::from($provider);
        }

        return $provider->availableModels();
    }

    /**
     * Get provider information for UI display.
     *
     * @return array
     */
    public function getProvidersInfo(): array
    {
        $info = [];

        foreach (AIProvider::cases() as $provider) {
            $isAvailable = AIProviderFactory::isAvailable($provider);

            $info[] = [
                'value' => $provider->value,
                'label' => $provider->label(),
                'icon' => $provider->icon(),
                'color' => $provider->color(),
                'available' => $isAvailable,
                'models' => $provider->availableModels(),
                'default_model' => $provider->defaultModel(),
            ];
        }

        return $info;
    }
}
