@extends('layouts.guest')

@section('content')
    <!--begin::Authentication - Verify Email -->
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
            <div class="w-lg-600px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                <!--begin::Icon-->
                <div class="text-center mb-10">
                    <span class="svg-icon svg-icon-3x svg-icon-primary">
                        <i class="bi bi-envelope-check fs-3x text-primary"></i>
                    </span>
                </div>
                <!--end::Icon-->
                <!--begin::Heading-->
                <div class="text-center mb-10">
                    <!--begin::Title-->
                    <h1 class="text-dark mb-3">{{ __('messages.verify_email_title') }}</h1>
                    <!--end::Title-->
                    <!--begin::Description-->
                    <div class="text-gray-500 fw-bold fs-5">
                        {{ __('messages.verify_email_description') }}
                    </div>
                    <!--end::Description-->
                </div>
                <!--end::Heading-->
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-10 p-6">
                    <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
                        <i class="bi bi-info-circle text-primary fs-2x"></i>
                    </span>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-bold">
                            <div class="fs-6 text-gray-700">
                                {{ __('messages.verify_email_notice') }}
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Notice-->
                <!--begin::Actions-->
                <div class="d-flex flex-wrap justify-content-center pb-lg-0 gap-3">
                    <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-lg btn-primary fw-bolder">
                            <span class="indicator-label">{{ __('messages.resend_verification') }}</span>
                            <span class="indicator-progress">{{ __('messages.please_wait') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-lg btn-light-danger fw-bolder">
                            {{ __('messages.sign_out') }}
                        </button>
                    </form>
                </div>
                <!--end::Actions-->
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
    <!--end::Authentication - Verify Email-->
@endsection