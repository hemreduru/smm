<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Repositories\UserRepository;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UpdateUserProfileRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Display user list page.
     */
    public function index(Request $request): View
    {
        $workspaceId = $request->user()->current_workspace_id;
        $roles = Role::where('workspace_id', $workspaceId)->get();

        return view('users.index', compact('roles', 'workspaceId'));
    }

    /**
     * Get users data for DataTables.
     */
    public function data(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id;
        $currentUserId = $request->user()->id;

        $users = User::select(['users.id', 'users.name', 'users.email', 'users.created_at'])
            ->join('workspace_user', 'users.id', '=', 'workspace_user.user_id')
            ->where('workspace_user.workspace_id', $workspaceId)
            ->with(['workspaces' => function ($query) use ($workspaceId) {
                $query->where('workspaces.id', $workspaceId);
            }]);

        return DataTables::eloquent($users)
            ->addColumn('role', function ($user) use ($workspaceId) {
                $pivot = $user->workspaces->first()?->pivot;
                if ($pivot && $pivot->role_id) {
                    $role = Role::find($pivot->role_id);
                    return $role ? $role->name : '-';
                }
                return '-';
            })
            ->addColumn('actions', function ($user) use ($currentUserId, $workspaceId) {
                $isCurrentUser = $user->id === $currentUserId;
                
                return view('users.partials.actions', [
                    'user' => $user,
                    'workspaceId' => $workspaceId,
                    'isCurrentUser' => $isCurrentUser,
                ])->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show user profile page.
     */
    public function profile(Request $request): View
    {
        $user = $request->user();

        return view('users.profile', compact('user'));
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateUserProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return back()->with('success', __('messages.profile_updated'));
    }
}
