@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search fs-3 position-absolute ms-4"></i>
                <input type="text" id="workspace-search" class="form-control form-control-solid w-250px ps-12" placeholder="{{ __('messages.search') }}..." />
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('workspaces.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>{{ __('messages.create_workspace') }}
            </a>
        </div>
    </div>
    <div class="card-body pt-0">
        @if(count($workspaces) > 0)
            <div class="row g-6 g-xl-9" id="workspaces-container">
                @foreach($workspaces as $workspace)
                    @php
                        $isCurrentWorkspace = auth()->user()->current_workspace_id == $workspace['id'];
                        $memberCount = $workspace['members_count'] ?? 0;
                    @endphp
                    <div class="col-md-6 col-xl-4 workspace-card" data-name="{{ strtolower($workspace['name']) }}">
                        <div class="card border border-2 h-100 {{ $isCurrentWorkspace ? 'border-primary shadow-sm' : 'border-gray-300 hover-elevate-up' }}">
                            {{-- Card Header with Gradient --}}
                            <div class="card-header border-0 pt-5 {{ $isCurrentWorkspace ? 'bg-light-primary' : '' }}">
                                <div class="card-title m-0">
                                    <div class="symbol symbol-50px w-50px bg-light me-3">
                                        <span class="symbol-label bg-light-{{ $isCurrentWorkspace ? 'primary' : 'info' }}">
                                            <i class="bi bi-briefcase text-{{ $isCurrentWorkspace ? 'primary' : 'info' }} fs-2"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('workspaces.show', $workspace['id']) }}" class="fs-4 fw-bold text-gray-900 text-hover-primary mb-1">
                                            {{ $workspace['name'] }}
                                        </a>
                                        @if($isCurrentWorkspace)
                                            <span class="badge badge-light-success fs-8 fw-semibold">
                                                <i class="bi bi-check-circle me-1"></i>{{ __('messages.current') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Action Dropdown --}}
                                <div class="card-toolbar">
                                    <button type="button" class="btn btn-sm btn-icon btn-color-gray-400 btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="bi bi-three-dots-vertical fs-4"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="{{ route('workspaces.show', $workspace['id']) }}" class="menu-link px-3">
                                                <i class="bi bi-eye me-2"></i>{{ __('messages.view') }}
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="{{ route('workspaces.edit', $workspace['id']) }}" class="menu-link px-3">
                                                <i class="bi bi-pencil me-2"></i>{{ __('messages.edit') }}
                                            </a>
                                        </div>
                                        @if(!$isCurrentWorkspace)
                                            <div class="separator my-2"></div>
                                            <div class="menu-item px-3">
                                                <form action="{{ route('workspaces.switch', $workspace['id']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="menu-link px-3 border-0 bg-transparent w-100 text-start">
                                                        <i class="bi bi-arrow-repeat me-2"></i>{{ __('messages.switch') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Card Body with Stats --}}
                            <div class="card-body d-flex flex-column px-9 pt-0 pb-5">
                                {{-- Members Avatar Group --}}
                                <div class="d-flex flex-stack mt-4">
                                    <div class="symbol-group symbol-hover flex-nowrap">
                                        @for($i = 0; $i < min(5, $memberCount); $i++)
                                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Team Member">
                                                <span class="symbol-label bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][$i % 5] }} text-inverse-{{ ['primary', 'success', 'info', 'warning', 'danger'][$i % 5] }} fs-8 fw-bold">
                                                    {{ chr(65 + $i) }}
                                                </span>
                                            </div>
                                        @endfor
                                        @if($memberCount > 5)
                                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="{{ $memberCount - 5 }} {{ __('messages.more') }}">
                                                <span class="symbol-label bg-gray-900 text-white fs-8 fw-bold">+{{ $memberCount - 5 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800">{{ $memberCount }}</span>
                                        <span class="text-gray-500 fs-7">{{ __('messages.members') }}</span>
                                    </div>
                                </div>

                                {{-- Quick Actions --}}
                                <div class="d-flex flex-stack flex-wrap gap-2 mt-auto pt-4">
                                    @if(!$isCurrentWorkspace)
                                        <form action="{{ route('workspaces.switch', $workspace['id']) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-primary w-100">
                                                <i class="bi bi-box-arrow-in-right me-1"></i>{{ __('messages.switch') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="flex-grow-1"></span>
                                    @endif
                                    <a href="{{ route('workspaces.show', $workspace['id']) }}" class="btn btn-sm btn-icon btn-light-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('workspaces.edit', $workspace['id']) }}" class="btn btn-sm btn-icon btn-light-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-15">
                <div class="symbol symbol-100px mb-5">
                    <span class="symbol-label bg-light-primary">
                        <i class="bi bi-briefcase text-primary fs-1"></i>
                    </span>
                </div>
                <h3 class="text-gray-900 mb-3">{{ __('messages.no_workspaces') }}</h3>
                <p class="text-gray-500 fs-6 mb-5">{{ __('messages.create_first_workspace') }}</p>
                <a href="{{ route('workspaces.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('messages.create_workspace') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search filter
    const searchInput = document.getElementById('workspace-search');
    const cards = document.querySelectorAll('.workspace-card');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        cards.forEach(card => {
            const name = card.dataset.name;
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
@endpush
