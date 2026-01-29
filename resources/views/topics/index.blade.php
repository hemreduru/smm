@extends('layouts.app')

@section('title', __('messages.topics.title'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.topics.title') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ __('messages.topics.title') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    {{-- Status Summary Cards --}}
    <div class="row g-5 g-xl-8 mb-5">
        @php
            $statusCards = [
                ['key' => 'total', 'value' => $statistics['total'], 'icon' => 'bi-folder', 'color' => '#7239ea'],
                ['key' => 'draft', 'value' => $statistics['by_status'][\App\Enums\TopicStatus::DRAFT->value] ?? 0, 'icon' => 'bi-file-earmark', 'color' => '#ffc107'],
                ['key' => 'approved', 'value' => $statistics['by_status'][\App\Enums\TopicStatus::APPROVED->value] ?? 0, 'icon' => 'bi-check-circle', 'color' => '#17c653'],
                ['key' => 'pending', 'value' => ($statistics['by_status'][\App\Enums\TopicStatus::SENT_TO_N8N->value] ?? 0) + ($statistics['by_status'][\App\Enums\TopicStatus::PROCESSING->value] ?? 0), 'icon' => 'bi-hourglass-split', 'color' => '#009ef7'],
                ['key' => 'completed', 'value' => $statistics['by_status'][\App\Enums\TopicStatus::COMPLETED->value] ?? 0, 'icon' => 'bi-check2-all', 'color' => '#50cd89'],
                ['key' => 'failed', 'value' => $statistics['by_status'][\App\Enums\TopicStatus::FAILED->value] ?? 0, 'icon' => 'bi-x-circle', 'color' => '#f1416c'],
            ];
        @endphp
        @foreach($statusCards as $card)
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card card-flush h-100 cursor-pointer status-filter-card"
                     data-status="{{ $card['key'] }}">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi {{ $card['icon'] }} fs-2x me-2" style="color: {{ $card['color'] }}"></i>
                            <span class="fs-2hx fw-bold" style="color: {{ $card['color'] }}">{{ $card['value'] }}</span>
                        </div>
                        <span class="text-gray-600 fw-semibold fs-7">{{ __('messages.topics.stats.' . $card['key']) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Topics DataTable --}}
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
                    {{-- AI Generate Button --}}
                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#generateTopicModal">
                        <i class="bi bi-stars fs-4 me-1"></i>
                        {{ __('messages.topics.generate') }}
                    </button>

                    {{-- Manual Create Button --}}
                    <a href="{{ route('topics.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg fs-4 me-1"></i>
                        {{ __('messages.topics.manual_create') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="topics-table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px">{{ __('messages.topics.fields.title') }}</th>
                            <th class="min-w-100px">{{ __('messages.topics.fields.niche') }}</th>
                            <th class="min-w-100px">{{ __('messages.topics.fields.status') }}</th>
                            <th class="min-w-100px">{{ __('messages.topics.fields.ai_provider') }}</th>
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

    {{-- AI Generate Modal --}}
    <div class="modal fade" id="generateTopicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">{{ __('messages.topics.generate_topics') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg fs-1"></i>
                    </div>
                </div>

                <form id="generateTopicForm">
                    <div class="modal-body py-10 px-lg-17">
                        {{-- Niche --}}
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.niche') }}</label>
                            <input type="text" class="form-control form-control-solid" name="niche"
                                   placeholder="{{ __('messages.topics.placeholders.niche') }}" required />
                        </div>

                        {{-- Keywords --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.keywords') }}</label>
                            <input type="text" class="form-control form-control-solid" name="keywords"
                                   placeholder="{{ __('messages.topics.placeholders.keywords') }}" />
                            <div class="form-text">{{ __('messages.topics.validation.max_keywords') }}</div>
                        </div>

                        {{-- AI Provider --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.ai_provider') }}</label>
                            <select class="form-select form-select-solid" name="provider" id="aiProviderSelect">
                                <option value="">{{ __('messages.topics.placeholders.select_provider') }}</option>
                                @foreach($providers as $provider)
                                    @if($provider['available'])
                                        <option value="{{ $provider['value'] }}"
                                                data-models='@json($provider['models'])'>
                                            {{ $provider['label'] }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        {{-- AI Model --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.ai_model') }}</label>
                            <select class="form-select form-select-solid" name="model" id="aiModelSelect" disabled>
                                <option value="">{{ __('messages.topics.placeholders.select_model') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" id="generateBtn">
                            <span class="indicator-label">
                                <i class="bi bi-stars me-1"></i>
                                {{ __('messages.topics.generate') }}
                            </span>
                            <span class="indicator-progress">
                                {{ __('messages.please_wait') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStatus = 'all';

    const table = initDataTable('#topics-table', {
        serverSide: true,
        ajax: {
            url: '{{ route('topics.index') }}',
            data: function(d) {
                d.status = currentStatus !== 'all' ? currentStatus : null;
            }
        },
        columns: [
            {
                data: 'title',
                render: function(data, type, row) {
                    let html = `<a href="/topics/${row.id}" class="text-gray-800 text-hover-primary fw-bold">${data}</a>`;
                    if (row.description) {
                        html += `<div class="text-muted fs-7 text-truncate" style="max-width: 300px;">${row.description}</div>`;
                    }
                    return html;
                }
            },
            {
                data: 'niche',
                render: function(data) {
                    return data ? `<span class="badge badge-light-info">${data}</span>` : '-';
                }
            },
            { data: 'status_badge', orderable: false },
            {
                data: 'ai_provider',
                render: function(data) {
                    return data ? `<span class="badge badge-light">${data}</span>` : '-';
                }
            },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']]
    });

    // Bind search
    bindSearch(table, 'input[data-table-filter="search"]');

    // Status filter cards
    document.querySelectorAll('.status-filter-card').forEach(card => {
        card.addEventListener('click', function() {
            const status = this.dataset.status;
            currentStatus = currentStatus === status ? 'all' : status;

            document.querySelectorAll('.status-filter-card').forEach(c => {
                c.classList.remove('border', 'border-primary');
            });

            if (currentStatus !== 'all') {
                this.classList.add('border', 'border-primary');
            }

            table.ajax.reload();
        });
    });

    // AI Provider/Model selection
    const providerSelect = document.getElementById('aiProviderSelect');
    const modelSelect = document.getElementById('aiModelSelect');

    providerSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const models = selected.dataset.models ? JSON.parse(selected.dataset.models) : [];

        modelSelect.innerHTML = '<option value="">{{ __('messages.topics.placeholders.select_model') }}</option>';

        if (models.length > 0) {
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });
            modelSelect.disabled = false;
            modelSelect.value = models[0]; // Default to first model
        } else {
            modelSelect.disabled = true;
        }
    });

    // Generate form submission
    const generateForm = document.getElementById('generateTopicForm');
    const generateBtn = document.getElementById('generateBtn');

    generateForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {
            niche: formData.get('niche'),
            keywords: formData.get('keywords'),
            provider: formData.get('provider') || null,
            model: formData.get('model') || null
        };

        generateBtn.setAttribute('data-kt-indicator', 'on');
        generateBtn.disabled = true;

        try {
            const response = await fetch('{{ route('topics.generate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                bootstrap.Modal.getInstance(document.getElementById('generateTopicModal')).hide();
                generateForm.reset();
                modelSelect.disabled = true;
                table.ajax.reload();
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('{{ __('messages.error') }}');
        } finally {
            generateBtn.removeAttribute('data-kt-indicator');
            generateBtn.disabled = false;
        }
    });
});
</script>
@endpush
