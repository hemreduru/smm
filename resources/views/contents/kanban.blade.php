@extends('layouts.app')

@section('title', __('messages.contents') . ' - ' . __('messages.kanban_view'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.contents') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ __('messages.contents') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    {{-- View Toggle & Actions --}}
    <div class="card card-flush mb-5">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    {{-- View Toggle --}}
                    <div class="btn-group" role="group">
                        <a href="{{ route('contents.index') }}" class="btn btn-light" data-bs-toggle="tooltip" title="{{ __('messages.table_view') }}">
                            <i class="bi bi-table fs-4"></i>
                        </a>
                        <a href="{{ route('contents.kanban') }}" class="btn btn-light-primary active" data-bs-toggle="tooltip" title="{{ __('messages.kanban_view') }}">
                            <i class="bi bi-kanban fs-4"></i>
                        </a>
                    </div>
                </div>
                <a href="{{ route('contents.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg fs-4 me-1"></i>
                    {{ __('messages.create_content') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Kanban Board --}}
    <div class="kanban-board">
        <div class="d-flex overflow-auto pb-4" style="gap: 1rem;">
            @foreach($statuses as $status)
                <div class="kanban-column flex-shrink-0" style="width: 320px;">
                    {{-- Column Header --}}
                    <div class="card card-flush mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="badge {{ $status->badgeClass() }} me-2">
                                        <i class="bi {{ $status->icon() }}"></i>
                                    </span>
                                    <span class="fw-bold text-gray-800">{{ $status->label() }}</span>
                                </div>
                                <span class="badge badge-light-dark">{{ $contentsByStatus[$status->value]->count() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Column Content --}}
                    <div class="kanban-items" data-status="{{ $status->value }}" style="min-height: 200px;">
                        @forelse($contentsByStatus[$status->value] as $content)
                            <div class="card card-flush mb-3 kanban-item" data-id="{{ $content->id }}">
                                <div class="card-body p-4">
                                    {{-- Thumbnail --}}
                                    @if($content->thumbnail_path)
                                        <div class="mb-3">
                                            <img src="{{ $content->getThumbnailUrl() }}" alt="" class="w-100 rounded" style="height: 120px; object-fit: cover;">
                                        </div>
                                    @else
                                        <div class="mb-3 bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                            <i class="bi bi-film text-gray-400 fs-2x"></i>
                                        </div>
                                    @endif

                                    {{-- Title --}}
                                    <a href="{{ route('contents.show', $content) }}" class="text-gray-800 fw-bold text-hover-primary fs-6 d-block mb-2">
                                        {{ Str::limit($content->title ?? $content->caption ?? __('messages.content') . ' #' . $content->id, 40) }}
                                    </a>

                                    {{-- Meta --}}
                                    <div class="d-flex align-items-center text-gray-500 fs-8 mb-3">
                                        @if($content->duration)
                                            <i class="bi bi-clock me-1"></i>
                                            <span class="me-3">{{ $content->formatted_duration }}</span>
                                        @endif
                                        <i class="bi bi-calendar me-1"></i>
                                        <span>{{ $content->created_at->format('d M') }}</span>
                                    </div>

                                    {{-- Account Group --}}
                                    @if($content->accountGroup)
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="symbol symbol-25px me-2">
                                                <div class="symbol-label bg-light-primary">
                                                    <i class="bi bi-collection text-primary fs-7"></i>
                                                </div>
                                            </div>
                                            <span class="text-gray-600 fs-8">{{ $content->accountGroup->name }}</span>
                                        </div>
                                    @endif

                                    {{-- Schedule Info --}}
                                    @if($content->scheduled_at)
                                        <div class="d-flex align-items-center text-warning fs-8">
                                            <i class="bi bi-clock-history me-1"></i>
                                            <span>{{ $content->scheduled_at->format('d M Y H:i') }}</span>
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="separator my-3"></div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex">
                                            <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-icon btn-light-primary me-1">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($content->isEditable())
                                                <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-icon btn-light-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        </div>
                                        <span class="text-gray-400 fs-9">{{ $content->creator->name }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card card-flush bg-light-secondary border-dashed">
                                <div class="card-body text-center py-10">
                                    <i class="bi bi-inbox text-gray-400 fs-2x d-block mb-3"></i>
                                    <span class="text-gray-500 fs-7">{{ __('messages.no_contents') }}</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
<style>
    .kanban-board {
        overflow-x: auto;
    }
    .kanban-column {
        min-width: 320px;
    }
    .kanban-item {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kanban-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
    }
    .border-dashed {
        border: 2px dashed var(--bs-gray-300) !important;
    }
</style>
@endpush
