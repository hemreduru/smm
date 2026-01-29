<?php

namespace App\Core\AI\Contracts;

/**
 * Interface for AI provider adapters.
 */
interface AIProviderInterface
{
    /**
     * Generate topic suggestions based on niche and keywords.
     *
     * @param string $niche The content niche/category
     * @param array $keywords Optional keywords to guide generation
     * @param string|null $customPrompt Optional custom prompt
     * @return AIResponse
     */
    public function generateTopics(string $niche, array $keywords = [], ?string $customPrompt = null): AIResponse;

    /**
     * Generate a single topic with detailed description.
     *
     * @param string $niche The content niche/category
     * @param string|null $context Additional context
     * @return AIResponse
     */
    public function generateSingleTopic(string $niche, ?string $context = null): AIResponse;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Get the model being used.
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Set the model to use.
     *
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self;

    /**
     * Test the API connection.
     *
     * @return bool
     */
    public function testConnection(): bool;
}
