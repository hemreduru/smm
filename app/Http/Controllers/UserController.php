<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Services\UserService;
use App\Http\Requests\UpdateUserProfileRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display user list page.
     */
    public function index(Request $request): View
    {
        $workspaceId = $request->user()->current_workspace_id;
        $roles = $this->userService->getRolesForWorkspace($workspaceId);

        return view('users.index', compact('roles', 'workspaceId'));
    }

    /**
     * Get users data for DataTables.
     */
    public function data(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id;
        $currentUserId = $request->user()->id;

        $users = $this->userService->getUsersQueryForWorkspace($workspaceId);

        return DataTables::eloquent($users)
            ->addColumn('role', function ($user) use ($workspaceId) {
                return $this->userService->getUserRoleInWorkspace($user, $workspaceId) ?? '-';
            })
            ->addColumn('is_owner', function ($user) use ($workspaceId) {
                return $this->userService->isWorkspaceOwner($user, $workspaceId);
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
    public function updateProfile(UpdateUserProfileRequest $request): Response
    {
        $result = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $result->toResponse($request);
    }
}
