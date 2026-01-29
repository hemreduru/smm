<?php

namespace App\Http\Controllers;

use App\Core\Services\PlatformAccountService;
use App\Enums\Platform;
use App\Http\Requests\DeletePlatformAccountRequest;
use App\Http\Requests\StorePlatformAccountRequest;
use App\Http\Requests\UpdatePlatformAccountRequest;
use App\Models\PlatformAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PlatformAccountController extends Controller
{
    public function __construct(
        private PlatformAccountService $accountService
    ) {}

    /**
     * Display a listing of platform accounts.
     */
    public function index(): View
    {
        $workspaceId = Auth::user()->current_workspace_id;
        $platformCounts = $this->accountService->getAccountCountByPlatform($workspaceId);
        $platforms = Platform::cases();

        return view('accounts.index', compact('platformCounts', 'platforms'));
    }

    /**
     * Return DataTables JSON for platform accounts.
     */
    public function data(Request $request): JsonResponse
    {
        $workspaceId = Auth::user()->current_workspace_id;
        $accounts = $this->accountService->getAccountsForWorkspace($workspaceId);

        return DataTables::of($accounts)
            ->addColumn('platform_label', fn($account) => $account->platform->label())
            ->addColumn('platform_icon', fn($account) => $account->platform->icon())
            ->addColumn('platform_badge', fn($account) => $account->platform->badgeClass())
            ->addColumn('status_label', fn($account) => $account->status->label())
            ->addColumn('status_badge', fn($account) => $account->status->badgeClass())
            ->addColumn('is_healthy', fn($account) => $account->isHealthy())
            ->editColumn('last_synced_at', function ($account) {
                return $account->last_synced_at ? $account->last_synced_at->toISOString() : null;
            })
            ->addColumn('actions', function ($account) {
                return view('accounts.partials.actions', compact('account'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for connecting a new platform account.
     */
    public function create(Request $request): View
    {
        $platform = $request->query('platform');
        $platforms = Platform::cases();

        return view('accounts.create', compact('platform', 'platforms'));
    }

    /**
     * Store a newly connected platform account.
     */
    public function store(StorePlatformAccountRequest $request): RedirectResponse
    {
        $workspaceId = Auth::user()->current_workspace_id;

        $result = $this->accountService->connectAccount($workspaceId, $request->validated());

        return redirect()
            ->route('accounts.index')
            ->with('result', $result->toArray());
    }

    /**
     * Display the specified platform account.
     */
    public function show(PlatformAccount $account): View
    {
        $this->authorizeWorkspace($account);

        $account->load('groups');

        return view('accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified platform account.
     */
    public function edit(PlatformAccount $account): View
    {
        $this->authorizeWorkspace($account);

        return view('accounts.edit', compact('account'));
    }

    /**
     * Update the specified platform account.
     */
    public function update(UpdatePlatformAccountRequest $request, PlatformAccount $account): RedirectResponse
    {
        $this->authorizeWorkspace($account);

        $result = $this->accountService->updateAccount($account, $request->validated());

        return redirect()
            ->route('accounts.index')
            ->with('result', $result->toArray());
    }

    /**
     * Disconnect (remove) the specified platform account.
     */
    public function destroy(DeletePlatformAccountRequest $request, PlatformAccount $account): Response
    {
        $result = $this->accountService->disconnectAccount($account);

        return $result->toResponse($request);
    }

    /**
     * Authorize that the account belongs to the current workspace.
     */
    private function authorizeWorkspace(PlatformAccount $account): void
    {
        $workspaceId = Auth::user()->current_workspace_id;

        if ($account->workspace_id !== $workspaceId) {
            abort(403, __('messages.unauthorized'));
        }
    }
}
