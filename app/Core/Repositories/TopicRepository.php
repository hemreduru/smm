<?php

namespace App\Core\Repositories;

use App\Enums\TopicStatus;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TopicRepository extends BaseRepository
{
    public function __construct(Topic $model)
    {
        parent::__construct($model);
    }

    /**
     * Get topics for a workspace.
     *
     * @param int $workspaceId
     * @return Collection
     */
    public function getForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['creator'])
            ->latest()
            ->get();
    }

    /**
     * Get topics for workspace with pagination.
     *
     * @param int $workspaceId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForWorkspace(int $workspaceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['creator'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get query builder for DataTables.
     *
     * @param int $workspaceId
     * @return Builder
     */
    public function queryForDataTable(int $workspaceId): Builder
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['creator'])
            ->select('topics.*');
    }

    /**
     * Find a topic by ID within a workspace.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Topic|null
     */
    public function findForWorkspace(int $id, int $workspaceId): ?Topic
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['creator'])
            ->find($id);
    }

    /**
     * Create a new topic.
     *
     * @param array $data
     * @return Topic
     */
    public function create(array $data): Topic
    {
        return $this->model->create($data);
    }

    /**
     * Update a topic.
     *
     * @param Topic $topic
     * @param array $data
     * @return bool
     */
    public function updateTopic(Topic $topic, array $data): bool
    {
        return $topic->update($data);
    }

    /**
     * Delete a topic.
     *
     * @param Topic $topic
     * @return bool
     */
    public function deleteTopic(Topic $topic): bool
    {
        return $topic->delete();
    }

    /**
     * Get topics by status.
     *
     * @param int $workspaceId
     * @param TopicStatus $status
     * @return Collection
     */
    public function getByStatus(int $workspaceId, TopicStatus $status): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->where('status', $status)
            ->with(['creator'])
            ->latest()
            ->get();
    }

    /**
     * Get approved topics ready to send.
     *
     * @param int $workspaceId
     * @return Collection
     */
    public function getApproved(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->approved()
            ->with(['creator'])
            ->latest()
            ->get();
    }

    /**
     * Get topics scheduled for sending.
     *
     * @return Collection
     */
    public function getScheduledForSending(): Collection
    {
        return $this->model
            ->scheduledForSending()
            ->with(['creator', 'workspace'])
            ->get();
    }

    /**
     * Get pending topics (sent to n8n or processing).
     *
     * @param int $workspaceId
     * @return Collection
     */
    public function getPending(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->pending()
            ->with(['creator'])
            ->latest('sent_at')
            ->get();
    }

    /**
     * Get failed topics.
     *
     * @param int $workspaceId
     * @return Collection
     */
    public function getFailed(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->failed()
            ->with(['creator'])
            ->latest()
            ->get();
    }

    /**
     * Get completed topics.
     *
     * @param int $workspaceId
     * @param int $limit
     * @return Collection
     */
    public function getCompleted(int $workspaceId, int $limit = 10): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->completed()
            ->with(['creator'])
            ->latest('completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get topics by niche.
     *
     * @param int $workspaceId
     * @param string $niche
     * @return Collection
     */
    public function getByNiche(int $workspaceId, string $niche): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->byNiche($niche)
            ->with(['creator'])
            ->latest()
            ->get();
    }

    /**
     * Get unique niches used in workspace.
     *
     * @param int $workspaceId
     * @return array
     */
    public function getNiches(int $workspaceId): array
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->distinct()
            ->pluck('niche')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Count topics by status for workspace.
     *
     * @param int $workspaceId
     * @return array
     */
    public function countByStatus(int $workspaceId): array
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Find topic by n8n execution ID.
     *
     * @param string $executionId
     * @return Topic|null
     */
    public function findByN8nExecutionId(string $executionId): ?Topic
    {
        return $this->model
            ->where('n8n_execution_id', $executionId)
            ->first();
    }
}
