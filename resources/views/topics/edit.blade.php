@extends('layouts.app')

@section('title', __('messages.topics.edit'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.topics.edit') }}
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
            <li class="breadcrumb-item text-muted">{{ __('messages.edit') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">{{ __('messages.topics.edit') }}</h3>
            <div class="card-toolbar">
                @include('topics.partials.status-badge', ['topic' => $topic])
            </div>
        </div>

        <form action="{{ route('topics.update', $topic) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                {{-- Title --}}
                <div class="fv-row mb-7">
                    <label class="required fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.title') }}</label>
                    <input type="text" class="form-control form-control-solid @error('title') is-invalid @enderror"
                           name="title" value="{{ old('title', $topic->title) }}"
                           placeholder="{{ __('messages.topics.placeholders.title') }}" required />
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="fv-row mb-7">
                    <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.description') }}</label>
                    <textarea class="form-control form-control-solid @error('description') is-invalid @enderror"
                              name="description" rows="4"
                              placeholder="{{ __('messages.topics.placeholders.description') }}">{{ old('description', $topic->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    {{-- Niche --}}
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.niche') }}</label>
                            <input type="text" class="form-control form-control-solid @error('niche') is-invalid @enderror"
                                   name="niche" value="{{ old('niche', $topic->niche) }}"
                                   placeholder="{{ __('messages.topics.placeholders.niche') }}" />
                            @error('niche')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Keywords --}}
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.keywords') }}</label>
                            <input type="text" class="form-control form-control-solid @error('keywords') is-invalid @enderror"
                                   name="keywords" value="{{ old('keywords', is_array($topic->keywords) ? implode(', ', $topic->keywords) : $topic->keywords) }}"
                                   placeholder="{{ __('messages.topics.placeholders.keywords') }}" />
                            @error('keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('messages.topics.validation.max_keywords') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Schedule --}}
                <div class="fv-row mb-7">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="is_scheduled" id="isScheduled"
                               value="1" {{ old('is_scheduled', $topic->is_scheduled) ? 'checked' : '' }} />
                        <label class="form-check-label fw-semibold text-gray-700" for="isScheduled">
                            {{ __('messages.schedule_for_later') ?? 'Schedule for later' }}
                        </label>
                    </div>
                </div>

                <div class="fv-row mb-7" id="scheduleWrapper" style="{{ old('is_scheduled', $topic->is_scheduled) ? '' : 'display: none;' }}">
                    <label class="fs-6 fw-semibold mb-2">{{ __('messages.topics.fields.scheduled_at') }}</label>
                    <input type="datetime-local" class="form-control form-control-solid @error('scheduled_at') is-invalid @enderror"
                           name="scheduled_at"
                           value="{{ old('scheduled_at', $topic->scheduled_at?->format('Y-m-d\TH:i')) }}" />
                    @error('scheduled_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('topics.show', $topic) }}" class="btn btn-light">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ __('messages.update') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isScheduledCheckbox = document.getElementById('isScheduled');
    const scheduleWrapper = document.getElementById('scheduleWrapper');

    function toggleSchedule() {
        scheduleWrapper.style.display = isScheduledCheckbox.checked ? 'block' : 'none';
    }

    isScheduledCheckbox.addEventListener('change', toggleSchedule);
});
</script>
@endpush
