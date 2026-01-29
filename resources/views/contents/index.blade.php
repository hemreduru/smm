@extends('layouts.app')

@section('title', __('messages.contents'))

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
    {{-- Status Summary Cards --}}
    <div class="row g-5 g-xl-8 mb-5">
        @foreach($statuses as $status)
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-flush h-100 cursor-pointer status-filter-card {{ request('status') === $status->value ? 'border border-primary' : '' }}"
                     data-status="{{ $status->value }}">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi {{ $status->icon() }} fs-2x me-2" style="color: {{ $status->color() }}"></i>
                            <span class="fs-2hx fw-bold" style="color: {{ $status->color() }}">{{ $statusCounts[$status->value] ?? 0 }}</span>
                        </div>
                        <span class="text-gray-600 fw-semibold fs-7">{{ $status->label() }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Contents DataTable --}}
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
                    {{-- View Toggle --}}
                    <div class="btn-group" role="group">
                        <a href="{{ route('contents.index') }}" class="btn btn-light-primary active" data-bs-toggle="tooltip" title="{{ __('messages.table_view') }}">
                            <i class="bi bi-table fs-4"></i>
                        </a>
                        <a href="{{ route('contents.kanban') }}" class="btn btn-light" data-bs-toggle="tooltip" title="{{ __('messages.kanban_view') }}">
                            <i class="bi bi-kanban fs-4"></i>
                        </a>
                    </div>

                    {{-- Create Button --}}
                    <a href="{{ route('contents.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg fs-4 me-1"></i>
                        {{ __('messages.create_content') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="contents-table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px">{{ __('messages.video') }}</th>
                            <th class="min-w-200px">{{ __('messages.content') }}</th>
                            <th class="min-w-100px">{{ __('messages.status') }}</th>
                            <th class="min-w-100px">{{ __('messages.schedule') }}</th>
                            <th class="min-w-80px">{{ __('messages.account_groups') }}</th>
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
    let currentStatus = 'all';

    const table = initDataTable('#contents-table', {
        serverSide: true,
        ajax: {
            url: '{{ route('contents.data') }}',
            data: function(d) {
                d.status = currentStatus;
            }
        },
        columns: [
            { data: 'thumbnail', orderable: false, searchable: false },
            { data: 'info', orderable: false },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'schedule_info', orderable: false },
            { data: 'account_group.name', defaultContent: '-', orderable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ]
    });

    // Search functionality
    bindSearch(table, '[data-table-filter="search"]');

    // Status filter cards
    document.querySelectorAll('.status-filter-card').forEach(card => {
        card.addEventListener('click', function() {
            const status = this.dataset.status;

            // Toggle selection
            if (currentStatus === status) {
                currentStatus = 'all';
                this.classList.remove('border', 'border-primary');
            } else {
                document.querySelectorAll('.status-filter-card').forEach(c => {
                    c.classList.remove('border', 'border-primary');
                });
                this.classList.add('border', 'border-primary');
                currentStatus = status;
            }

            table.ajax.reload();
        });
    });
});
</script>
@endpush
