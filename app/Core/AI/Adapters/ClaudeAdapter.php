<?php

namespace App\Core\AI\Adapters;

use App\Core\AI\Contracts\AIResponse;
use App\Enums\AIProvider;
use Illuminate\Support\Facades\Http;

/**
 * Anthropic Claude API adapter.
 */
class ClaudeAdapter extends BaseAIAdapter
{
    protected function getDefaultModel(): string
    {
        return 'claude-3-5-sonnet-20241022';
    }

    protected function getProvider(): AIProvider
    {
        return AIProvider::CLAUDE;
    }

    public function generateTopics(string $niche, array $keywords = [], ?string $customPrompt = null): AIResponse
    {
        $prompt = $this->buildTopicPrompt($niche, $keywords, $customPrompt);

        return $this->chat($prompt);
    }

    public function generateSingleTopic(string $niche, ?string $context = null): AIResponse
    {
        $prompt = $this->buildSingleTopicPrompt($niche, $context);

        return $this->chat($prompt);
    }

    public function testConnection(): bool
    {
        try {
            // Claude doesn't have a simple health check endpoint,
            // so we send a minimal request
            $response = Http::withHeaders([
                'x-api-key' => $this->config['api_key'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
                ->timeout(10)
                ->post($this->config['base_url'] . '/messages', [
                    'model' => $this->model,
                    'max_tokens' => 10,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Hi']
                    ]
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            $this->logError('Connection test', $e->getMessage());
            return false;
        }
    }

    /**
     * Send a message request to Claude.
     */
    protected function chat(string $prompt): AIResponse
    {
        $this->logRequest('Message', ['prompt_length' => strlen($prompt)]);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->config['api_key'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->config['timeout'] ?? 60)
                ->post($this->config['base_url'] . '/messages', [
                    'model' => $this->model,
                    'max_tokens' => $this->config['max_tokens'] ?? 2048,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message', 'Unknown error');
                $this->logError('Message', $error, ['status' => $response->status()]);

                return AIResponse::failure($error, [
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            // Claude returns content as an array of content blocks
            $contentBlocks = $response->json('content', []);
            $content = collect($contentBlocks)
                ->where('type', 'text')
                ->pluck('text')
                ->implode("\n");

            $usage = $response->json('usage');

            $this->logRequest('Message success', [
                'input_tokens' => $usage['input_tokens'] ?? null,
                'output_tokens' => $usage['output_tokens'] ?? null,
            ]);

            return AIResponse::success(
                content: $content,
                prompt: $prompt,
                model: $this->model,
                provider: $this->getProviderName(),
                metadata: [
                    'usage' => $usage,
                    'stop_reason' => $response->json('stop_reason'),
                ]
            );

        } catch (\Exception $e) {
            $this->logError('Message', $e->getMessage());

            return AIResponse::failure($e->getMessage());
        }
    }
}
