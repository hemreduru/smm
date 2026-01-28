<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\Platform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlatformAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'platform',
        'platform_user_id',
        'username',
        'display_name',
        'profile_picture_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'platform' => Platform::class,
        'status' => AccountStatus::class,
        'metadata' => 'array',
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the workspace that owns this platform account.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the account groups this account belongs to.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(AccountGroup::class, 'account_group_members')
            ->withTimestamps();
    }

    /**
     * Check if the account token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if the account is healthy and can be used for publishing.
     */
    public function isHealthy(): bool
    {
        return $this->status->isHealthy() && !$this->isTokenExpired();
    }

    /**
     * Scope to filter by platform.
     */
    public function scopeForPlatform($query, Platform $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to filter by workspace.
     */
    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope to filter by active status.
     */
    public function scopeActive($query)
    {
        return $query->where('status', AccountStatus::ACTIVE);
    }

    /**
     * Scope to filter by healthy accounts.
     */
    public function scopeHealthy($query)
    {
        return $query->where('status', AccountStatus::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '>', now());
            });
    }
}
