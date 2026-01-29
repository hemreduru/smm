@extends('layouts.app')

@section('title', __('messages.create_content'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.create_content') }}
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
            <li class="breadcrumb-item text-muted">{{ __('messages.create_content') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <form action="{{ route('contents.store') }}" method="POST" enctype="multipart/form-data" id="content-form">
        @csrf

        <div class="row g-5 g-xl-8">
            {{-- Main Content --}}
            <div class="col-xl-8">
                {{-- Video Upload Card --}}
                <div class="card card-flush mb-5">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('messages.upload_video') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="fv-row">
                            <div class="dropzone dropzone-queue" id="video-dropzone">
                                <div class="dropzone-panel mb-lg-0 mb-2">
                                    <div class="dz-message needsclick">
                                        <i class="bi bi-cloud-arrow-up text-primary fs-3x mb-3"></i>
                                        <div class="ms-4">
                                            <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __('messages.drag_drop_video') }}</h3>
                                            <span class="fs-7 text-gray-500">{{ __('messages.supported_formats') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropzone-items wm-200px">
                                    <div class="dropzone-item p-5" style="display:none">
                                        <div class="dropzone-file">
                                            <div class="dropzone-filename text-gray-900" title="some_image_file_name.jpg">
                                                <span data-dz-name>some_image_file_name.jpg</span>
                                                <strong>(<span data-dz-size>340kb</span>)</strong>
                                            </div>
                                            <div class="dropzone-error mt-0" data-dz-errormessage></div>
                                        </div>
                                        <div class="dropzone-progress">
                                            <div class="progress bg-gray-300">
                                                <div class="progress-bar bg-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-dz-uploadprogress></div>
                                            </div>
                                        </div>
                                        <div class="dropzone-toolbar">
                                            <span class="dropzone-delete" data-dz-remove><i class="bi bi-x fs-1"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="file" name="video" id="video-input" accept="video/mp4,video/mov,video/avi,video/webm" class="d-none" required>
                            @error('video')
                                <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Video Preview --}}
                        <div id="video-preview" class="mt-5 d-none">
                            <video id="preview-player" class="w-100 rounded" controls style="max-height: 400px;"></video>
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
                                   name="title" value="{{ old('title') }}"
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
                                      maxlength="2200">{{ old('caption_tr') }}</textarea>
                            <div class="form-text text-end"><span id="caption-tr-count">0</span>/2200</div>
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
                                      maxlength="2200">{{ old('caption_en') }}</textarea>
                            <div class="form-text text-end"><span id="caption-en-count">0</span>/2200</div>
                            @error('caption_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Hashtags --}}
                        <div class="fv-row">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.hashtags') }}</label>
                            <textarea class="form-control form-control-solid @error('hashtags') is-invalid @enderror"
                                      name="hashtags" rows="2"
                                      placeholder="#hashtag1 #hashtag2 #hashtag3">{{ old('hashtags') }}</textarea>
                            @error('hashtags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-xl-4">
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
                                    <option value="{{ $group->id }}" {{ old('account_group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }} ({{ $group->accounts_count }} {{ __('messages.accounts') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('account_group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Schedule --}}
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ __('messages.scheduled_at') }}</label>
                            <input type="datetime-local" class="form-control form-control-solid @error('scheduled_at') is-invalid @enderror"
                                   name="scheduled_at" value="{{ old('scheduled_at') }}"
                                   min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"/>
                            <div class="form-text">{{ __('messages.scheduled_at_must_be_future') }}</div>
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
                                      placeholder="{{ __('messages.internal_notes') }}">{{ old('notes') }}</textarea>
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
                                {{ __('messages.save') }}
                            </button>
                            <a href="{{ route('contents.index') }}" class="btn btn-light">
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
    const dropzone = document.getElementById('video-dropzone');
    const fileInput = document.getElementById('video-input');
    const videoPreview = document.getElementById('video-preview');
    const previewPlayer = document.getElementById('preview-player');
    const dropzoneItems = dropzone.querySelector('.dropzone-items');
    const dropzoneItem = dropzone.querySelector('.dropzone-item');

    // Click to upload
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop handlers
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.add('dropzone-primary'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.remove('dropzone-primary'), false);
    });

    dropzone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    }

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });

    function handleFile(file) {
        // Validate file type
        const validTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'];
        if (!validTypes.includes(file.type)) {
            if (typeof window.showError === 'function') {
                window.showError('{{ __('messages.video_invalid_format') }}');
            }
            return;
        }

        // Validate file size (500MB)
        if (file.size > 512000 * 1024) {
            if (typeof window.showError === 'function') {
                window.showError('{{ __('messages.video_too_large') }}');
            }
            return;
        }

        // Update file input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Show file info
        dropzoneItem.querySelector('[data-dz-name]').textContent = file.name;
        dropzoneItem.querySelector('[data-dz-size]').textContent = formatFileSize(file.size);
        dropzoneItem.style.display = 'block';

        // Preview video
        const url = URL.createObjectURL(file);
        previewPlayer.src = url;
        videoPreview.classList.remove('d-none');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Remove file
    dropzoneItem.querySelector('[data-dz-remove]').addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        dropzoneItem.style.display = 'none';
        videoPreview.classList.add('d-none');
        previewPlayer.src = '';
    });

    // Character counters
    const captionTr = document.querySelector('textarea[name="caption_tr"]');
    const captionEn = document.querySelector('textarea[name="caption_en"]');
    const captionTrCount = document.getElementById('caption-tr-count');
    const captionEnCount = document.getElementById('caption-en-count');

    captionTr.addEventListener('input', () => captionTrCount.textContent = captionTr.value.length);
    captionEn.addEventListener('input', () => captionEnCount.textContent = captionEn.value.length);

    // Initialize counts
    captionTrCount.textContent = captionTr.value.length;
    captionEnCount.textContent = captionEn.value.length;
});
</script>
@endpush

@push('styles')
<style>
    #video-dropzone {
        border: 2px dashed var(--bs-gray-300);
        border-radius: 0.75rem;
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    #video-dropzone:hover {
        border-color: var(--bs-primary);
        background-color: var(--bs-light-primary);
    }
    #video-dropzone.dropzone-primary {
        border-color: var(--bs-primary);
        background-color: var(--bs-light-primary);
    }
    #video-dropzone .dz-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
