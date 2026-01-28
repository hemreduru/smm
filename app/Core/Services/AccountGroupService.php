<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Repositories\AccountGroupRepository;
use App\Core\Results\BaseResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use App\Core\Results\SuccessResult;
use App\Models\AccountGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountGroupService
{
    public function __construct(
        private AccountGroupRepository $repository
    ) {}

    /**
     * Get all account groups for a workspace.
     */
    public function getGroupsForWorkspace(int $workspaceId): Collection
    {
        return $this->repository->getForWorkspace($workspaceId);
    }

    /**
     * Get active account groups for a workspace.
     */
    public function getActiveGroupsForWorkspace(int $workspaceId): Collection
    {
        return $this->repository->getActiveForWorkspace($workspaceId);
    }

    /**
     * Get groups count for a workspace.
     */
    public function getGroupsCount(int $workspaceId): int
    {
        return $this->repository->getCountForWorkspace($workspaceId);
    }

    /**
     * Create a new account group.
     */
    public function createGroup(int $workspaceId, array $data): BaseResult
    {
        try {
            DB::beginTransaction();

            $group = $this->repository->create([
                'workspace_id' => $workspaceId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Attach accounts if provided
            if (!empty($data['account_ids'])) {
                $this->repository->syncAccounts($group, $data['account_ids']);
            }

            $group->load('accounts');

            DB::commit();

            Log::info("CreateGroup: Created account group: {$group->name} in workspace: {$workspaceId}");

            return new SuccessResult(__('messages.group_created'), $group);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("CreateGroup: Failed to create group - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update an account group.
     */
    public function updateGroup(AccountGroup $group, array $data): BaseResult
    {
        try {
            DB::beginTransaction();

            $updateData = [];
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            if (isset($data['is_active'])) {
                $updateData['is_active'] = $data['is_active'];
            }

            if (!empty($updateData)) {
                $this->repository->update($group, $updateData);
            }

            // Sync accounts if provided
            if (isset($data['account_ids'])) {
                $this->repository->syncAccounts($group, $data['account_ids']);
            }

            $group->load('accounts');

            DB::commit();

            Log::info("UpdateGroup: Updated account group: {$group->id}");

            return new SuccessResult(__('messages.group_updated'), $group);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("UpdateGroup: Failed to update group - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Delete an account group.
     */
    public function deleteGroup(AccountGroup $group): BaseResult
    {
        try {
            $groupName = $group->name;

            DB::beginTransaction();

            $this->repository->delete($group);

            DB::commit();

            Log::info("DeleteGroup: Deleted account group: {$groupName}");

            return new SuccessResult(__('messages.group_deleted'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("DeleteGroup: Failed to delete group - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Add accounts to a group.
     */
    public function addAccountsToGroup(AccountGroup $group, array $accountIds): BaseResult
    {
        try {
            $this->repository->addAccounts($group, $accountIds);

            Log::info("AddAccountsToGroup: Added " . count($accountIds) . " accounts to group: {$group->id}");

            return new SuccessResult(__('messages.accounts_added_to_group'));
        } catch (\Throwable $e) {
            Log::error("AddAccountsToGroup: Failed to add accounts - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Remove accounts from a group.
     */
    public function removeAccountsFromGroup(AccountGroup $group, array $accountIds): BaseResult
    {
        try {
            $this->repository->removeAccounts($group, $accountIds);

            Log::info("RemoveAccountsFromGroup: Removed " . count($accountIds) . " accounts from group: {$group->id}");

            return new SuccessResult(__('messages.accounts_removed_from_group'));
        } catch (\Throwable $e) {
            Log::error("RemoveAccountsFromGroup: Failed to remove accounts - " . $e->getMessage());
            return new ServerErrorResult(__('messages.server_error'));
        }
    }
}
