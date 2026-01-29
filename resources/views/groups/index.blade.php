@extends('layouts.app')

@section('title', __('messages.account_groups'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.account_groups') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ __('messages.account_groups') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    {{-- Summary Card --}}
    <div class="row g-5 g-xl-8 mb-5">
        <div class="col-xl-4">
            <div class="card card-flush bg-primary h-lg-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <span class="text-white fw-bold fs-1">{{ $groupsCount }}</span>
                        <span class="text-white opacity-75 fs-6">{{ __('messages.account_groups') }}</span>
                    </div>
                    <i class="bi bi-collection text-white opacity-50 fs-3x"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Groups DataTable --}}
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
                <a href="{{ route('groups.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg fs-4 me-1"></i>
                    {{ __('messages.create_group') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="groups-table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-150px">{{ __('messages.group_name') }}</th>
                            <th class="min-w-125px">{{ __('messages.group_accounts') }}</th>
                            <th class="min-w-80px">{{ __('messages.status') }}</th>
                            <th class="min-w-100px">{{ __('messages.created_at') }}</th>
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
    const table = initDataTable('#groups-table', {
        serverSide: false,
        ajax: {
            url: '{{ route('groups.data') }}',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function(data) {
                    return `<div class="d-flex align-items-center">
                        <div class="symbol symbol-45px me-3">
                            <div class="symbol-label bg-light-primary">
                                <i class="bi bi-collection text-primary fs-3"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <a href="/groups/${data.id}" class="text-gray-800 fw-bold text-hover-primary">${data.name}</a>
                            ${data.description ? `<span class="text-gray-500 fs-7">${data.description.substring(0, 50)}${data.description.length > 50 ? '...' : ''}</span>` : ''}
                        </div>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    const platforms = data.platforms || [];
                    let badges = platforms.map(p => {
                        const icons = {
                            'instagram': 'bi-instagram',
                            'tiktok': 'bi-tiktok',
                            'youtube_shorts': 'bi-youtube'
                        };
                        return `<span class="badge badge-light me-1"><i class="bi ${icons[p] || 'bi-globe'}"></i></span>`;
                    }).join('');
                    return `<div class="d-flex align-items-center">
                        <span class="badge badge-light-primary me-2">${data.accounts_count} {{ __('messages.accounts') }}</span>
                        ${badges}
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return data.status
                        ? '<span class="badge badge-light-success">{{ __('messages.active') }}</span>'
                        : '<span class="badge badge-light-secondary">{{ __('messages.inactive') }}</span>';
                }
            },
            {
                data: 'created_at',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString() : '-';
                }
            },
            {
                data: 'actions',
                orderable: false
            }
        ],
        order: [[0, 'asc']]
    });

    // Search handler
    bindSearch(table, '[data-table-filter="search"]');
});
</script>
@endpush
