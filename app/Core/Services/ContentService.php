<?php

namespace App\Core\Services;

use App\Core\Repositories\ContentRepository;
use App\Core\Results\BaseResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use App\Core\Results\SuccessResult;
use App\Enums\ContentStatus;
use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentService extends BaseService
{
    public function __construct(
        protected ContentRepository $contentRepository
    ) {}

    /**
     * Get all contents for workspace
     */
    public function getForWorkspace(int $workspaceId): Collection
    {
        return $this->contentRepository->getForWorkspace($workspaceId);
    }

    /**
     * Get content by ID with workspace check
     */
    public function findForWorkspace(int $id, int $workspaceId): ?Content
    {
        return $this->contentRepository->findForWorkspace($id, $workspaceId);
    }

    /**
     * Get status counts for dashboard
     */
    public function getStatusCounts(int $workspaceId): array
    {
        return $this->contentRepository->getStatusCounts($workspaceId);
    }

    /**
     * Get scheduled contents
     */
    public function getScheduledForWorkspace(int $workspaceId): Collection
    {
        return $this->contentRepository->getScheduledForWorkspace($workspaceId);
    }

    /**
     * Create new content
     */
    public function create(array $data, UploadedFile $video, int $workspaceId, int $userId): BaseResult
    {
        DB::beginTransaction();
        try {
            // Upload video
            $videoPath = $this->uploadVideo($video, $workspaceId);
            if (!$videoPath) {
                DB::rollBack();
                return new FailResult(__('messages.video_upload_failed'));
            }

            // Prepare content data
            $contentData = [
                'workspace_id' => $workspaceId,
                'created_by' => $userId,
                'video_path' => $videoPath,
                'original_filename' => $video->getClientOriginalName(),
                'file_size' => $video->getSize(),
                'title' => $data['title'] ?? null,
                'caption_tr' => $data['caption_tr'] ?? null,
                'caption_en' => $data['caption_en'] ?? null,
                'hashtags' => $this->parseHashtags($data['hashtags'] ?? ''),
                'account_group_id' => $data['account_group_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => ContentStatus::DRAFT,
            ];

            // Handle scheduling
            if (!empty($data['scheduled_at'])) {
                $contentData['scheduled_at'] = $data['scheduled_at'];
                $contentData['status'] = ContentStatus::SCHEDULED;
            }

            $content = $this->contentRepository->create($contentData);

            Log::info("[ContentModule] Content created - User: {$userId} - Workspace: {$workspaceId} - Content: {$content->id}");

            DB::commit();
            return new SuccessResult(
                __('messages.content_created'),
                ['content' => $content],
                route('contents.show', $content)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[ContentModule] Content creation failed - User: {$userId} - Error: {$e->getMessage()}");
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update content
     */
    public function update(int $id, array $data, int $workspaceId, int $userId): BaseResult
    {
        DB::beginTransaction();
        try {
            $content = $this->contentRepository->findForWorkspace($id, $workspaceId);

            if (!$content) {
                DB::rollBack();
                return new FailResult(__('messages.content_not_found'), null, null, 404);
            }

            if (!$content->isEditable()) {
                DB::rollBack();
                return new FailResult(__('messages.content_not_editable'));
            }

            $updateData = [
                'title' => $data['title'] ?? $content->title,
                'caption_tr' => $data['caption_tr'] ?? $content->caption_tr,
                'caption_en' => $data['caption_en'] ?? $content->caption_en,
                'hashtags' => isset($data['hashtags']) ? $this->parseHashtags($data['hashtags']) : $content->hashtags,
                'account_group_id' => $data['account_group_id'] ?? $content->account_group_id,
                'notes' => $data['notes'] ?? $content->notes,
            ];

            // Handle scheduling update
            if (isset($data['scheduled_at'])) {
                $updateData['scheduled_at'] = $data['scheduled_at'] ?: null;
            }

            $this->contentRepository->update($id, $updateData);

            Log::info("[ContentModule] Content updated - User: {$userId} - Content: {$id}");

            DB::commit();
            return new SuccessResult(
                __('messages.content_updated'),
                ['content' => $content->fresh()],
                route('contents.show', $content)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[ContentModule] Content update failed - Content: {$id} - Error: {$e->getMessage()}");
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Delete content
     */
    public function delete(int $id, int $workspaceId, int $userId): BaseResult
    {
        DB::beginTransaction();
        try {
            $content = $this->contentRepository->findForWorkspace($id, $workspaceId);

            if (!$content) {
                DB::rollBack();
                return new FailResult(__('messages.content_not_found'), null, null, 404);
            }

            if (!$content->isDeletable()) {
                DB::rollBack();
                return new FailResult(__('messages.content_not_deletable'));
            }

            // Delete video file
            if ($content->video_path) {
                Storage::disk('public')->delete($content->video_path);
            }
            if ($content->thumbnail_path) {
                Storage::disk('public')->delete($content->thumbnail_path);
            }

            $this->contentRepository->delete($id);

            Log::info("[ContentModule] Content deleted - User: {$userId} - Content: {$id}");

            DB::commit();
            return new SuccessResult(
                __('messages.content_deleted'),
                null,
                route('contents.index')
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[ContentModule] Content deletion failed - Content: {$id} - Error: {$e->getMessage()}");
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update content status
     */
    public function updateStatus(int $id, ContentStatus $newStatus, int $workspaceId, int $userId): BaseResult
    {
        DB::beginTransaction();
        try {
            $content = $this->contentRepository->findForWorkspace($id, $workspaceId);

            if (!$content) {
                DB::rollBack();
                return new FailResult(__('messages.content_not_found'), null, null, 404);
            }

            if (!$content->canTransitionTo($newStatus)) {
                DB::rollBack();
                return new FailResult(__('messages.invalid_status_transition'));
            }

            $this->contentRepository->updateStatus($id, $newStatus);

            Log::info("[ContentModule] Content status updated - User: {$userId} - Content: {$id} - Status: {$newStatus->value}");

            DB::commit();
            return new SuccessResult(
                __('messages.status_updated'),
                ['content' => $content->fresh()]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[ContentModule] Status update failed - Content: {$id} - Error: {$e->getMessage()}");
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Approve content
     */
    public function approve(int $id, int $workspaceId, int $userId): BaseResult
    {
        return $this->updateStatus($id, ContentStatus::APPROVED, $workspaceId, $userId);
    }

    /**
     * Schedule content for publishing
     */
    public function schedule(int $id, string $scheduledAt, int $workspaceId, int $userId): BaseResult
    {
        DB::beginTransaction();
        try {
            $content = $this->contentRepository->findForWorkspace($id, $workspaceId);

            if (!$content) {
                DB::rollBack();
                return new FailResult(__('messages.content_not_found'), null, null, 404);
            }

            if (!$content->canTransitionTo(ContentStatus::SCHEDULED)) {
                DB::rollBack();
                return new FailResult(__('messages.invalid_status_transition'));
            }

            $this->contentRepository->update($id, [
                'status' => ContentStatus::SCHEDULED,
                'scheduled_at' => $scheduledAt,
            ]);

            Log::info("[ContentModule] Content scheduled - User: {$userId} - Content: {$id} - Scheduled: {$scheduledAt}");

            DB::commit();
            return new SuccessResult(
                __('messages.content_scheduled'),
                ['content' => $content->fresh()]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[ContentModule] Scheduling failed - Content: {$id} - Error: {$e->getMessage()}");
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Upload video file
     */
    protected function uploadVideo(UploadedFile $video, int $workspaceId): ?string
    {
        try {
            $filename = Str::uuid() . '.' . $video->getClientOriginalExtension();
            $path = "contents/{$workspaceId}/{$filename}";

            Storage::disk('public')->putFileAs(
                "contents/{$workspaceId}",
                $video,
                $filename
            );

            return $path;
        } catch (\Exception $e) {
            Log::error("[ContentModule] Video upload failed - Error: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Parse hashtags from string
     */
    protected function parseHashtags(string $hashtagsString): array
    {
        if (empty($hashtagsString)) {
            return [];
        }

        // Split by space, comma, or newline
        $tags = preg_split('/[\s,]+/', $hashtagsString);

        // Clean up: remove # prefix, empty values, duplicates
        $tags = array_map(fn($tag) => ltrim(trim($tag), '#'), $tags);
        $tags = array_filter($tags, fn($tag) => !empty($tag));
        $tags = array_unique($tags);

        return array_values($tags);
    }

    /**
     * Get DataTable query
     */
    public function getDataTableQuery(int $workspaceId)
    {
        return $this->contentRepository->getDataTableQuery($workspaceId);
    }
}
