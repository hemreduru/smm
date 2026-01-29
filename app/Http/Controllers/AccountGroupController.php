<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Services\AccountGroupService;
use App\Core\Services\PlatformAccountService;
use App\Http\Requests\DeleteAccountGroupRequest;
use App\Http\Requests\StoreAccountGroupRequest;
use App\Http\Requests\UpdateAccountGroupRequest;
use App\Models\AccountGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AccountGroupController extends Controller
{
    public function __construct(
        private AccountGroupService $groupService,
        private PlatformAccountService $accountService
    ) {}

    /**
     * Display a listing of account groups.
     */
    public function index(): View
    {
        $workspaceId = Auth::user()->current_workspace_id;
        $groupsCount = $this->groupService->getGroupsCount($workspaceId);

        return view('groups.index', compact('groupsCount'));
    }

    /**
     * Return DataTables JSON for account groups.
     */
    public function data(Request $request): JsonResponse
    {
        $workspaceId = Auth::user()->current_workspace_id;
        $groups = $this->groupService->getGroupsForWorkspace($workspaceId);

        return DataTables::of($groups)
            ->addColumn('accounts_count', fn($group) => $group->accounts->count())
            ->addColumn('platforms', function ($group) {
                return $group->accounts->pluck('platform')->unique()->values();
            })
            ->addColumn('status', fn($group) => $group->is_active)
            ->addColumn('actions', function ($group) {
                return view('groups.partials.actions', compact('group'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new account group.
     */
    public function create(): View
    {
        $workspaceId = Auth::user()->current_workspace_id;
        $accounts = $this->accountService->getHealthyAccountsForWorkspace($workspaceId);

        return view('groups.create', compact('accounts'));
    }

    /**
     * Store a newly created account group.
     */
    public function store(StoreAccountGroupRequest $request): RedirectResponse
    {
        $workspaceId = Auth::user()->current_workspace_id;

        $result = $this->groupService->createGroup($workspaceId, $request->validated());

        return redirect()
            ->route('groups.index')
            ->with('result', $result->toArray());
    }

    /**
     * Display the specified account group.
     */
    public function show(AccountGroup $group): View
    {
        $this->authorizeWorkspace($group);

        $group->load('accounts');

        return view('groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified account group.
     */
    public function edit(AccountGroup $group): View
    {
        $this->authorizeWorkspace($group);

        $workspaceId = Auth::user()->current_workspace_id;
        $accounts = $this->accountService->getHealthyAccountsForWorkspace($workspaceId);
        $selectedAccountIds = $group->accounts->pluck('id')->toArray();

        return view('groups.edit', compact('group', 'accounts', 'selectedAccountIds'));
    }

    /**
     * Update the specified account group.
     */
    public function update(UpdateAccountGroupRequest $request, AccountGroup $group): RedirectResponse
    {
        $this->authorizeWorkspace($group);

        $result = $this->groupService->updateGroup($group, $request->validated());

        return redirect()
            ->route('groups.index')
            ->with('result', $result->toArray());
    }

    /**
     * Remove the specified account group.
     */
    public function destroy(DeleteAccountGroupRequest $request, AccountGroup $group): Response
    {
        $result = $this->groupService->deleteGroup($group);

        return $result->toResponse($request);
    }

    /**
     * Authorize that the group belongs to the current workspace.
     */
    private function authorizeWorkspace(AccountGroup $group): void
    {
        $workspaceId = Auth::user()->current_workspace_id;

        if ($group->workspace_id !== $workspaceId) {
            abort(403, __('messages.unauthorized'));
        }
    }
}
