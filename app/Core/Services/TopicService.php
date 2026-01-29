<?php

namespace App\Core\Services;

use App\Core\AI\AIProviderFactory;
use App\Core\Repositories\TopicRepository;
use App\Core\Results\FailResult;
use App\Core\Results\Result;
use App\Core\Results\ServerErrorResult;
use App\Core\Results\SuccessResult;
use App\Enums\AIProvider;
use App\Enums\TopicStatus;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TopicService extends BaseService
{
    public function __construct(
        protected TopicRepository $repository,
        protected AIService $aiService,
        protected N8nService $n8nService
    ) {}

    /**
     * Get topics for the current workspace with pagination.
     *
     * @param int $workspaceId
     * @param int $perPage
     * @return Result
     */
    public function getForWorkspace(int $workspaceId, int $perPage = 15): Result
    {
        try {
            $topics = $this->repository->paginateForWorkspace($workspaceId, $perPage);
            return new SuccessResult(__('messages.topics.list_success'), ['topics' => $topics]);
        } catch (\Exception $e) {
            Log::error('[TopicService] Error fetching topics', ['error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.list_error'));
        }
    }

    /**
     * Get DataTable query for topics.
     *
     * @param int $workspaceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getDataTableQuery(int $workspaceId)
    {
        return $this->repository->queryForDataTable($workspaceId);
    }

    /**
     * Find a topic by ID.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Result
     */
    public function find(int $id, int $workspaceId): Result
    {
        try {
            $topic = $this->repository->findForWorkspace($id, $workspaceId);

            if (!$topic) {
                return new FailResult(__('messages.topics.not_found'));
            }

            return new SuccessResult(__('messages.topics.found'), ['topic' => $topic]);
        } catch (\Exception $e) {
            Log::error('[TopicService] Error finding topic', ['id' => $id, 'error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.find_error'));
        }
    }

    /**
     * Create a new topic manually (without AI).
     *
     * @param array $data
     * @param User $user
     * @return Result
     */
    public function create(array $data, User $user): Result
    {
        try {
            DB::beginTransaction();

            $topic = $this->repository->create([
                'workspace_id' => $user->current_workspace_id,
                'created_by' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'niche' => $data['niche'] ?? null,
                'keywords' => $data['keywords'] ?? [],
                'status' => TopicStatus::DRAFT,
            ]);

            DB::commit();

            Log::info('[TopicService] Topic created', [
                'topic_id' => $topic->id,
                'user_id' => $user->id,
            ]);

            return new SuccessResult(__('messages.topics.created'), ['topic' => $topic]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error creating topic', ['error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.create_error'));
        }
    }

    /**
     * Generate topics using AI.
     *
     * @param string $niche
     * @param array $keywords
     * @param User $user
     * @param AIProvider|null $provider
     * @param string|null $model
     * @return Result
     */
    public function generateWithAI(
        string $niche,
        array $keywords,
        User $user,
        ?AIProvider $provider = null,
        ?string $model = null
    ): Result {
        try {
            // Use specified provider or default
            if ($provider) {
                $this->aiService->useProvider($provider, $model);
            }

            $aiResponse = $this->aiService->generateTopics($niche, $keywords);

            if (!$aiResponse->isSuccess()) {
                return new FailResult(__('messages.topics.ai_generation_failed', [
                    'error' => $aiResponse->getError(),
                ]));
            }

            DB::beginTransaction();

            $topics = [];
            $parsedTopics = $aiResponse->parseTopics();

            foreach ($parsedTopics as $topicData) {
                $topic = $this->repository->create([
                    'workspace_id' => $user->current_workspace_id,
                    'created_by' => $user->id,
                    'title' => $topicData['title'],
                    'description' => $topicData['description'] ?? null,
                    'niche' => $niche,
                    'keywords' => $keywords,
                    'ai_provider' => $provider?->value ?? config('ai.default_provider'),
                    'ai_model' => $model ?? config("ai.providers.{$provider?->value}.default_model"),
                    'ai_prompt' => $aiResponse->getRawResponse()['prompt'] ?? null,
                    'ai_response' => $aiResponse->getRawResponse(),
                    'status' => TopicStatus::DRAFT,
                ]);

                $topics[] = $topic;
            }

            DB::commit();

            Log::info('[TopicService] AI topics generated', [
                'count' => count($topics),
                'niche' => $niche,
                'provider' => $provider?->value,
                'user_id' => $user->id,
            ]);

            return new SuccessResult(
                __('messages.topics.ai_generated', ['count' => count($topics)]),
                ['topics' => $topics]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error generating AI topics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return new ServerErrorResult(__('messages.topics.ai_generation_error'));
        }
    }

    /**
     * Update a topic.
     *
     * @param int $id
     * @param array $data
     * @param int $workspaceId
     * @return Result
     */
    public function update(int $id, array $data, int $workspaceId): Result
    {
        try {
            $topic = $this->repository->findForWorkspace($id, $workspaceId);

            if (!$topic) {
                return new FailResult(__('messages.topics.not_found'));
            }

            if (!$topic->canBeEdited()) {
                return new FailResult(__('messages.topics.cannot_edit'));
            }

            DB::beginTransaction();

            $this->repository->updateTopic($topic, [
                'title' => $data['title'] ?? $topic->title,
                'description' => $data['description'] ?? $topic->description,
                'niche' => $data['niche'] ?? $topic->niche,
                'keywords' => $data['keywords'] ?? $topic->keywords,
                'is_scheduled' => $data['is_scheduled'] ?? $topic->is_scheduled,
                'scheduled_at' => $data['scheduled_at'] ?? $topic->scheduled_at,
            ]);

            $topic->refresh();

            DB::commit();

            Log::info('[TopicService] Topic updated', ['topic_id' => $topic->id]);

            return new SuccessResult(__('messages.topics.updated'), ['topic' => $topic]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error updating topic', ['id' => $id, 'error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.update_error'));
        }
    }

    /**
     * Delete a topic.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Result
     */
    public function delete(int $id, int $workspaceId): Result
    {
        try {
            $topic = $this->repository->findForWorkspace($id, $workspaceId);

            if (!$topic) {
                return new FailResult(__('messages.topics.not_found'));
            }

            if (!$topic->canBeEdited()) {
                return new FailResult(__('messages.topics.cannot_delete'));
            }

            DB::beginTransaction();

            $this->repository->deleteTopic($topic);

            DB::commit();

            Log::info('[TopicService] Topic deleted', ['topic_id' => $id]);

            return new SuccessResult(__('messages.topics.deleted'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error deleting topic', ['id' => $id, 'error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.delete_error'));
        }
    }

    /**
     * Approve a topic.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Result
     */
    public function approve(int $id, int $workspaceId): Result
    {
        try {
            $topic = $this->repository->findForWorkspace($id, $workspaceId);

            if (!$topic) {
                return new FailResult(__('messages.topics.not_found'));
            }

            if (!$topic->isDraft()) {
                return new FailResult(__('messages.topics.cannot_approve'));
            }

            DB::beginTransaction();

            $topic->approve();

            DB::commit();

            Log::info('[TopicService] Topic approved', ['topic_id' => $topic->id]);

            return new SuccessResult(__('messages.topics.approved'), ['topic' => $topic]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error approving topic', ['id' => $id, 'error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.approve_error'));
        }
    }

    /**
     * Send a topic to n8n for processing.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Result
     */
    public function sendToN8n(int $id, int $workspaceId): Result
    {
        try {
            $topic = $this->repository->findForWorkspace($id, $workspaceId);

            if (!$topic) {
                return new FailResult(__('messages.topics.not_found'));
            }

            if (!$topic->canBeSentToN8n()) {
                return new FailResult(__('messages.topics.cannot_send'));
            }

            // Send to n8n
            $n8nResult = $this->n8nService->sendTopic($topic);

            if (!$n8nResult['success']) {
                $topic->markAsFailed($n8nResult['error']);
                return new FailResult(__('messages.topics.n8n_send_failed', [
                    'error' => $n8nResult['error'],
                ]));
            }

            // Update topic status
            $topic->markAsSent($n8nResult['execution_id']);

            Log::info('[TopicService] Topic sent to n8n', [
                'topic_id' => $topic->id,
                'execution_id' => $n8nResult['execution_id'],
            ]);

            return new SuccessResult(__('messages.topics.sent_to_n8n'), ['topic' => $topic]);
        } catch (\Exception $e) {
            Log::error('[TopicService] Error sending topic to n8n', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            return new ServerErrorResult(__('messages.topics.send_error'));
        }
    }

    /**
     * Handle n8n callback for a topic.
     *
     * @param string $executionId
     * @param string $status
     * @param array $data
     * @return Result
     */
    public function handleN8nCallback(string $executionId, string $status, array $data = []): Result
    {
        try {
            $topic = $this->repository->findByN8nExecutionId($executionId);

            if (!$topic) {
                Log::warning('[TopicService] Topic not found for n8n callback', [
                    'execution_id' => $executionId,
                ]);
                return new FailResult('Topic not found');
            }

            DB::beginTransaction();

            switch ($status) {
                case 'processing':
                    $topic->markAsProcessing();
                    break;

                case 'completed':
                    $topic->markAsCompleted($data['result'] ?? null);
                    break;

                case 'failed':
                    $topic->markAsFailed(
                        $data['error'] ?? 'Unknown error',
                        $data['details'] ?? null
                    );
                    break;

                default:
                    Log::warning('[TopicService] Unknown n8n callback status', [
                        'status' => $status,
                        'execution_id' => $executionId,
                    ]);
            }

            DB::commit();

            Log::info('[TopicService] n8n callback processed', [
                'topic_id' => $topic->id,
                'execution_id' => $executionId,
                'status' => $status,
            ]);

            return new SuccessResult('Callback processed', ['topic' => $topic]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error processing n8n callback', [
                'execution_id' => $executionId,
                'error' => $e->getMessage(),
            ]);
            return new ServerErrorResult('Failed to process callback');
        }
    }

    /**
     * Reset a failed topic to draft.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Result
     */
    public function resetToDraft(int $id, int $workspaceId): Result
    {
        try {
            $topic = $this->repository->findForWorkspace($id, $workspaceId);

            if (!$topic) {
                return new FailResult(__('messages.topics.not_found'));
            }

            if (!$topic->isFailed()) {
                return new FailResult(__('messages.topics.cannot_reset'));
            }

            DB::beginTransaction();

            $topic->resetToDraft();

            DB::commit();

            Log::info('[TopicService] Topic reset to draft', ['topic_id' => $topic->id]);

            return new SuccessResult(__('messages.topics.reset'), ['topic' => $topic]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TopicService] Error resetting topic', ['id' => $id, 'error' => $e->getMessage()]);
            return new ServerErrorResult(__('messages.topics.reset_error'));
        }
    }

    /**
     * Get topic statistics for workspace.
     *
     * @param int $workspaceId
     * @return array
     */
    public function getStatistics(int $workspaceId): array
    {
        $counts = $this->repository->countByStatus($workspaceId);
        $niches = $this->repository->getNiches($workspaceId);

        return [
            'total' => array_sum($counts),
            'by_status' => $counts,
            'niches' => $niches,
            'niche_count' => count($niches),
        ];
    }

    /**
     * Get available AI providers info for UI.
     *
     * @return array
     */
    public function getAIProvidersInfo(): array
    {
        return $this->aiService->getProvidersInfo();
    }
}
