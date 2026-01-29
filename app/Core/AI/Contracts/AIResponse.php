<?php

namespace App\Core\AI\Contracts;

/**
 * Represents a response from an AI provider.
 */
class AIResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $content,
        public readonly ?string $error = null,
        public readonly ?array $metadata = null,
        public readonly ?string $prompt = null,
        public readonly ?string $model = null,
        public readonly ?string $provider = null,
    ) {}

    /**
     * Create a successful response.
     */
    public static function success(
        string $content,
        ?string $prompt = null,
        ?string $model = null,
        ?string $provider = null,
        ?array $metadata = null
    ): self {
        return new self(
            success: true,
            content: $content,
            prompt: $prompt,
            model: $model,
            provider: $provider,
            metadata: $metadata,
        );
    }

    /**
     * Create a failed response.
     */
    public static function failure(string $error, ?array $metadata = null): self
    {
        return new self(
            success: false,
            content: null,
            error: $error,
            metadata: $metadata,
        );
    }

    /**
     * Parse topics from content (expects JSON array format).
     *
     * @return array
     */
    public function parseTopics(): array
    {
        if (!$this->success || !$this->content) {
            return [];
        }

        // Try to extract JSON from the response
        $content = $this->content;

        // If content contains markdown code blocks, extract the JSON
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Fallback: return as single topic if not JSON
        return [
            ['title' => $content, 'description' => null]
        ];
    }
}
