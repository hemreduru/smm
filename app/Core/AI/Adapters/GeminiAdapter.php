<?php

namespace App\Core\AI\Adapters;

use App\Core\AI\Contracts\AIResponse;
use App\Enums\AIProvider;
use Illuminate\Support\Facades\Http;

/**
 * Google Gemini API adapter.
 */
class GeminiAdapter extends BaseAIAdapter
{
    protected function getDefaultModel(): string
    {
        return 'gemini-1.5-pro';
    }

    protected function getProvider(): AIProvider
    {
        return AIProvider::GEMINI;
    }

    public function generateTopics(string $niche, array $keywords = [], ?string $customPrompt = null): AIResponse
    {
        $prompt = $this->buildTopicPrompt($niche, $keywords, $customPrompt);

        return $this->generateContent($prompt);
    }

    public function generateSingleTopic(string $niche, ?string $context = null): AIResponse
    {
        $prompt = $this->buildSingleTopicPrompt($niche, $context);

        return $this->generateContent($prompt);
    }

    public function testConnection(): bool
    {
        try {
            $url = $this->buildUrl('models');

            $response = Http::timeout(10)->get($url);

            return $response->successful();
        } catch (\Exception $e) {
            $this->logError('Connection test', $e->getMessage());
            return false;
        }
    }

    /**
     * Build the API URL with API key.
     */
    protected function buildUrl(string $endpoint): string
    {
        $baseUrl = rtrim($this->config['base_url'], '/');
        return "{$baseUrl}/{$endpoint}?key={$this->config['api_key']}";
    }

    /**
     * Send a generate content request to Gemini.
     */
    protected function generateContent(string $prompt): AIResponse
    {
        $this->logRequest('Generate content', ['prompt_length' => strlen($prompt)]);

        try {
            $url = $this->buildUrl("models/{$this->model}:generateContent");

            $response = Http::timeout($this->config['timeout'] ?? 60)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => config('ai.topic_generation.temperature', 0.8),
                        'maxOutputTokens' => $this->config['max_tokens'] ?? 2048,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_ONLY_HIGH'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_ONLY_HIGH'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_ONLY_HIGH'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_ONLY_HIGH'
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message', 'Unknown error');
                $this->logError('Generate content', $error, ['status' => $response->status()]);

                return AIResponse::failure($error, [
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);
            }

            // Extract text from Gemini response
            $candidates = $response->json('candidates', []);
            $content = '';

            if (!empty($candidates)) {
                $parts = $candidates[0]['content']['parts'] ?? [];
                $content = collect($parts)->pluck('text')->implode("\n");
            }

            $usageMetadata = $response->json('usageMetadata');

            $this->logRequest('Generate content success', [
                'prompt_tokens' => $usageMetadata['promptTokenCount'] ?? null,
                'output_tokens' => $usageMetadata['candidatesTokenCount'] ?? null,
            ]);

            return AIResponse::success(
                content: $content,
                prompt: $prompt,
                model: $this->model,
                provider: $this->getProviderName(),
                metadata: [
                    'usage' => $usageMetadata,
                    'finish_reason' => $candidates[0]['finishReason'] ?? null,
                ]
            );

        } catch (\Exception $e) {
            $this->logError('Generate content', $e->getMessage());

            return AIResponse::failure($e->getMessage());
        }
    }
}
