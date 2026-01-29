<?php

namespace App\Core\AI\Adapters;

use App\Core\AI\Contracts\AIResponse;
use App\Enums\AIProvider;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI API adapter.
 */
class OpenAIAdapter extends BaseAIAdapter
{
    protected function getDefaultModel(): string
    {
        return 'gpt-4o';
    }

    protected function getProvider(): AIProvider
    {
        return AIProvider::OPENAI;
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
            $response = Http::withToken($this->config['api_key'])
                ->timeout(10)
                ->get($this->config['base_url'] . '/models');

            return $response->successful();
        } catch (\Exception $e) {
            $this->logError('Connection test', $e->getMessage());
            return false;
        }
    }

    /**
     * Send a chat completion request to OpenAI.
     */
    protected function chat(string $prompt): AIResponse
    {
        $this->logRequest('Chat completion', ['prompt_length' => strlen($prompt)]);

        try {
            $response = Http::withToken($this->config['api_key'])
                ->timeout($this->config['timeout'] ?? 60)
                ->post($this->config['base_url'] . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Sen yardımcı bir asistansın. Yanıtlarını istenilen formatta ver.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => $this->config['max_tokens'] ?? 2048,
                    'temperature' => config('ai.topic_generation.temperature', 0.8),
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message', 'Unknown error');
                $this->logError('Chat completion', $error, ['status' => $response->status()]);

                return AIResponse::failure($error, [
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            $content = $response->json('choices.0.message.content');
            $usage = $response->json('usage');

            $this->logRequest('Chat completion success', [
                'tokens_used' => $usage['total_tokens'] ?? null,
            ]);

            return AIResponse::success(
                content: $content,
                prompt: $prompt,
                model: $this->model,
                provider: $this->getProviderName(),
                metadata: [
                    'usage' => $usage,
                    'finish_reason' => $response->json('choices.0.finish_reason'),
                ]
            );

        } catch (\Exception $e) {
            $this->logError('Chat completion', $e->getMessage());

            return AIResponse::failure($e->getMessage());
        }
    }
}
