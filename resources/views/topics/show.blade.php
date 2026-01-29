@extends('layouts.app')

@section('title', $topic->title)

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.topics.view') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('topics.index') }}" class="text-muted text-hover-primary">{{ __('messages.topics.title') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ Str::limit($topic->title, 30) }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-10">
        {{-- Main Content --}}
        <div class="col-xl-8">
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <h3 class="card-title fw-bold">{{ $topic->title }}</h3>
                    <div class="card-toolbar">
                        @include('topics.partials.status-badge', ['topic' => $topic])
                    </div>
                </div>
                <div class="card-body">
                    {{-- Description --}}
                    @if($topic->description)
                        <div class="mb-7">
                            <label class="fs-6 fw-semibold text-muted mb-2">{{ __('messages.topics.fields.description') }}</label>
                            <p class="fs-6 text-gray-800">{{ $topic->description }}</p>
                        </div>
                    @endif

                    {{-- Niche & Keywords --}}
                    <div class="row mb-7">
                        <div class="col-md-6">
                            <label class="fs-6 fw-semibold text-muted mb-2">{{ __('messages.topics.fields.niche') }}</label>
                            <div>
                                @if($topic->niche)
                                    <span class="badge badge-light-info fs-7">{{ $topic->niche }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="fs-6 fw-semibold text-muted mb-2">{{ __('messages.topics.fields.keywords') }}</label>
                            <div>
                                @if($topic->keywords && count($topic->keywords) > 0)
                                    @foreach($topic->keywords as $keyword)
                                        <span class="badge badge-light-primary me-1 mb-1">{{ $keyword }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- AI Response (if any) --}}
                    @if($topic->ai_response)
                        <div class="mb-7">
                            <label class="fs-6 fw-semibold text-muted mb-2">AI Response</label>
                            <div class="bg-light-primary rounded p-4">
                                <pre class="mb-0 text-gray-800" style="white-space: pre-wrap;">{{ is_array($topic->ai_response) ? json_encode($topic->ai_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $topic->ai_response }}</pre>
                            </div>
                        </div>
                    @endif

                    {{-- Error Message (if failed) --}}
                    @if($topic->isFailed() && $topic->error_message)
                        <div class="alert alert-danger d-flex align-items-center p-5">
                            <i class="bi bi-exclamation-triangle-fill fs-2x text-danger me-4"></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">{{ __('messages.error') }}</h4>
                                <span>{{ $topic->error_message }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-xl-4">
            {{-- Actions Card --}}
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <h3 class="card-title fw-bold">{{ __('messages.actions') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        {{-- Edit --}}
                        @if($topic->canBeEdited())
                            <a href="{{ route('topics.edit', $topic) }}" class="btn btn-light-primary">
                                <i class="bi bi-pencil me-2"></i>
                                {{ __('messages.edit') }}
                            </a>
                        @endif

                        {{-- Approve --}}
                        @if($topic->isDraft())
                            <button type="button" class="btn btn-light-success" onclick="approveTopic({{ $topic->id }})">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ __('messages.topics.approve') }}
                            </button>
                        @endif

                        {{-- Send to n8n --}}
                        @if($topic->canBeSentToN8n())
                            <button type="button" class="btn btn-primary" onclick="sendToN8n({{ $topic->id }})">
                                <i class="bi bi-send me-2"></i>
                                {{ __('messages.topics.send_to_n8n') }}
                            </button>
                        @endif

                        {{-- Reset to Draft --}}
                        @if($topic->isFailed())
                            <button type="button" class="btn btn-light-warning" onclick="resetToDraft({{ $topic->id }})">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>
                                {{ __('messages.topics.reset_to_draft') }}
                            </button>
                        @endif

                        {{-- Delete --}}
                        @if($topic->canBeEdited())
                            <button type="button" class="btn btn-light-danger" onclick="deleteTopic({{ $topic->id }})">
                                <i class="bi bi-trash me-2"></i>
                                {{ __('messages.delete') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Details Card --}}
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="card-title fw-bold">{{ __('messages.details') ?? 'Details' }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-4">
                        {{-- Status --}}
                        <div>
                            <label class="fs-7 fw-semibold text-muted">{{ __('messages.topics.fields.status') }}</label>
                            <div class="fw-semibold">@include('topics.partials.status-badge', ['topic' => $topic])</div>
                        </div>

                        {{-- AI Provider --}}
                        @if($topic->ai_provider)
                            <div>
                                <label class="fs-7 fw-semibold text-muted">{{ __('messages.topics.fields.ai_provider') }}</label>
                                <div class="fw-semibold">{{ $topic->ai_provider }}</div>
                            </div>
                        @endif

                        {{-- AI Model --}}
                        @if($topic->ai_model)
                            <div>
                                <label class="fs-7 fw-semibold text-muted">{{ __('messages.topics.fields.ai_model') }}</label>
                                <div class="fw-semibold">{{ $topic->ai_model }}</div>
                            </div>
                        @endif

                        {{-- n8n Execution ID --}}
                        @if($topic->n8n_execution_id)
                            <div>
                                <label class="fs-7 fw-semibold text-muted">n8n Execution ID</label>
                                <div class="fw-semibold"><code>{{ $topic->n8n_execution_id }}</code></div>
                            </div>
                        @endif

                        {{-- Created By --}}
                        <div>
                            <label class="fs-7 fw-semibold text-muted">{{ __('messages.topics.fields.created_by') }}</label>
                            <div class="fw-semibold">{{ $topic->creator?->name ?? '-' }}</div>
                        </div>

                        {{-- Created At --}}
                        <div>
                            <label class="fs-7 fw-semibold text-muted">{{ __('messages.created_at') }}</label>
                            <div class="fw-semibold">{{ $topic->created_at->format('d.m.Y H:i') }}</div>
                        </div>

                        {{-- Sent At --}}
                        @if($topic->sent_at)
                            <div>
                                <label class="fs-7 fw-semibold text-muted">{{ __('messages.topics.fields.sent_at') }}</label>
                                <div class="fw-semibold">{{ $topic->sent_at->format('d.m.Y H:i') }}</div>
                            </div>
                        @endif

                        {{-- Completed At --}}
                        @if($topic->completed_at)
                            <div>
                                <label class="fs-7 fw-semibold text-muted">{{ __('messages.topics.fields.completed_at') }}</label>
                                <div class="fw-semibold">{{ $topic->completed_at->format('d.m.Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
async function approveTopic(id) {
    const confirmed = await confirmAction({
        title: '{{ __('messages.topics.approve') }}',
        text: '{{ __('messages.confirm_action') ?? 'Are you sure?' }}',
        confirmButtonText: '{{ __('messages.yes') }}',
    });

    if (!confirmed) return;

    try {
        const response = await fetch(`/topics/${id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccess(result.message);
            location.reload();
        } else {
            showError(result.message);
        }
    } catch (error) {
        showError('{{ __('messages.error') }}');
    }
}

async function sendToN8n(id) {
    const confirmed = await confirmAction({
        title: '{{ __('messages.topics.send_to_n8n') }}',
        text: '{{ __('messages.confirm_action') ?? 'Are you sure?' }}',
        confirmButtonText: '{{ __('messages.yes') }}',
    });

    if (!confirmed) return;

    try {
        const response = await fetch(`/topics/${id}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccess(result.message);
            location.reload();
        } else {
            showError(result.message);
        }
    } catch (error) {
        showError('{{ __('messages.error') }}');
    }
}

async function resetToDraft(id) {
    const confirmed = await confirmAction({
        title: '{{ __('messages.topics.reset_to_draft') }}',
        text: '{{ __('messages.confirm_action') ?? 'Are you sure?' }}',
        confirmButtonText: '{{ __('messages.yes') }}',
    });

    if (!confirmed) return;

    try {
        const response = await fetch(`/topics/${id}/reset`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccess(result.message);
            location.reload();
        } else {
            showError(result.message);
        }
    } catch (error) {
        showError('{{ __('messages.error') }}');
    }
}

async function deleteTopic(id) {
    const confirmed = await confirmAction({
        title: '{{ __('messages.delete') }}',
        text: '{{ __('messages.confirm_delete') ?? 'This action cannot be undone.' }}',
        confirmButtonText: '{{ __('messages.delete') }}',
        icon: 'warning'
    });

    if (!confirmed) return;

    try {
        const response = await fetch(`/topics/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccess(result.message);
            window.location.href = '{{ route('topics.index') }}';
        } else {
            showError(result.message);
        }
    } catch (error) {
        showError('{{ __('messages.error') }}');
    }
}
</script>
@endpush
