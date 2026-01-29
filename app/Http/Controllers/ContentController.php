<?php

namespace App\Http\Controllers;

use App\Core\Repositories\AccountGroupRepository;
use App\Core\Services\ContentService;
use App\Enums\ContentStatus;
use App\Http\Requests\DeleteContentRequest;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ContentController extends Controller
{
    public function __construct(
        protected ContentService $contentService,
        protected AccountGroupRepository $accountGroupRepository
    ) {}

    /**
     * Display a listing of contents
     */
    public function index(): View
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $statusCounts = $this->contentService->getStatusCounts($workspaceId);
        $statuses = ContentStatus::cases();

        return view('contents.index', compact('statusCounts', 'statuses'));
    }

    /**
     * DataTable data endpoint
     */
    public function data(Request $request): JsonResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $query = $this->contentService->getDataTableQuery($workspaceId);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return DataTables::eloquent($query)
            ->addColumn('thumbnail', function ($content) {
                return view('contents.partials.thumbnail', compact('content'))->render();
            })
            ->addColumn('info', function ($content) {
                return view('contents.partials.info', compact('content'))->render();
            })
            ->addColumn('status_badge', function ($content) {
                return view('contents.partials.status', compact('content'))->render();
            })
            ->addColumn('schedule_info', function ($content) {
                return view('contents.partials.schedule', compact('content'))->render();
            })
            ->addColumn('actions', function ($content) {
                return view('contents.partials.actions', compact('content'))->render();
            })
            ->rawColumns(['thumbnail', 'info', 'status_badge', 'schedule_info', 'actions'])
            ->toJson();
    }

    /**
     * Show the form for creating new content
     */
    public function create(): View
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $accountGroups = $this->accountGroupRepository->getActiveForWorkspace($workspaceId);

        return view('contents.create', compact('accountGroups'));
    }

    /**
     * Store a newly created content
     */
    public function store(StoreContentRequest $request): RedirectResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $userId = auth()->id();

        $result = $this->contentService->create(
            $request->validated(),
            $request->file('video'),
            $workspaceId,
            $userId
        );

        if ($result->success) {
            return redirect($result->redirect)->with('success', $result->message);
        }

        return back()->withInput()->with('error', $result->message);
    }

    /**
     * Display the specified content
     */
    public function show(int $id): View|RedirectResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $content = $this->contentService->findForWorkspace($id, $workspaceId);

        if (!$content) {
            return redirect()->route('contents.index')->with('error', __('messages.content_not_found'));
        }

        return view('contents.show', compact('content'));
    }

    /**
     * Show the form for editing content
     */
    public function edit(int $id): View|RedirectResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $content = $this->contentService->findForWorkspace($id, $workspaceId);

        if (!$content) {
            return redirect()->route('contents.index')->with('error', __('messages.content_not_found'));
        }

        if (!$content->isEditable()) {
            return redirect()->route('contents.show', $content)->with('error', __('messages.content_not_editable'));
        }

        $accountGroups = $this->accountGroupRepository->getActiveForWorkspace($workspaceId);

        return view('contents.edit', compact('content', 'accountGroups'));
    }

    /**
     * Update the specified content
     */
    public function update(UpdateContentRequest $request, int $id): RedirectResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $userId = auth()->id();

        $result = $this->contentService->update($id, $request->validated(), $workspaceId, $userId);

        if ($result->success) {
            return redirect($result->redirect)->with('success', $result->message);
        }

        return back()->withInput()->with('error', $result->message);
    }

    /**
     * Remove the specified content
     */
    public function destroy(DeleteContentRequest $request, int $id): RedirectResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $userId = auth()->id();

        $result = $this->contentService->delete($id, $workspaceId, $userId);

        if ($result->success) {
            return redirect($result->redirect)->with('success', $result->message);
        }

        return back()->with('error', $result->message);
    }

    /**
     * Approve content
     */
    public function approve(int $id): RedirectResponse|JsonResponse
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $userId = auth()->id();

        $result = $this->contentService->approve($id, $workspaceId, $userId);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
            ], $result->success ? 200 : 400);
        }

        if ($result->success) {
            return back()->with('success', $result->message);
        }

        return back()->with('error', $result->message);
    }

    /**
     * Schedule content for publishing
     */
    public function schedule(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $workspaceId = auth()->user()->current_workspace_id;
        $userId = auth()->id();

        $result = $this->contentService->schedule($id, $request->scheduled_at, $workspaceId, $userId);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => $result->success,
                'message' => $result->message,
            ], $result->success ? 200 : 400);
        }

        if ($result->success) {
            return back()->with('success', $result->message);
        }

        return back()->with('error', $result->message);
    }

    /**
     * Kanban board view
     */
    public function kanban(): View
    {
        $workspaceId = auth()->user()->current_workspace_id;
        $contents = $this->contentService->getForWorkspace($workspaceId);
        $statuses = ContentStatus::cases();

        // Group contents by status
        $contentsByStatus = [];
        foreach ($statuses as $status) {
            $contentsByStatus[$status->value] = $contents->where('status', $status);
        }

        return view('contents.kanban', compact('contentsByStatus', 'statuses'));
    }
}
