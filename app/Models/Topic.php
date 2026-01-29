<?php

namespace App\Models;

use App\Enums\AIProvider;
use App\Enums\TopicStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'created_by',
        'title',
        'description',
        'niche',
        'keywords',
        'ai_provider',
        'ai_model',
        'ai_prompt',
        'ai_response',
        'status',
        'n8n_execution_id',
        'sent_at',
        'completed_at',
        'error_message',
        'error_details',
        'is_scheduled',
        'scheduled_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'error_details' => 'array',
        'status' => TopicStatus::class,
        'ai_provider' => AIProvider::class,
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_scheduled' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Status Methods
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === TopicStatus::DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === TopicStatus::APPROVED;
    }

    public function isSentToN8n(): bool
    {
        return $this->status === TopicStatus::SENT_TO_N8N;
    }

    public function isProcessing(): bool
    {
        return $this->status === TopicStatus::PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === TopicStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === TopicStatus::FAILED;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, TopicStatus::editableStatuses());
    }

    public function canBeSentToN8n(): bool
    {
        return in_array($this->status, TopicStatus::sendableStatuses());
    }

    /*
    |--------------------------------------------------------------------------
    | Status Transitions
    |--------------------------------------------------------------------------
    */

    public function approve(): bool
    {
        if (!$this->status->canTransitionTo(TopicStatus::APPROVED)) {
            return false;
        }

        $this->update(['status' => TopicStatus::APPROVED]);
        return true;
    }

    public function markAsSent(string $n8nExecutionId): bool
    {
        if (!$this->status->canTransitionTo(TopicStatus::SENT_TO_N8N)) {
            return false;
        }

        $this->update([
            'status' => TopicStatus::SENT_TO_N8N,
            'n8n_execution_id' => $n8nExecutionId,
            'sent_at' => now(),
            'error_message' => null,
            'error_details' => null,
        ]);
        return true;
    }

    public function markAsProcessing(): bool
    {
        if (!$this->status->canTransitionTo(TopicStatus::PROCESSING)) {
            return false;
        }

        $this->update(['status' => TopicStatus::PROCESSING]);
        return true;
    }

    public function markAsCompleted(): bool
    {
        if (!$this->status->canTransitionTo(TopicStatus::COMPLETED)) {
            return false;
        }

        $this->update([
            'status' => TopicStatus::COMPLETED,
            'completed_at' => now(),
        ]);
        return true;
    }

    public function markAsFailed(string $message, ?array $details = null): bool
    {
        if (!$this->status->canTransitionTo(TopicStatus::FAILED)) {
            return false;
        }

        $this->update([
            'status' => TopicStatus::FAILED,
            'error_message' => $message,
            'error_details' => $details,
        ]);
        return true;
    }

    public function resetToDraft(): bool
    {
        if (!$this->status->canTransitionTo(TopicStatus::DRAFT)) {
            return false;
        }

        $this->update([
            'status' => TopicStatus::DRAFT,
            'n8n_execution_id' => null,
            'sent_at' => null,
            'completed_at' => null,
            'error_message' => null,
            'error_details' => null,
        ]);
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', TopicStatus::DRAFT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', TopicStatus::APPROVED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [TopicStatus::SENT_TO_N8N, TopicStatus::PROCESSING]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TopicStatus::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', TopicStatus::FAILED);
    }

    public function scopeScheduledForSending($query)
    {
        return $query->where('is_scheduled', true)
            ->where('status', TopicStatus::APPROVED)
            ->where('scheduled_at', '<=', now());
    }

    public function scopeByNiche($query, string $niche)
    {
        return $query->where('niche', $niche);
    }
}
