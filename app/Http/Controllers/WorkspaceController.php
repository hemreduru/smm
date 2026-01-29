<?php

namespace App\Http\Controllers;

use App\Core\Services\WorkspaceService;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Requests\Workspace\InviteUserRequest;
use App\Http\Requests\Workspace\UpdateUserRoleRequest;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceController extends Controller
{
    public function __construct(
        protected WorkspaceService $workspaceService
    ) {}

    /**
     * Display list of user's workspaces.
     */
    public function index(Request $request): View
    {
        $workspaces = $this->workspaceService->getUserWorkspaces($request->user()->id);

        return view('workspaces.index', compact('workspaces'));
    }

    /**
     * Show create workspace form.
     */
    public function create(): View
    {
        return view('workspaces.create');
    }

    /**
     * Store new workspace.
     */
    public function store(CreateWorkspaceRequest $request): Response
    {
        $result = $this->workspaceService->create(
            $request->validated(),
            $request->user()->id
        );

        return $result->toResponse($request);
    }

    /**
     * Display workspace details.
     */
    public function show(Workspace $workspace): View
    {
        $workspace->load(['users', 'roles', 'owner']);

        return view('workspaces.show', compact('workspace'));
    }

    /**
     * Show edit workspace form.
     */
    public function edit(Workspace $workspace): View
    {
        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Update workspace.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): Response
    {
        $result = $this->workspaceService->update(
            $workspace->id,
            $request->validated(),
            $request->user()->id
        );

        return $result->toResponse($request);
    }

    /**
     * Delete workspace.
     */
    public function destroy(Request $request, Workspace $workspace): Response
    {
        $result = $this->workspaceService->delete($workspace->id, $request->user()->id);

        return $result->toResponse($request);
    }

    /**
     * Switch to a workspace.
     */
    public function switch(Request $request, Workspace $workspace): Response
    {
        $result = $this->workspaceService->switchWorkspace($request->user()->id, $workspace->id);

        return $result->toResponse($request);
    }

    /**
     * Invite user to workspace.
     */
    public function inviteUser(InviteUserRequest $request, Workspace $workspace): Response
    {
        $result = $this->workspaceService->inviteUser(
            $workspace->id,
            $request->validated()['email'],
            $request->validated()['role_id'],
            $request->user()->id
        );

        return $result->toResponse($request);
    }

    /**
     * Remove user from workspace.
     */
    public function removeUser(Request $request, Workspace $workspace, User $user): Response
    {
        $result = $this->workspaceService->removeUser(
            $workspace->id,
            $user->id,
            $request->user()->id
        );

        return $result->toResponse($request);
    }

    /**
     * Update user's role in workspace.
     */
    public function updateUserRole(UpdateUserRoleRequest $request, Workspace $workspace, User $user): Response
    {
        $result = $this->workspaceService->updateUserRole(
            $workspace->id,
            $user->id,
            $request->validated()['role_id'],
            $request->user()->id
        );

        return $result->toResponse($request);
    }
}
