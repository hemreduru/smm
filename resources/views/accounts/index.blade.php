@extends('layouts.app')

@section('title', __('messages.connected_accounts'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.connected_accounts') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ __('messages.connected_accounts') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    {{-- Platform Summary Cards --}}
    <div class="row g-5 g-xl-8 mb-5">
        @foreach($platforms as $platform)
            <div class="col-xl-4">
                <div class="card card-flush h-lg-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">{{ $platform->label() }}</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $platformCounts[$platform->value] ?? 0 }} {{ __('messages.connected_accounts') }}</span>
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge {{ $platform->badgeClass() }} fs-6">
                                <i class="bi {{ $platform->icon() }} me-1"></i>
                                {{ $platform->label() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body pt-5">
                        <a href="{{ route('accounts.create', ['platform' => $platform->value]) }}" 
                           class="btn btn-sm btn-light-primary w-100">
                            <i class="bi bi-plus-lg"></i>
                            {{ __('messages.connect_account') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Accounts DataTable --}}
    <div class="card card-flush">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="bi bi-search fs-3 position-absolute ms-5"></i>
                    <input type="text" data-table-filter="search" class="form-control form-control-solid w-250px ps-12" 
                           placeholder="{{ __('messages.search') }}..."/>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light-primary" data-filter-menu="true">
                        <i class="bi bi-funnel fs-4 me-2"></i>
                        {{ __('messages.filter') }}
                    </button>
                    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg fs-4 me-1"></i>
                        {{ __('messages.connect_account') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="accounts-table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-check-all="true" value="1"/>
                                </div>
                            </th>
                            <th class="min-w-100px">{{ __('messages.platform') }}</th>
                            <th class="min-w-150px">{{ __('messages.username') }}</th>
                            <th class="min-w-100px">{{ __('messages.status') }}</th>
                            <th class="min-w-100px">{{ __('messages.last_synced') }}</th>
                            <th class="text-end min-w-100px">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = $('#accounts-table').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route('accounts.data') }}',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                if (typeof window.showError === 'function') {
                    window.showError('{{ __('messages.datatable_load_error') }}');
                }
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `<div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${data.id}"/>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `<div class="d-flex align-items-center">
                        <span class="badge ${data.platform_badge} me-2">
                            <i class="bi ${data.platform_icon} me-1"></i>
                            ${data.platform_label}
                        </span>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `<div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                            ${data.profile_picture_url 
                                ? `<img src="${data.profile_picture_url}" alt="${data.username}" class="w-100"/>` 
                                : `<div class="symbol-label fs-6 bg-light-primary text-primary fw-bold">${data.username.charAt(0).toUpperCase()}</div>`
                            }
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold text-hover-primary">${data.username}</span>
                            ${data.display_name ? `<span class="text-muted fs-7">${data.display_name}</span>` : ''}
                        </div>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    const healthIcon = data.is_healthy 
                        ? '<i class="bi bi-check-circle text-success me-1"></i>' 
                        : '<i class="bi bi-exclamation-circle text-warning me-1"></i>';
                    return `<span class="badge ${data.status_badge}">${healthIcon}${data.status_label}</span>`;
                }
            },
            {
                data: 'last_synced_at',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString() : '-';
                }
            },
            {
                data: 'actions',
                orderable: false
            }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<span class="spinner-border spinner-border-sm align-middle"></span> {{ __('messages.loading') }}',
            emptyTable: '{{ __('messages.no_records_found') }}',
            zeroRecords: '{{ __('messages.no_matching_records') }}'
        }
    });

    // Search handler
    const searchInput = document.querySelector('[data-table-filter="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            table.search(this.value).draw();
        });
    }
});
</script>
@endpush
