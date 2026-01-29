<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Content extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'account_group_id',
        'created_by',
        'video_path',
        'thumbnail_path',
        'original_filename',
        'file_size',
        'duration',
        'caption_tr',
        'caption_en',
        'hashtags',
        'status',
        'scheduled_at',
        'published_at',
        'title',
        'notes',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'status' => ContentStatus::class,
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'file_size' => 'integer',
        'duration' => 'integer',
    ];

    // ─── Relationships ─────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ────────────────────────────────────────────────────────

    public function scopeForWorkspace(Builder $query, int $workspaceId): Builder
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeWithStatus(Builder $query, ContentStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::DRAFT);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::APPROVED);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::SCHEDULED);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::FAILED);
    }

    public function scopeReadyToPublish(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    // ─── Accessors ─────────────────────────────────────────────────────

    /**
     * Get caption based on current locale
     */
    public function getCaptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'tr' ? $this->caption_tr : $this->caption_en;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get formatted duration (MM:SS)
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '-';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get hashtags as string
     */
    public function getHashtagsStringAttribute(): string
    {
        if (!$this->hashtags || empty($this->hashtags)) {
            return '';
        }

        return implode(' ', array_map(fn($tag) => '#' . ltrim($tag, '#'), $this->hashtags));
    }

    // ─── Methods ───────────────────────────────────────────────────────

    /**
     * Check if content can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ContentStatus::editableStatuses());
    }

    /**
     * Check if content can be deleted
     */
    public function isDeletable(): bool
    {
        return in_array($this->status, ContentStatus::deletableStatuses());
    }

    /**
     * Check if content can transition to a new status
     */
    public function canTransitionTo(ContentStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    /**
     * Get video URL for display
     */
    public function getVideoUrl(): ?string
    {
        if (!$this->video_path) {
            return null;
        }

        return asset('storage/' . $this->video_path);
    }

    /**
     * Get thumbnail URL for display
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return asset('storage/' . $this->thumbnail_path);
    }
}
