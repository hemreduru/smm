@extends('layouts.app')

@section('title', $workspace->name)

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ $workspace->name }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('workspaces.index') }}" class="text-muted text-hover-primary">{{ __('messages.workspaces') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ $workspace->name }}</li>
        </ul>
    </div>
@endsection

@section('content')
{{-- Workspace Header Card --}}
<div class="card mb-5 mb-xl-10">
    <div class="card-body pt-9 pb-0">
        {{-- Header --}}
        <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
            {{-- Avatar --}}
            <div class="me-7 mb-4">
                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                    <span class="symbol-label bg-light-primary">
                        <i class="bi bi-briefcase text-primary fs-1" style="font-size: 4rem !important;"></i>
                    </span>
                    @if(auth()->user()->current_workspace_id == $workspace->id)
                        <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                    @endif
                </div>
            </div>
            
            {{-- Info --}}
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <h2 class="text-gray-900 fs-2 fw-bold me-3">{{ $workspace->name }}</h2>
                            @if(auth()->user()->current_workspace_id == $workspace->id)
                                <span class="badge badge-light-success fs-8 fw-semibold">{{ __('messages.current') }}</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                            <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                <i class="bi bi-person fs-4 me-1"></i>
                                {{ __('messages.owner') }}: {{ $workspace->owner->name ?? 'N/A' }}
                            </span>
                            <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                <i class="bi bi-calendar3 fs-4 me-1"></i>
                                {{ $workspace->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                    {{-- Actions --}}
                    <div class="d-flex my-4">
                        @if(auth()->user()->current_workspace_id != $workspace->id)
                            <form action="{{ route('workspaces.switch', $workspace) }}" method="POST" class="me-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>{{ __('messages.switch') }}
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('workspaces.edit', $workspace) }}" class="btn btn-sm btn-light-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('messages.edit') }}
                        </a>
                    </div>
                </div>
                
                {{-- Stats --}}
                <div class="d-flex flex-wrap flex-stack">
                    <div class="d-flex flex-column flex-grow-1 pe-8">
                        <div class="d-flex flex-wrap">
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people fs-3 text-primary me-2"></i>
                                    <span class="fs-2 fw-bold text-gray-800">{{ $workspace->users->count() }}</span>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-500">{{ __('messages.members') }}</div>
                            </div>
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shield fs-3 text-info me-2"></i>
                                    <span class="fs-2 fw-bold text-gray-800">{{ $workspace->roles->count() }}</span>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-500">{{ __('messages.roles') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Team Members Card --}}
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search fs-3 position-absolute ms-4"></i>
                <input type="text" id="member-search" class="form-control form-control-solid w-250px ps-12" placeholder="{{ __('messages.search') }}..." />
            </div>
        </div>
        <div class="card-toolbar">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
                <i class="bi bi-person-plus me-2"></i>{{ __('messages.invite_user') }}
            </button>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="members-table">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-300px rounded-start">{{ __('messages.name') }}</th>
                    <th class="min-w-150px">{{ __('messages.role') }}</th>
                    <th class="min-w-150px">{{ __('messages.joined_at') }}</th>
                    <th class="min-w-100px text-end rounded-end pe-4">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workspace->users as $user)
                    @php
                        $role = $user->pivot->role_id ? \App\Models\Role::find($user->pivot->role_id) : null;
                        $isOwner = $workspace->owner_id === $user->id;
                        $roleColors = ['Admin' => 'primary', 'Editor' => 'warning', 'Viewer' => 'info'];
                        $roleColor = $roleColors[$role?->name] ?? 'secondary';
                    @endphp
                    <tr class="member-row" data-name="{{ strtolower($user->name) }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-4">
                                    <span class="symbol-label bg-light-{{ $isOwner ? 'warning' : 'primary' }} text-{{ $isOwner ? 'warning' : 'primary' }} fs-5 fw-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold fs-6">{{ $user->name }}</span>
                                    <span class="text-gray-500 fs-7">{{ $user->email }}</span>
                                </div>
                                @if($isOwner)
                                    <span class="badge badge-light-warning ms-3">
                                        <i class="bi bi-star-fill text-warning me-1"></i>{{ __('messages.owner') }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(!$isOwner)
                                <select class="form-select form-select-sm form-select-solid w-125px" 
                                        onchange="updateUserRole({{ $workspace->id }}, {{ $user->id }}, this.value)">
                                    @foreach($workspace->roles as $r)
                                        <option value="{{ $r->id }}" {{ $role && $role->id === $r->id ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="badge badge-light-primary fs-7">Admin</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-gray-600 fs-7">{{ $user->pivot->created_at ? \Carbon\Carbon::parse($user->pivot->created_at)->format('d M Y') : 'N/A' }}</span>
                        </td>
                        <td class="text-end pe-4">
                            @if(!$isOwner)
                                <button type="button" 
                                        class="btn btn-sm btn-icon btn-light-danger"
                                        data-confirm="true"
                                        data-confirm-title="{{ __('messages.confirm_title') }}"
                                        data-confirm-text="{{ __('messages.confirm_remove_user') }}"
                                        data-confirm-button="{{ __('messages.confirm_button') }}"
                                        data-confirm-method="delete"
                                        data-confirm-url="{{ route('workspaces.users.remove', [$workspace, $user]) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Invite User Modal --}}
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('workspaces.invite', $workspace) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>{{ __('messages.invite_user') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required">{{ __('messages.email') }}</label>
                        <input type="email" class="form-control form-control-solid" name="email" required placeholder="{{ __('messages.email_placeholder') }}">
                    </div>
                    <div class="mb-5">
                        <label class="form-label required">{{ __('messages.role') }}</label>
                        <select class="form-select form-select-solid" name="role_id" required>
                            @foreach($workspace->roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>{{ __('messages.invite') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateUserRole(workspaceId, userId, roleId) {
    axios.put(`/workspaces/${workspaceId}/users/${userId}/role`, {
        role_id: roleId
    }).then(response => {
        showSuccess('{{ __("messages.role_updated") }}');
    }).catch(error => {
        showError('{{ __("messages.error") }}');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Search filter
    const searchInput = document.getElementById('member-search');
    const rows = document.querySelectorAll('.member-row');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        rows.forEach(row => {
            const name = row.dataset.name;
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });
});
</script>
@endpush
