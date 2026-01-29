@extends('layouts.app')

@section('title', __('messages.edit_content'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.edit_content') }}
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
            <li class="breadcrumb-item text-muted">{{ __('messages.edit_content') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <form action="{{ route('contents.update', $content) }}" method="POST" id="content-form">
        @csrf
        @method('PUT')

        <div class="row g-5 g-xl-8">
            {{-- Main Content --}}
            <div class="col-xl-8">
                {{-- Video Preview Card --}}
                <div class="card card-flush mb-5">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('messages.video') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <video class="w-100" controls style="max-height: 400px; background: #000;">
                            <source src="{{ $content->getVideoUrl() }}" type="video/mp4">
                        </video>
                    </div>
                    <div class="card-footer py-3">
                        <div class="d-flex align-items-center text-gray-500 fs-7">
                            @if($content->duration)
                                <i class="bi bi-clock me-1"></i>
                                <span class="me-4">{{ $content->formatted_duration }}</span>
                            @endif
                            @if($content->file_size)
                                <i class="bi bi-hdd me-1"></i>
                                <span>{{ $content->formatted_file_size }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Captions Card --}}
                <div class="card card-flush mb-5">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('messages.caption') }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- Title --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.name') }}</label>
                            <input type="text" class="form-control form-control-solid @error('title') is-invalid @enderror"
                                   name="title" value="{{ old('title', $content->title) }}"
                                   placeholder="{{ __('messages.name') }}"/>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Caption TR --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.caption_tr') }}</label>
                            <textarea class="form-control form-control-solid @error('caption_tr') is-invalid @enderror"
                                      name="caption_tr" rows="4"
                                      placeholder="{{ __('messages.caption_tr') }}"
                                      maxlength="2200">{{ old('caption_tr', $content->caption_tr) }}</textarea>
                            <div class="form-text text-end"><span id="caption-tr-count">{{ strlen($content->caption_tr ?? '') }}</span>/2200</div>
                            @error('caption_tr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Caption EN --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.caption_en') }}</label>
                            <textarea class="form-control form-control-solid @error('caption_en') is-invalid @enderror"
                                      name="caption_en" rows="4"
                                      placeholder="{{ __('messages.caption_en') }}"
                                      maxlength="2200">{{ old('caption_en', $content->caption_en) }}</textarea>
                            <div class="form-text text-end"><span id="caption-en-count">{{ strlen($content->caption_en ?? '') }}</span>/2200</div>
                            @error('caption_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Hashtags --}}
                        <div class="fv-row">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.hashtags') }}</label>
                            <textarea class="form-control form-control-solid @error('hashtags') is-invalid @enderror"
                                      name="hashtags" rows="2"
                                      placeholder="#hashtag1 #hashtag2 #hashtag3">{{ old('hashtags', $content->hashtags_string) }}</textarea>
                            @error('hashtags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
                        <span class="badge {{ $content->status->badgeClass() }} fs-6 px-4 py-3">
                            <i class="bi {{ $content->status->icon() }} me-1"></i>
                            {{ $content->status->label() }}
                        </span>
                    </div>
                </div>

                {{-- Publishing Options Card --}}
                <div class="card card-flush mb-5">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('messages.schedule') }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- Account Group --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.select_group') }}</label>
                            <select class="form-select form-select-solid @error('account_group_id') is-invalid @enderror"
                                    name="account_group_id">
                                <option value="">{{ __('messages.no_group') }}</option>
                                @foreach($accountGroups as $group)
                                    <option value="{{ $group->id }}" {{ old('account_group_id', $content->account_group_id) == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }} ({{ $group->accounts_count }} {{ __('messages.accounts') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('account_group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Schedule --}}
                        <div class="fv-row">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.scheduled_at') }}</label>
                            <input type="datetime-local" class="form-control form-control-solid @error('scheduled_at') is-invalid @enderror"
                                   name="scheduled_at"
                                   value="{{ old('scheduled_at', $content->scheduled_at?->format('Y-m-d\TH:i')) }}"
                                   min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"/>
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Notes Card --}}
                <div class="card card-flush mb-5">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('messages.internal_notes') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="fv-row">
                            <textarea class="form-control form-control-solid @error('notes') is-invalid @enderror"
                                      name="notes" rows="4"
                                      placeholder="{{ __('messages.internal_notes') }}">{{ old('notes', $content->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="card card-flush">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                {{ __('messages.update') }}
                            </button>
                            <a href="{{ route('contents.show', $content) }}" class="btn btn-light">
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counters
    const captionTr = document.querySelector('textarea[name="caption_tr"]');
    const captionEn = document.querySelector('textarea[name="caption_en"]');
    const captionTrCount = document.getElementById('caption-tr-count');
    const captionEnCount = document.getElementById('caption-en-count');

    captionTr.addEventListener('input', () => captionTrCount.textContent = captionTr.value.length);
    captionEn.addEventListener('input', () => captionEnCount.textContent = captionEn.value.length);
});
</script>
@endpush
