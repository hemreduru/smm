<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Repositories\PlatformAccountRepository;
use App\Core\Results\BaseResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use App\Core\Results\SuccessResult;
use App\Enums\AccountStatus;
use App\Enums\Platform;
use App\Models\PlatformAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformAccountService
{
    public function __construct(
        private PlatformAccountRepository $repository
    ) {}

    /**
     * Get all platform accounts for a workspace.
     */
    public function getAccountsForWorkspace(int $workspaceId): Collection
    {
        return $this->repository->getForWorkspace($workspaceId);
    }

    /**
     * Get healthy accounts for a workspace.
     */
    public function getHealthyAccountsForWorkspace(int $workspaceId): Collection
    {
        return $this->repository->getHealthyForWorkspace($workspaceId);
    }

    /**
     * Get accounts by platform.
     */
    public function getAccountsByPlatform(int $workspaceId, Platform $platform): Collection
    {
        return $this->repository->getByPlatform($workspaceId, $platform);
    }

    /**
     * Get accounts count by platform.
     */
    public function getAccountCountByPlatform(int $workspaceId): array
    {
        return $this->repository->getCountByPlatform($workspaceId);
    }

    /**
     * Connect a new platform account.
     */
    public function connectAccount(int $workspaceId, array $data): BaseResult
    {
        try {
            $platform = Platform::from($data['platform']);

            // Check if account already exists
            if (
                isset($data['platform_user_id']) &&
                $this->repository->existsForWorkspace($workspaceId, $platform, $data['platform_user_id'])
            ) {
                return new FailResult(__('messages.account_already_connected'));
            }

            DB::beginTransaction();

            $account = $this->repository->create([
                'workspace_id' => $workspaceId,
                'platform' => $data['platform'],
                'platform_user_id' => $data['platform_user_id'] ?? null,
                'username' => $data['username'],
                'display_name' => $data['display_name'] ?? null,
                'profile_picture_url' => $data['profile_picture_url'] ?? null,
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'token_expires_at' => $data['token_expires_at'] ?? null,
                'status' => AccountStatus::ACTIVE->value,
                'metadata' => $data['metadata'] ?? null,
            ]);

            DB::commit();

            Log::info("ConnectAccount: User connected {$platform->label()} account: {$account->username} in workspace: {$workspaceId}");

            return new SuccessResult(__('messages.account_connected'), $account);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("ConnectAccount: Failed to connect account - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update a platform account.
     */
    public function updateAccount(PlatformAccount $account, array $data): BaseResult
    {
        try {
            DB::beginTransaction();

            $this->repository->update($account, $data);

            DB::commit();

            Log::info("UpdateAccount: Updated platform account: {$account->id}");

            return new SuccessResult(__('messages.account_updated'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("UpdateAccount: Failed to update account - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Disconnect (delete) a platform account.
     */
    public function disconnectAccount(PlatformAccount $account): BaseResult
    {
        try {
            $platform = $account->platform->label();
            $username = $account->username;

            DB::beginTransaction();

            $this->repository->delete($account);

            DB::commit();

            Log::info("DisconnectAccount: Disconnected {$platform} account: {$username}");

            return new SuccessResult(__('messages.account_disconnected'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("DisconnectAccount: Failed to disconnect account - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update account status.
     */
    public function updateStatus(PlatformAccount $account, AccountStatus $status): BaseResult
    {
        try {
            $this->repository->updateStatus($account, $status->value);

            Log::info("UpdateAccountStatus: Updated account {$account->id} status to {$status->value}");

            return new SuccessResult(__('messages.status_updated'));
        } catch (\Throwable $e) {
            Log::error("UpdateAccountStatus: Failed to update status - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Refresh account tokens.
     */
    public function refreshTokens(
        PlatformAccount $account,
        string $accessToken,
        ?string $refreshToken = null,
        ?\DateTimeInterface $expiresAt = null
    ): BaseResult {
        try {
            $this->repository->updateTokens($account, $accessToken, $refreshToken, $expiresAt);

            Log::info("RefreshTokens: Refreshed tokens for account {$account->id}");

            return new SuccessResult(__('messages.tokens_refreshed'));
        } catch (\Throwable $e) {
            Log::error("RefreshTokens: Failed to refresh tokens - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Get expiring accounts that need token refresh.
     */
    public function getExpiringAccounts(int $hoursBeforeExpiry = 24): Collection
    {
        return $this->repository->getExpiringAccounts($hoursBeforeExpiry);
    }
}
