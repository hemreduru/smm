<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccountGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the workspace that owns this account group.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the platform accounts in this group.
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(PlatformAccount::class, 'account_group_members')
            ->withTimestamps();
    }

    /**
     * Get only healthy accounts in this group.
     */
    public function healthyAccounts(): BelongsToMany
    {
        return $this->accounts()->healthy();
    }

    /**
     * Get the count of accounts per platform in this group.
     */
    public function getAccountCountByPlatformAttribute(): array
    {
        return $this->accounts()
            ->get()
            ->groupBy('platform')
            ->map->count()
            ->toArray();
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope to filter active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
