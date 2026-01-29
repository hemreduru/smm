<?php

declare(strict_types=1);

namespace App\Core\Repositories;

use App\Models\Role;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class WorkspaceRepository extends BaseRepository
{
    public function __construct(Workspace $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a new workspace.
     */
    public function createWorkspace(string $name, int $ownerId): Workspace
    {
        return Workspace::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'owner_id' => $ownerId,
        ]);
    }

    /**
     * Create a role for workspace.
     */
    public function createRole(int $workspaceId, string $name, string $slug): Role
    {
        return Role::create([
            'workspace_id' => $workspaceId,
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    /**
     * Create default roles for workspace.
     * 
     * @return array{admin: Role, editor: Role, viewer: Role}
     */
    public function createDefaultRoles(int $workspaceId): array
    {
        return [
            'admin' => $this->createRole($workspaceId, 'Admin', 'admin'),
            'editor' => $this->createRole($workspaceId, 'Editor', 'editor'),
            'viewer' => $this->createRole($workspaceId, 'Viewer', 'viewer'),
        ];
    }

    /**
     * Get role by workspace and slug.
     */
    public function getRoleBySlug(int $workspaceId, string $slug): ?Role
    {
        return Role::where('workspace_id', $workspaceId)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get all workspaces for a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Workspace::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    /**
     * Get workspace with users and roles.
     */
    public function getWithUsers(int $workspaceId): ?Workspace
    {
        return Workspace::with(['users', 'roles', 'owner'])
            ->find($workspaceId);
    }

    /**
     * Check if user is member of workspace.
     */
    public function userIsMember(int $workspaceId, int $userId): bool
    {
        return Workspace::where('id', $workspaceId)
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }

    /**
     * Check if user is owner of workspace.
     */
    public function userIsOwner(int $workspaceId, int $userId): bool
    {
        return Workspace::where('id', $workspaceId)
            ->where('owner_id', $userId)
            ->exists();
    }

    /**
     * Add user to workspace with role.
     */
    public function addUser(int $workspaceId, int $userId, ?int $roleId = null): void
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $workspace->users()->attach($userId, ['role_id' => $roleId]);
    }

    /**
     * Remove user from workspace.
     */
    public function removeUser(int $workspaceId, int $userId): void
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $workspace->users()->detach($userId);
    }

    /**
     * Update user's role in workspace.
     */
    public function updateUserRole(int $workspaceId, int $userId, int $roleId): void
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $workspace->users()->updateExistingPivot($userId, ['role_id' => $roleId]);
    }
}
