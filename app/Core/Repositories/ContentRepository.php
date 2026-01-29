<?php

namespace App\Core\Repositories;

use App\Enums\ContentStatus;
use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContentRepository extends BaseRepository
{
    public function __construct(Content $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all contents for a workspace
     */
    public function getForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['accountGroup', 'creator'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get paginated contents for a workspace
     */
    public function getPaginatedForWorkspace(int $workspaceId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['accountGroup', 'creator'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get contents by status for a workspace
     */
    public function getByStatus(int $workspaceId, ContentStatus $status): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->withStatus($status)
            ->with(['accountGroup', 'creator'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get contents ready to publish
     */
    public function getReadyToPublish(): Collection
    {
        return $this->model
            ->readyToPublish()
            ->with(['accountGroup.accounts', 'workspace'])
            ->get();
    }

    /**
     * Get content counts by status for a workspace
     */
    public function getStatusCounts(int $workspaceId): array
    {
        $counts = $this->model
            ->forWorkspace($workspaceId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses have a count
        $result = [];
        foreach (ContentStatus::cases() as $status) {
            $result[$status->value] = $counts[$status->value] ?? 0;
        }

        return $result;
    }

    /**
     * Get scheduled contents for a workspace (ordered by schedule time)
     */
    public function getScheduledForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->scheduled()
            ->with(['accountGroup', 'creator'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get recent contents for a workspace
     */
    public function getRecentForWorkspace(int $workspaceId, int $limit = 10): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['accountGroup', 'creator'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Update content status
     */
    public function updateStatus(int $id, ContentStatus $status): bool
    {
        $content = $this->find($id);
        if (!$content) {
            return false;
        }

        $updateData = ['status' => $status];

        // Set published_at when transitioning to published
        if ($status === ContentStatus::PUBLISHED) {
            $updateData['published_at'] = now();
        }

        return $content->update($updateData);
    }

    /**
     * Find content with workspace check
     */
    public function findForWorkspace(int $id, int $workspaceId): ?Content
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['accountGroup', 'creator'])
            ->find($id);
    }

    /**
     * Search contents
     */
    public function search(int $workspaceId, string $query): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('caption_tr', 'like', "%{$query}%")
                    ->orWhere('caption_en', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
            ->with(['accountGroup', 'creator'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get contents for DataTable
     */
    public function getDataTableQuery(int $workspaceId)
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with(['accountGroup', 'creator'])
            ->orderByDesc('created_at')
            ->select('contents.*');
    }
}
