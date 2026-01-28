<?php

declare(strict_types=1);

namespace App\Core\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get users by workspace ID.
     */
    public function getByWorkspaceId(int $workspaceId): Collection
    {
        return User::whereHas('workspaces', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->with(['workspaces' => function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        }])->get();
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get user with workspace role.
     */
    public function getWithWorkspaceRole(int $userId, int $workspaceId): ?User
    {
        return User::with(['workspaces' => function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId)->with('roles');
        }])->find($userId);
    }

    /**
     * Switch user's current workspace.
     */
    public function switchWorkspace(int $userId, ?int $workspaceId): bool
    {
        return User::where('id', $userId)
            ->update(['current_workspace_id' => $workspaceId]) > 0;
    }

    /**
     * Get users not in workspace (for invite).
     */
    public function getUsersNotInWorkspace(int $workspaceId): Collection
    {
        return User::whereDoesntHave('workspaces', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->get();
    }
}
