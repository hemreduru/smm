@extends('layouts.app')

@section('toolbar')
    <!--begin::Page title-->
    <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
        data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
        class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
        <!--begin::Title-->
        <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">{{ __('messages.dashboard') }}</h1>
        <!--end::Title-->
    </div>
    <!--end::Page title-->
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('messages.welcome') }}</h3>
        </div>
        <div class="card-body">
            <p>{{ __('messages.metronic_integrated') }}</p>
        </div>
    </div>
@endsection