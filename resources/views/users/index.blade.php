@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search fs-3 position-absolute ms-4"></i>
                <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="{{ __('messages.search') }}..." />
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                {{-- Filter Dropdown --}}
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="bi bi-funnel me-1"></i>{{ __('messages.filter') ?? 'Filter' }}
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">{{ __('messages.filter_options') ?? 'Filter Options' }}</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        <div class="mb-10">
                            <label class="form-label fw-semibold">{{ __('messages.role') }}:</label>
                            <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Select role" data-allow-clear="true" id="role-filter">
                                <option></option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true">{{ __('messages.cancel') }}</button>
                            <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" id="apply-filter">{{ __('messages.apply') ?? 'Apply' }}</button>
                        </div>
                    </div>
                </div>
                
                {{-- Invite Button --}}
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
                    <i class="bi bi-person-plus me-2"></i>{{ __('messages.invite_user') }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="users-table" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    <th class="ps-4 min-w-300px rounded-start">{{ __('messages.name') }}</th>
                    <th class="min-w-125px">{{ __('messages.role') }}</th>
                    <th class="min-w-125px">{{ __('messages.status') ?? 'Status' }}</th>
                    <th class="min-w-150px">{{ __('messages.joined_at') }}</th>
                    <th class="min-w-100px text-end rounded-end pe-4">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

{{-- Invite User Modal --}}
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('workspaces.invite', $workspaceId) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus text-primary me-2"></i>{{ __('messages.invite_user') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required">{{ __('messages.email') }}</label>
                        <input type="email" class="form-control form-control-solid" name="email" required placeholder="{{ __('messages.email_placeholder') ?? 'Enter email address' }}">
                    </div>
                    <div class="mb-5">
                        <label class="form-label required">{{ __('messages.role') }}</label>
                        <select class="form-select form-select-solid" name="role_id" required>
                            @foreach($roles as $role)
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
$(document).ready(function() {
    let table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("users.data") }}',
            data: function(d) {
                d.role_id = $('#role-filter').val();
            }
        },
        columns: [
            { 
                data: 'name', 
                name: 'name',
                render: function(data, type, row) {
                    let avatarColor = row.is_owner ? 'warning' : 'primary';
                    let ownerBadge = row.is_owner ? '<span class="badge badge-light-warning ms-2"><i class="bi bi-star-fill text-warning me-1"></i>{{ __("messages.owner") }}</span>' : '';
                    
                    return `<div class="d-flex align-items-center">
                        <div class="symbol symbol-45px me-4">
                            <span class="symbol-label bg-light-${avatarColor} text-${avatarColor} fs-5 fw-bold">
                                ${data.charAt(0).toUpperCase()}
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-900 fw-bold fs-6">${data}</span>
                            <span class="text-gray-500 fs-7">${row.email}</span>
                        </div>
                        ${ownerBadge}
                    </div>`;
                }
            },
            { 
                data: 'role', 
                name: 'role', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    let roleColors = {'Admin': 'primary', 'Editor': 'warning', 'Viewer': 'info'};
                    let color = roleColors[data] || 'secondary';
                    return `<span class="badge badge-light-${color} fs-7">${data || 'N/A'}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (row.email_verified_at) {
                        return '<span class="badge badge-light-success fs-8"><i class="bi bi-check-circle me-1"></i>{{ __("messages.verified") }}</span>';
                    } else {
                        return '<span class="badge badge-light-warning fs-8"><i class="bi bi-clock me-1"></i>{{ __("messages.not_verified") }}</span>';
                    }
                }
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data, type, row) {
                    return `<span class="text-gray-600 fs-7">${data}</span>`;
                }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                className: 'text-end pe-4'
            }
        ],
        language: {
            url: '{{ app()->getLocale() === "tr" ? "//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json" : "" }}'
        },
        dom: 'rt<"row align-items-center"<"col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start"li><"col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end"p>>',
        pageLength: 10,
        order: [[3, 'desc']]
    });

    // Search handler
    $('[data-kt-user-table-filter="search"]').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Filter handler
    $('#apply-filter').on('click', function() {
        table.ajax.reload();
    });
});
</script>
@endpush
