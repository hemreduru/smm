<?php

namespace App\Http\Controllers;

use App\Core\Services\TopicService;
use App\Enums\AIProvider;
use App\Enums\TopicStatus;
use App\Http\Requests\Topic\GenerateTopicRequest;
use App\Http\Requests\Topic\StoreTopicRequest;
use App\Http\Requests\Topic\UpdateTopicRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TopicController extends Controller
{
    public function __construct(
        protected TopicService $topicService
    ) {}

    /**
     * Display a listing of topics.
     */
    public function index(Request $request): View|JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id;

        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = $this->topicService->getDataTableQuery($workspaceId);

            return DataTables::eloquent($query)
                ->addColumn('status_badge', function ($topic) {
                    return view('topics.partials.status-badge', compact('topic'))->render();
                })
                ->addColumn('creator_name', function ($topic) {
                    return $topic->creator?->name ?? '-';
                })
                ->addColumn('actions', function ($topic) {
                    return view('topics.partials.actions', compact('topic'))->render();
                })
                ->editColumn('created_at', function ($topic) {
                    return $topic->created_at->format('d.m.Y H:i');
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        $statistics = $this->topicService->getStatistics($workspaceId);
        $providers = $this->topicService->getAIProvidersInfo();

        return view('topics.index', compact('statistics', 'providers'));
    }

    /**
     * Show the form for creating a new topic.
     */
    public function create(): View
    {
        $providers = $this->topicService->getAIProvidersInfo();

        return view('topics.create', compact('providers'));
    }

    /**
     * Store a newly created topic.
     */
    public function store(StoreTopicRequest $request): RedirectResponse|JsonResponse
    {
        $result = $this->topicService->create(
            $request->validated(),
            $request->user()
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
                'data' => $result->getData(),
            ], $result->isSuccess() ? 200 : 422);
        }

        if (!$result->isSuccess()) {
            return back()
                ->withInput()
                ->with('error', $result->getMessage());
        }

        return redirect()
            ->route('topics.show', $result->getData()['topic']->id)
            ->with('success', $result->getMessage());
    }

    /**
     * Generate topics using AI.
     */
    public function generate(GenerateTopicRequest $request): JsonResponse
    {
        $provider = $request->input('provider')
            ? AIProvider::from($request->input('provider'))
            : null;

        $result = $this->topicService->generateWithAI(
            niche: $request->input('niche'),
            keywords: $request->input('keywords', []),
            user: $request->user(),
            provider: $provider,
            model: $request->input('model')
        );

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->isSuccess() ? 200 : 422);
    }

    /**
     * Display the specified topic.
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $result = $this->topicService->find($id, $request->user()->current_workspace_id);

        if (!$result->isSuccess()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result->getMessage(),
                ], 404);
            }

            abort(404, $result->getMessage());
        }

        $topic = $result->getData()['topic'];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => ['topic' => $topic],
            ]);
        }

        return view('topics.show', compact('topic'));
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(Request $request, int $id): View
    {
        $result = $this->topicService->find($id, $request->user()->current_workspace_id);

        if (!$result->isSuccess()) {
            abort(404, $result->getMessage());
        }

        $topic = $result->getData()['topic'];

        if (!$topic->canBeEdited()) {
            return redirect()
                ->route('topics.show', $id)
                ->with('error', __('messages.topics.cannot_edit'));
        }

        return view('topics.edit', compact('topic'));
    }

    /**
     * Update the specified topic.
     */
    public function update(UpdateTopicRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $result = $this->topicService->update(
            $id,
            $request->validated(),
            $request->user()->current_workspace_id
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
                'data' => $result->getData(),
            ], $result->isSuccess() ? 200 : 422);
        }

        if (!$result->isSuccess()) {
            return back()
                ->withInput()
                ->with('error', $result->getMessage());
        }

        return redirect()
            ->route('topics.show', $id)
            ->with('success', $result->getMessage());
    }

    /**
     * Remove the specified topic.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $result = $this->topicService->delete($id, $request->user()->current_workspace_id);

        if ($request->ajax()) {
            return response()->json([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
            ], $result->isSuccess() ? 200 : 422);
        }

        if (!$result->isSuccess()) {
            return back()->with('error', $result->getMessage());
        }

        return redirect()
            ->route('topics.index')
            ->with('success', $result->getMessage());
    }

    /**
     * Approve a topic.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $result = $this->topicService->approve($id, $request->user()->current_workspace_id);

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->isSuccess() ? 200 : 422);
    }

    /**
     * Send a topic to n8n for processing.
     */
    public function sendToN8n(Request $request, int $id): JsonResponse
    {
        $result = $this->topicService->sendToN8n($id, $request->user()->current_workspace_id);

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->isSuccess() ? 200 : 422);
    }

    /**
     * Reset a failed topic to draft status.
     */
    public function resetToDraft(Request $request, int $id): JsonResponse
    {
        $result = $this->topicService->resetToDraft($id, $request->user()->current_workspace_id);

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], $result->isSuccess() ? 200 : 422);
    }

    /**
     * Get available AI providers and their models.
     */
    public function providers(): JsonResponse
    {
        $providers = $this->topicService->getAIProvidersInfo();

        return response()->json([
            'success' => true,
            'data' => ['providers' => $providers],
        ]);
    }
}
