<?php

declare(strict_types=1);

namespace App\Core\Repositories;

use App\Models\AccountGroup;
use Illuminate\Database\Eloquent\Collection;

class AccountGroupRepository
{
    public function __construct(
        private AccountGroup $model
    ) {}

    /**
     * Get all account groups for a workspace.
     */
    public function getForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with('accounts')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active account groups for a workspace.
     */
    public function getActiveForWorkspace(int $workspaceId): Collection
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->active()
            ->with('accounts')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find an account group by ID.
     */
    public function find(int $id): ?AccountGroup
    {
        return $this->model->with('accounts')->find($id);
    }

    /**
     * Find an account group by workspace ID and ID.
     */
    public function findForWorkspace(int $workspaceId, int $id): ?AccountGroup
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->with('accounts')
            ->find($id);
    }

    /**
     * Create a new account group.
     */
    public function create(array $data): AccountGroup
    {
        return $this->model->create($data);
    }

    /**
     * Update an account group.
     */
    public function update(AccountGroup $group, array $data): bool
    {
        return $group->update($data);
    }

    /**
     * Delete an account group.
     */
    public function delete(AccountGroup $group): bool
    {
        return $group->delete();
    }

    /**
     * Sync accounts to a group.
     */
    public function syncAccounts(AccountGroup $group, array $accountIds): void
    {
        $group->accounts()->sync($accountIds);
    }

    /**
     * Add accounts to a group.
     */
    public function addAccounts(AccountGroup $group, array $accountIds): void
    {
        $group->accounts()->attach($accountIds);
    }

    /**
     * Remove accounts from a group.
     */
    public function removeAccounts(AccountGroup $group, array $accountIds): void
    {
        $group->accounts()->detach($accountIds);
    }

    /**
     * Get groups count for a workspace.
     */
    public function getCountForWorkspace(int $workspaceId): int
    {
        return $this->model
            ->forWorkspace($workspaceId)
            ->count();
    }
}
