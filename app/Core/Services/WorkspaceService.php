<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Repositories\WorkspaceRepository;
use App\Core\Repositories\UserRepository;
use App\Core\Results\BaseResult;
use App\Core\Results\SuccessResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use App\Models\Workspace;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WorkspaceService extends BaseService
{
    public function __construct(
        protected WorkspaceRepository $workspaceRepository,
        protected UserRepository $userRepository
    ) {}

    /**
     * Get all workspaces for authenticated user.
     */
    public function getUserWorkspaces(int $userId): array
    {
        return $this->workspaceRepository->getByUserId($userId)->toArray();
    }

    /**
     * Get workspace with details.
     */
    public function getWorkspace(int $workspaceId): ?Workspace
    {
        return $this->workspaceRepository->getWithUsers($workspaceId);
    }

    /**
     * Create a new workspace.
     */
    public function create(array $data, int $ownerId): BaseResult
    {
        DB::beginTransaction();

        try {
            $workspace = Workspace::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . uniqid(),
                'owner_id' => $ownerId,
            ]);

            // Create default Admin role for workspace
            $adminRole = Role::create([
                'workspace_id' => $workspace->id,
                'name' => 'Admin',
                'slug' => 'admin',
            ]);

            // Create Editor and Viewer roles
            Role::create([
                'workspace_id' => $workspace->id,
                'name' => 'Editor',
                'slug' => 'editor',
            ]);

            Role::create([
                'workspace_id' => $workspace->id,
                'name' => 'Viewer',
                'slug' => 'viewer',
            ]);

            // Add owner as admin
            $this->workspaceRepository->addUser($workspace->id, $ownerId, $adminRole->id);

            DB::commit();

            Log::info("WorkspaceService: Create - User: {$ownerId} created workspace: {$workspace->id}");

            return new SuccessResult(
                __('messages.workspace_created'),
                ['workspace' => $workspace],
                route('workspaces.show', $workspace->id)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("WorkspaceService: Create failed - User: {$ownerId} - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update workspace.
     */
    public function update(int $workspaceId, array $data, int $userId): BaseResult
    {
        DB::beginTransaction();

        try {
            $workspace = Workspace::findOrFail($workspaceId);

            // Only owner can update
            if ($workspace->owner_id !== $userId) {
                return new FailResult(__('messages.unauthorized'));
            }

            $workspace->update([
                'name' => $data['name'],
            ]);

            DB::commit();

            Log::info("WorkspaceService: Update - User: {$userId} updated workspace: {$workspaceId}");

            return new SuccessResult(__('messages.workspace_updated'), ['workspace' => $workspace]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("WorkspaceService: Update failed - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Delete workspace.
     */
    public function delete(int $workspaceId, int $userId): BaseResult
    {
        DB::beginTransaction();

        try {
            $workspace = Workspace::findOrFail($workspaceId);

            if ($workspace->owner_id !== $userId) {
                return new FailResult(__('messages.unauthorized'));
            }

            $workspace->delete();

            DB::commit();

            Log::info("WorkspaceService: Delete - User: {$userId} deleted workspace: {$workspaceId}");

            return new SuccessResult(__('messages.workspace_deleted'), null, route('dashboard'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("WorkspaceService: Delete failed - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Switch current workspace for user.
     */
    public function switchWorkspace(int $userId, int $workspaceId): BaseResult
    {
        try {
            // Check if user is member
            if (!$this->workspaceRepository->userIsMember($workspaceId, $userId)) {
                return new FailResult(__('messages.not_workspace_member'));
            }

            $this->userRepository->switchWorkspace($userId, $workspaceId);

            Log::info("WorkspaceService: Switch - User: {$userId} switched to workspace: {$workspaceId}");

            return new SuccessResult(__('messages.workspace_switched'));
        } catch (\Exception $e) {
            Log::error("WorkspaceService: Switch failed - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Invite user to workspace.
     */
    public function inviteUser(int $workspaceId, string $email, int $roleId, int $inviterId): BaseResult
    {
        DB::beginTransaction();

        try {
            // Check if inviter has permission
            if (!$this->workspaceRepository->userIsOwner($workspaceId, $inviterId)) {
                $inviterRole = $this->getUserRoleInWorkspace($inviterId, $workspaceId);
                if (!$inviterRole || $inviterRole->slug !== 'admin') {
                    return new FailResult(__('messages.unauthorized'));
                }
            }

            // Find user by email
            $user = $this->userRepository->findByEmail($email);
            if (!$user) {
                return new FailResult(__('messages.user_not_found'));
            }

            // Check if already member
            if ($this->workspaceRepository->userIsMember($workspaceId, $user->id)) {
                return new FailResult(__('messages.user_already_member'));
            }

            // Add to workspace
            $this->workspaceRepository->addUser($workspaceId, $user->id, $roleId);

            DB::commit();

            Log::info("WorkspaceService: Invite - User: {$inviterId} invited user: {$user->id} to workspace: {$workspaceId}");

            return new SuccessResult(__('messages.user_invited'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("WorkspaceService: Invite failed - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Remove user from workspace.
     */
    public function removeUser(int $workspaceId, int $userId, int $removerId): BaseResult
    {
        DB::beginTransaction();

        try {
            $workspace = Workspace::findOrFail($workspaceId);

            // Cannot remove owner
            if ($workspace->owner_id === $userId) {
                return new FailResult(__('messages.cannot_remove_owner'));
            }

            // Check if remover has permission
            if ($workspace->owner_id !== $removerId) {
                $removerRole = $this->getUserRoleInWorkspace($removerId, $workspaceId);
                if (!$removerRole || $removerRole->slug !== 'admin') {
                    return new FailResult(__('messages.unauthorized'));
                }
            }

            $this->workspaceRepository->removeUser($workspaceId, $userId);

            // If removed user's current workspace is this one, clear it
            $this->userRepository->switchWorkspace($userId, null);

            DB::commit();

            Log::info("WorkspaceService: Remove - User: {$removerId} removed user: {$userId} from workspace: {$workspaceId}");

            return new SuccessResult(__('messages.user_removed'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("WorkspaceService: Remove failed - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Update user role in workspace.
     */
    public function updateUserRole(int $workspaceId, int $userId, int $roleId, int $updaterId): BaseResult
    {
        DB::beginTransaction();

        try {
            $workspace = Workspace::findOrFail($workspaceId);

            // Cannot change owner's role
            if ($workspace->owner_id === $userId) {
                return new FailResult(__('messages.cannot_change_owner_role'));
            }

            // Check permission
            if ($workspace->owner_id !== $updaterId) {
                $updaterRole = $this->getUserRoleInWorkspace($updaterId, $workspaceId);
                if (!$updaterRole || $updaterRole->slug !== 'admin') {
                    return new FailResult(__('messages.unauthorized'));
                }
            }

            $this->workspaceRepository->updateUserRole($workspaceId, $userId, $roleId);

            DB::commit();

            Log::info("WorkspaceService: UpdateRole - User: {$updaterId} updated role of user: {$userId} in workspace: {$workspaceId}");

            return new SuccessResult(__('messages.role_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("WorkspaceService: UpdateRole failed - {$e->getMessage()}");

            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Get user's role in workspace.
     */
    protected function getUserRoleInWorkspace(int $userId, int $workspaceId): ?Role
    {
        $pivot = DB::table('workspace_user')
            ->where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->first();

        if (!$pivot || !$pivot->role_id) {
            return null;
        }

        return Role::find($pivot->role_id);
    }
}
