<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Repositories\WorkspaceRepository;
use App\Core\Repositories\UserRepository;
use App\Core\Results\BaseResult;
use App\Core\Results\SuccessResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use App\Models\Role;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            // Create workspace via repository
            $workspace = $this->workspaceRepository->createWorkspace($data['name'], $ownerId);

            // Create default roles via repository
            $roles = $this->workspaceRepository->createDefaultRoles($workspace->id);

            // Add owner as admin
            $this->workspaceRepository->addUser($workspace->id, $ownerId, $roles['admin']->id);

            DB::commit();

            Log::info("[WorkspaceModule] Created - User: {$ownerId} - Workspace: {$workspace->id}");

            return new SuccessResult(
                __('messages.workspace_created'),
                ['workspace' => $workspace],
                route('workspaces.show', $workspace->id)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[WorkspaceModule] Create failed - User: {$ownerId} - Error: {$e->getMessage()}");

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

            Log::info("[WorkspaceModule] Updated - User: {$userId} - Workspace: {$workspaceId}");

            return new SuccessResult(__('messages.workspace_updated'), ['workspace' => $workspace]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[WorkspaceModule] Update failed - User: {$userId} - Workspace: {$workspaceId} - Error: {$e->getMessage()}");

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

            Log::info("[WorkspaceModule] Deleted - User: {$userId} - Workspace: {$workspaceId}");

            return new SuccessResult(__('messages.workspace_deleted'), null, route('dashboard'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[WorkspaceModule] Delete failed - User: {$userId} - Workspace: {$workspaceId} - Error: {$e->getMessage()}");

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

            Log::info("[WorkspaceModule] Switched - User: {$userId} - Workspace: {$workspaceId}");

            return new SuccessResult(__('messages.workspace_switched'));
        } catch (\Exception $e) {
            Log::error("[WorkspaceModule] Switch failed - User: {$userId} - Workspace: {$workspaceId} - Error: {$e->getMessage()}");

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

            Log::info("[WorkspaceModule] Invited - Inviter: {$inviterId} - Invitee: {$user->id} - Workspace: {$workspaceId}");

            return new SuccessResult(__('messages.user_invited'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[WorkspaceModule] Invite failed - Inviter: {$inviterId} - Workspace: {$workspaceId} - Error: {$e->getMessage()}");

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

            Log::info("[WorkspaceModule] Removed - Remover: {$removerId} - Removed: {$userId} - Workspace: {$workspaceId}");

            return new SuccessResult(__('messages.user_removed'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[WorkspaceModule] Remove failed - Remover: {$removerId} - Workspace: {$workspaceId} - Error: {$e->getMessage()}");

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

            Log::info("[WorkspaceModule] Role updated - Updater: {$updaterId} - User: {$userId} - Workspace: {$workspaceId}");

            return new SuccessResult(__('messages.role_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[WorkspaceModule] Role update failed - Updater: {$updaterId} - Workspace: {$workspaceId} - Error: {$e->getMessage()}");

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
