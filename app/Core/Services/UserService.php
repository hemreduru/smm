<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Repositories\UserRepository;
use App\Core\Results\BaseResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use App\Core\Results\SuccessResult;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Get users for a workspace (for DataTables).
     */
    public function getUsersQueryForWorkspace(int $workspaceId): Builder
    {
        return User::select(['users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.created_at'])
            ->join('workspace_user', 'users.id', '=', 'workspace_user.user_id')
            ->where('workspace_user.workspace_id', $workspaceId)
            ->with(['workspaces' => function ($query) use ($workspaceId) {
                $query->where('workspaces.id', $workspaceId);
            }]);
    }

    /**
     * Get users by workspace ID.
     */
    public function getUsersByWorkspace(int $workspaceId): Collection
    {
        return $this->userRepository->getByWorkspaceId($workspaceId);
    }

    /**
     * Get roles for a workspace.
     */
    public function getRolesForWorkspace(int $workspaceId): Collection
    {
        return Role::where('workspace_id', $workspaceId)->get();
    }

    /**
     * Get user's role name in a workspace.
     */
    public function getUserRoleInWorkspace(User $user, int $workspaceId): ?string
    {
        $pivot = $user->workspaces->first()?->pivot;
        
        if ($pivot && $pivot->role_id) {
            $role = Role::find($pivot->role_id);
            return $role?->name;
        }
        
        return null;
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): BaseResult
    {
        try {
            DB::beginTransaction();

            $this->userRepository->update($user, $data);

            DB::commit();

            Log::info("[UserModule] Profile updated - User: {$user->id}");

            return new SuccessResult(
                __('messages.profile_updated'),
                ['user' => $user->fresh()]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("[UserModule] Profile update failed - User: {$user->id} - Error: {$e->getMessage()}");
            return new ServerErrorResult(__('messages.server_error'));
        }
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Check if user is workspace owner.
     */
    public function isWorkspaceOwner(User $user, int $workspaceId): bool
    {
        $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
        
        return $workspace && $workspace->owner_id === $user->id;
    }
}
