<?php

namespace App\Http\Controllers;

use App\Core\Services\N8nService;
use App\Core\Services\TopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected TopicService $topicService,
        protected N8nService $n8nService
    ) {}

    /**
     * Handle n8n callback webhook.
     *
     * This endpoint receives status updates from n8n about topic processing.
     * Expected payload:
     * {
     *   "execution_id": "string",
     *   "topic_id": "int",
     *   "status": "processing|completed|failed",
     *   "result": {...},  // optional, for completed status
     *   "error": "string", // optional, for failed status
     *   "details": {...}   // optional, additional error details
     * }
     */
    public function n8nCallback(Request $request): JsonResponse
    {
        Log::info('[WebhookController] n8n callback received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        // Validate required fields
        $validated = $request->validate([
            'execution_id' => 'required|string',
            'status' => 'required|string|in:processing,completed,failed',
            'topic_id' => 'sometimes|integer',
            'result' => 'sometimes|array',
            'error' => 'sometimes|string',
            'details' => 'sometimes|array',
        ]);

        // Verify API key if configured
        $configuredKey = config('ai.n8n.api_key');
        if ($configuredKey) {
            $providedKey = $request->header('X-N8N-API-KEY');

            if ($providedKey !== $configuredKey) {
                Log::warning('[WebhookController] Invalid n8n API key', [
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
        }

        $result = $this->topicService->handleN8nCallback(
            executionId: $validated['execution_id'],
            status: $validated['status'],
            data: [
                'result' => $validated['result'] ?? null,
                'error' => $validated['error'] ?? null,
                'details' => $validated['details'] ?? null,
            ]
        );

        if (!$result->isSuccess()) {
            Log::error('[WebhookController] Failed to process n8n callback', [
                'execution_id' => $validated['execution_id'],
                'message' => $result->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback processed successfully',
        ]);
    }

    /**
     * Test n8n connection health.
     */
    public function n8nHealth(): JsonResponse
    {
        $result = $this->n8nService->testConnection();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 503);
    }
}
