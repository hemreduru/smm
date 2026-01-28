<?php

declare(strict_types=1);

namespace App\Core\Repositories;

use App\Enums\Platform;
use App\Models\PlatformAccount;
use Illuminate\Database\Eloquent\Collection;

class PlatformAccountRepository
{
    public function __construct(
        private PlatformAccount $model
    ) {}

    /**
     * Get all platform accounts for a workspace.
     */
    public function getForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->orderBy('platform')
            ->orderBy('username')
            ->get();
    }

    /**
     * Get platform accounts by platform type for a workspace.
     */
    public function getByPlatform(int $workspaceId, Platform $platform): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->forPlatform($platform)
            ->orderBy('username')
            ->get();
    }

    /**
     * Get only healthy (active and non-expired) accounts for a workspace.
     */
    public function getHealthyForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->healthy()
            ->orderBy('platform')
            ->orderBy('username')
            ->get();
    }

    /**
     * Find a platform account by ID.
     */
    public function find(int $id): ?PlatformAccount
    {
        return $this->model->find($id);
    }

    /**
     * Find a platform account by workspace ID and ID.
     */
    public function findForWorkspace(int $workspaceId, int $id): ?PlatformAccount
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->find($id);
    }

    /**
     * Create a new platform account.
     */
    public function create(array $data): PlatformAccount
    {
        return $this->model->create($data);
    }

    /**
     * Update a platform account.
     */
    public function update(PlatformAccount $account, array $data): bool
    {
        return $account->update($data);
    }

    /**
     * Delete a platform account.
     */
    public function delete(PlatformAccount $account): bool
    {
        return $account->delete();
    }

    /**
     * Update account status.
     */
    public function updateStatus(PlatformAccount $account, string $status): bool
    {
        return $account->update(['status' => $status]);
    }

    /**
     * Update account tokens.
     */
    public function updateTokens(
        PlatformAccount $account,
        string $accessToken,
        ?string $refreshToken = null,
        ?\DateTimeInterface $expiresAt = null
    ): bool {
        $data = [
            'access_token' => $accessToken,
            'status' => 'active',
        ];

        if ($refreshToken) {
            $data['refresh_token'] = $refreshToken;
        }

        if ($expiresAt) {
            $data['token_expires_at'] = $expiresAt;
        }

        return $account->update($data);
    }

    /**
     * Check if a platform account already exists for this workspace.
     */
    public function existsForWorkspace(
        int $workspaceId,
        Platform $platform,
        string $platformUserId
    ): bool {
        return $this->model
            ->forWorkspace($workspaceId)
            ->forPlatform($platform)
            ->where('platform_user_id', $platformUserId)
            ->exists();
    }

    /**
     * Get accounts needing token refresh.
     */
    public function getExpiringAccounts(int $hoursBeforeExpiry = 24): Collection
    {
        return $this->model
            ->active()
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addHours($hoursBeforeExpiry))
            ->get();
    }

    /**
     * Get accounts count by platform for a workspace.
     */
    public function getCountByPlatform(int $workspaceId): array
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();
    }
}
