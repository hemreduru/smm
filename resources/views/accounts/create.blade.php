@extends('layouts.app')

@section('title', __('messages.connect_account'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.connect_account') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('accounts.index') }}" class="text-muted text-hover-primary">{{ __('messages.connected_accounts') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ __('messages.connect_account') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">{{ __('messages.connect_account') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf

                {{-- Platform Selection --}}
                <div class="fv-row mb-7">
                    <label class="required fs-6 fw-semibold mb-2">{{ __('messages.select_platform') }}</label>
                    <div class="row g-4">
                        @foreach($platforms as $platformOption)
                            <div class="col-md-4">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6 
                                    {{ old('platform', $platform) === $platformOption->value ? 'active' : '' }}" 
                                    data-platform-card="{{ $platformOption->value }}">
                                    <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">
                                        <input class="form-check-input" type="radio" name="platform" 
                                               value="{{ $platformOption->value }}" 
                                               {{ old('platform', $platform) === $platformOption->value ? 'checked' : '' }}/>
                                    </span>
                                    <span class="ms-5">
                                        <span class="fs-4 fw-bold mb-1 d-block">
                                            <i class="bi {{ $platformOption->icon() }} me-2" style="color: {{ $platformOption->color() }}"></i>
                                            {{ $platformOption->label() }}
                                        </span>
                                        <span class="fw-semibold fs-7 text-gray-600">
                                            @switch($platformOption->value)
                                                @case('instagram')
                                                    Photo & video sharing platform
                                                    @break
                                                @case('tiktok')
                                                    Short-form video platform
                                                    @break
                                                @case('youtube_shorts')
                                                    YouTube's short video format
                                                    @break
                                            @endswitch
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('platform')
                        <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Username --}}
                <div class="fv-row mb-7">
                    <label class="required fs-6 fw-semibold mb-2">{{ __('messages.username') }}</label>
                    <input type="text" class="form-control form-control-solid @error('username') is-invalid @enderror" 
                           name="username" value="{{ old('username') }}" 
                           placeholder="@username"/>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Enter the username/handle for this account</div>
                </div>

                {{-- Display Name --}}
                <div class="fv-row mb-7">
                    <label class="fs-6 fw-semibold mb-2">{{ __('messages.display_name') }}</label>
                    <input type="text" class="form-control form-control-solid @error('display_name') is-invalid @enderror" 
                           name="display_name" value="{{ old('display_name') }}" 
                           placeholder="Display Name (optional)"/>
                    @error('display_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Note about OAuth --}}
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 mb-7">
                    <i class="bi bi-exclamation-triangle fs-2tx text-warning me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Manual Connection</h4>
                            <div class="fs-6 text-gray-700">
                                This is a placeholder for manual account connection. 
                                OAuth integration with platform APIs will be implemented in the next phase.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('accounts.index') }}" class="btn btn-light me-3">{{ __('messages.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-link-45deg me-1"></i>
                        {{ __('messages.connect_account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-platform-card]').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('[data-platform-card]').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input[type="radio"]').checked = true;
    });
});
</script>
@endpush
