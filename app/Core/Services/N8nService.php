<?php

namespace App\Core\Services;

use App\Models\Topic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for n8n webhook integration.
 *
 * Note: n8n is self-hosted.
 */
class N8nService extends BaseService
{
    protected string $baseUrl;
    protected string $webhookPath;
    protected ?string $apiKey;
    protected int $timeout;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->baseUrl = config('ai.n8n.base_url', 'http://localhost:5678');
        $this->webhookPath = config('ai.n8n.webhook_path', '/webhook/topic');
        $this->apiKey = config('ai.n8n.api_key');
        $this->timeout = config('ai.n8n.timeout', 30);
        $this->verifySsl = config('ai.n8n.verify_ssl', false);
    }

    /**
     * Send a topic to n8n for processing.
     *
     * @param Topic $topic
     * @return array{success: bool, execution_id: ?string, error: ?string}
     */
    public function sendTopic(Topic $topic): array
    {
        Log::info('[N8nService] Sending topic to n8n', [
            'topic_id' => $topic->id,
            'workspace_id' => $topic->workspace_id,
        ]);

        try {
            $payload = $this->buildPayload($topic);
            $response = $this->sendWebhook($payload);

            if (!$response->successful()) {
                $error = $response->json('message', 'n8n request failed');

                Log::error('[N8nService] n8n webhook failed', [
                    'topic_id' => $topic->id,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return [
                    'success' => false,
                    'execution_id' => null,
                    'error' => $error,
                ];
            }

            // n8n typically returns the execution ID in the response
            $executionId = $response->json('executionId')
                ?? $response->json('execution_id')
                ?? $response->json('id')
                ?? 'n8n_' . now()->timestamp;

            Log::info('[N8nService] Topic sent successfully', [
                'topic_id' => $topic->id,
                'execution_id' => $executionId,
            ]);

            return [
                'success' => true,
                'execution_id' => $executionId,
                'error' => null,
            ];

        } catch (\Exception $e) {
            Log::error('[N8nService] Exception sending topic', [
                'topic_id' => $topic->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'execution_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build the webhook payload for a topic.
     *
     * @param Topic $topic
     * @return array
     */
    protected function buildPayload(Topic $topic): array
    {
        return [
            'topic_id' => $topic->id,
            'workspace_id' => $topic->workspace_id,
            'title' => $topic->title,
            'description' => $topic->description,
            'niche' => $topic->niche,
            'keywords' => $topic->keywords ?? [],
            'ai_response' => $topic->ai_response,
            'callback_url' => route('webhooks.n8n.callback'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Send a webhook request to n8n.
     *
     * @param array $payload
     * @return \Illuminate\Http\Client\Response
     */
    protected function sendWebhook(array $payload): \Illuminate\Http\Client\Response
    {
        $url = rtrim($this->baseUrl, '/') . $this->webhookPath;

        $request = Http::timeout($this->timeout);

        // Disable SSL verification for self-hosted n8n
        if (!$this->verifySsl) {
            $request = $request->withoutVerifying();
        }

        // Add API key header if configured
        if ($this->apiKey) {
            $request = $request->withHeaders([
                'X-N8N-API-KEY' => $this->apiKey,
            ]);
        }

        return $request->post($url, $payload);
    }

    /**
     * Test n8n connection.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(): array
    {
        try {
            $request = Http::timeout(10);

            if (!$this->verifySsl) {
                $request = $request->withoutVerifying();
            }

            // Try to reach n8n health endpoint
            $healthUrl = rtrim($this->baseUrl, '/') . '/healthz';
            $response = $request->get($healthUrl);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'n8n connection successful',
                ];
            }

            return [
                'success' => false,
                'message' => 'n8n returned status: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get the webhook URL for n8n to callback.
     *
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return route('webhooks.n8n.callback');
    }
}
