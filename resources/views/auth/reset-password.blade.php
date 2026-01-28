@extends('layouts.guest')

@section('content')
    <!--begin::Authentication - Reset Password -->
    <div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed"
        style="background-image: url({{ asset('assets/media/illustrations/sketchy-1/14.png') }})">
        <!--begin::Content-->
        <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
            <!--begin::Logo-->
            <a href="{{ url('/') }}" class="mb-12">
                <img alt="Logo" src="{{ asset('assets/media/logos/logo-1.svg') }}" class="h-40px" />
            </a>
            <!--end::Logo-->
            <!--begin::Wrapper-->
            <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                <!--begin::Form-->
                <form class="form w-100" novalidate="novalidate" id="kt_new_password_form" method="POST"
                    action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <!--begin::Heading-->
                    <div class="text-center mb-10">
                        <!--begin::Title-->
                        <h1 class="text-dark mb-3">{{ __('messages.reset_password_title') }}</h1>
                        <!--end::Title-->
                        <!--begin::Link-->
                        <div class="text-gray-400 fw-bold fs-4">
                            {{ __('messages.reset_password_description') }}
                        </div>
                        <!--end::Link-->
                    </div>
                    <!--begin::Heading-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <label class="form-label fs-6 fw-bolder text-dark">{{ __('messages.email') }}</label>
                        <input class="form-control form-control-lg form-control-solid @error('email') is-invalid @enderror"
                            type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required
                            readonly />
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <label class="form-label fs-6 fw-bolder text-dark">{{ __('messages.new_password') }}</label>
                        <input
                            class="form-control form-control-lg form-control-solid @error('password') is-invalid @enderror"
                            type="password" name="password" autocomplete="new-password" required
                            placeholder="{{ __('messages.new_password_placeholder') }}" />
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <label class="form-label fs-6 fw-bolder text-dark">{{ __('messages.confirm_password') }}</label>
                        <input class="form-control form-control-lg form-control-solid" type="password"
                            name="password_confirmation" autocomplete="new-password" required
                            placeholder="{{ __('messages.confirm_password_placeholder') }}" />
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center">
                        <button type="submit" id="kt_new_password_submit" class="btn btn-lg btn-primary fw-bolder">
                            <span class="indicator-label">{{ __('messages.reset_password') }}</span>
                            <span class="indicator-progress">{{ __('messages.please_wait') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Content-->
        <!--begin::Footer-->
        <div class="d-flex flex-center flex-column-auto p-10">
            <div class="d-flex align-items-center fw-bold fs-6">
                <a href="#" class="text-muted text-hover-primary px-2">{{ __('messages.about') }}</a>
                <a href="#" class="text-muted text-hover-primary px-2">{{ __('messages.contact') }}</a>
            </div>
        </div>
        <!--end::Footer-->
    </div>
    <!--end::Authentication - Reset Password-->
@endsection