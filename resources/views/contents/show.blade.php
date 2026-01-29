@extends('layouts.app')

@section('title', $content->title ?? __('messages.content'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ $content->title ?? __('messages.content') . ' #' . $content->id }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('contents.index') }}" class="text-muted text-hover-primary">{{ __('messages.contents') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ $content->title ?? '#' . $content->id }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-8">
        {{-- Main Content --}}
        <div class="col-xl-8">
            {{-- Video Player Card --}}
            <div class="card card-flush mb-5">
                <div class="card-body p-0">
                    <video class="w-100 rounded-top" controls style="max-height: 500px; background: #000;">
                        <source src="{{ $content->getVideoUrl() }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="card-footer py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center text-gray-500 fs-7">
                            @if($content->duration)
                                <i class="bi bi-clock me-1"></i>
                                <span class="me-4">{{ $content->formatted_duration }}</span>
                            @endif
                            @if($content->file_size)
                                <i class="bi bi-hdd me-1"></i>
                                <span class="me-4">{{ $content->formatted_file_size }}</span>
                            @endif
                            @if($content->original_filename)
                                <i class="bi bi-file-earmark me-1"></i>
                                <span>{{ $content->original_filename }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Captions Card --}}
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.caption') }}</h3>
                </div>
                <div class="card-body">
                    {{-- Turkish Caption --}}
                    @if($content->caption_tr)
                        <div class="mb-5">
                            <label class="fs-6 fw-semibold text-gray-600 mb-2">{{ __('messages.caption_tr') }}</label>
                            <div class="bg-light rounded p-4">
                                {!! nl2br(e($content->caption_tr)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- English Caption --}}
                    @if($content->caption_en)
                        <div class="mb-5">
                            <label class="fs-6 fw-semibold text-gray-600 mb-2">{{ __('messages.caption_en') }}</label>
                            <div class="bg-light rounded p-4">
                                {!! nl2br(e($content->caption_en)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Hashtags --}}
                    @if($content->hashtags && count($content->hashtags) > 0)
                        <div>
                            <label class="fs-6 fw-semibold text-gray-600 mb-2">{{ __('messages.hashtags') }}</label>
                            <div>
                                @foreach($content->hashtags as $hashtag)
                                    <span class="badge badge-light-primary me-1 mb-1">#{{ $hashtag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-xl-4">
            {{-- Status Card --}}
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.status') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-5">
                        <span class="badge {{ $content->status->badgeClass() }} fs-6 px-4 py-3">
                            <i class="bi {{ $content->status->icon() }} me-1"></i>
                            {{ $content->status->label() }}
                        </span>
                    </div>

                    @if($content->scheduled_at)
                        <div class="d-flex align-items-center text-gray-600 mb-3">
                            <i class="bi bi-calendar-event fs-4 me-2"></i>
                            <span class="fw-semibold">{{ __('messages.scheduled_at') }}:</span>
                            <span class="ms-2">{{ $content->scheduled_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif

                    @if($content->published_at)
                        <div class="d-flex align-items-center text-success mb-3">
                            <i class="bi bi-check-circle fs-4 me-2"></i>
                            <span class="fw-semibold">{{ __('messages.published_at') }}:</span>
                            <span class="ms-2">{{ $content->published_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif

                    <div class="separator my-5"></div>

                    {{-- Status Actions --}}
                    <div class="d-grid gap-2">
                        @if($content->canTransitionTo(\App\Enums\ContentStatus::APPROVED))
                            <form action="{{ route('contents.approve', $content) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>
                                    {{ __('messages.approve') }}
                                </button>
                            </form>
                        @endif

                        @if($content->canTransitionTo(\App\Enums\ContentStatus::SCHEDULED))
                            <button type="button" class="btn btn-light-warning" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                <i class="bi bi-clock me-2"></i>
                                {{ __('messages.schedule') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Account Group Card --}}
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.account_groups') }}</h3>
                </div>
                <div class="card-body">
                    @if($content->accountGroup)
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-3">
                                <div class="symbol-label bg-light-primary">
                                    <i class="bi bi-collection text-primary fs-3"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="{{ route('groups.show', $content->accountGroup) }}" class="text-gray-800 fw-bold text-hover-primary fs-6">
                                    {{ $content->accountGroup->name }}
                                </a>
                                <span class="text-gray-500 fs-7">{{ $content->accountGroup->accounts->count() }} {{ __('messages.accounts') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-5">
                            <i class="bi bi-inbox fs-3x mb-3 d-block text-gray-400"></i>
                            {{ __('messages.no_group') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Details Card --}}
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.details') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span class="text-gray-600">{{ __('messages.created_at') }}</span>
                        <span class="text-gray-800 fw-semibold">{{ $content->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span class="text-gray-600">{{ __('messages.created_by') }}</span>
                        <span class="text-gray-800 fw-semibold">{{ $content->creator->name }}</span>
                    </div>
                    @if($content->notes)
                        <div class="pt-3">
                            <span class="text-gray-600 d-block mb-2">{{ __('messages.internal_notes') }}</span>
                            <div class="bg-light rounded p-3 text-gray-700 fs-7">
                                {{ $content->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="card card-flush">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($content->isEditable())
                            <a href="{{ route('contents.edit', $content) }}" class="btn btn-light-primary">
                                <i class="bi bi-pencil me-2"></i>
                                {{ __('messages.edit') }}
                            </a>
                        @endif

                        @if($content->isDeletable())
                            <form method="POST" action="{{ route('contents.destroy', $content) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light-danger w-100"
                                        data-confirm="true"
                                        data-confirm-title="{{ __('messages.confirm_title') }}"
                                        data-confirm-text="{{ __('messages.confirm_delete_content') }}"
                                        data-confirm-button="{{ __('messages.delete') }}">
                                    <i class="bi bi-trash me-2"></i>
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('contents.index') }}" class="btn btn-light">
                            {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Modal --}}
    @if($content->canTransitionTo(\App\Enums\ContentStatus::SCHEDULED))
        <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('contents.schedule', $content) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('messages.schedule') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="fv-row">
                                <label class="required fs-6 fw-semibold mb-2">{{ __('messages.scheduled_at') }}</label>
                                <input type="datetime-local" class="form-control form-control-solid"
                                       name="scheduled_at" required
                                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('messages.schedule') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
