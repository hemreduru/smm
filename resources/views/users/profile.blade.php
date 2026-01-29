@extends('layouts.app')

@section('title', __('messages.profile'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.profile') }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ __('messages.profile') }}</li>
        </ul>
    </div>
@endsection

@section('content')
{{-- Profile Header Card --}}
<div class="card mb-5 mb-xl-10">
    <div class="card-body pt-9 pb-0">
        <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
            {{-- Avatar --}}
            <div class="me-7 mb-4">
                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                    <span class="symbol-label bg-light-primary text-primary fs-1 fw-bold" style="font-size: 3rem !important;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                </div>
            </div>
            
            {{-- Info --}}
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <h2 class="text-gray-900 fs-2 fw-bold me-3">{{ auth()->user()->name }}</h2>
                            @if(auth()->user()->email_verified_at)
                                <span class="badge badge-light-success fs-8">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('messages.verified') }}
                                </span>
                            @else
                                <span class="badge badge-light-warning fs-8">
                                    <i class="bi bi-clock me-1"></i>{{ __('messages.not_verified') }}
                                </span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                            <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                <i class="bi bi-envelope fs-4 me-1"></i>
                                {{ auth()->user()->email }}
                            </span>
                            <span class="d-flex align-items-center text-gray-500 mb-2">
                                <i class="bi bi-calendar3 fs-4 me-1"></i>
                                {{ __('messages.joined') ?? 'Joined' }}: {{ auth()->user()->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                
                {{-- Stats --}}
                <div class="d-flex flex-wrap flex-stack">
                    <div class="d-flex flex-column flex-grow-1 pe-8">
                        <div class="d-flex flex-wrap">
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-briefcase fs-3 text-primary me-2"></i>
                                    <span class="fs-2 fw-bold text-gray-800">{{ auth()->user()->workspaces()->count() }}</span>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-500">{{ __('messages.workspaces') }}</div>
                            </div>
                            @if(auth()->user()->currentWorkspace)
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle fs-3 text-success me-2"></i>
                                        <span class="fs-6 fw-bold text-gray-800">{{ auth()->user()->currentWorkspace->name }}</span>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-500">{{ __('messages.current_workspace') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Tab Navigation --}}
        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
            <li class="nav-item mt-2">
                <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="#profile_details" data-bs-toggle="tab">
                    <i class="bi bi-person me-2"></i>{{ __('messages.profile') ?? 'Profile' }}
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link text-active-primary ms-0 me-10 py-5" href="#security" data-bs-toggle="tab">
                    <i class="bi bi-shield-lock me-2"></i>{{ __('messages.security') ?? 'Security' }}
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- Tab Content --}}
<div class="tab-content">
    {{-- Profile Details Tab --}}
    <div class="tab-pane fade show active" id="profile_details">
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">{{ __('messages.profile_details') ?? 'Profile Details' }}</h3>
                </div>
            </div>
            <div class="card-body border-top p-9">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">{{ __('messages.name') }}</label>
                        <div class="col-lg-8">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid" 
                                   value="{{ old('name', auth()->user()->name) }}" required />
                            @error('name')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">{{ __('messages.email') }}</label>
                        <div class="col-lg-8">
                            <input type="email" name="email" class="form-control form-control-lg form-control-solid" 
                                   value="{{ old('email', auth()->user()->email) }}" required />
                            @error('email')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-0">
                        <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('messages.email_verification_status') }}</label>
                        <div class="col-lg-8 d-flex align-items-center">
                            @if(auth()->user()->email_verified_at)
                                <span class="badge badge-light-success fs-7">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('messages.verified') }}
                                </span>
                                <span class="text-gray-500 fs-7 ms-3">{{ auth()->user()->email_verified_at->format('d M Y H:i') }}</span>
                            @else
                                <span class="badge badge-light-warning fs-7">
                                    <i class="bi bi-clock me-1"></i>{{ __('messages.not_verified') }}
                                </span>
                                <form action="{{ route('verification.send') }}" method="POST" class="ms-3">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light-primary">
                                        {{ __('messages.resend_verification') ?? 'Resend' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    <div class="separator my-6"></div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>{{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Security Tab --}}
    <div class="tab-pane fade" id="security">
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">{{ __('messages.change_password') ?? 'Change Password' }}</h3>
                </div>
            </div>
            <div class="card-body border-top p-9">
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">{{ __('messages.current_password') ?? 'Current Password' }}</label>
                        <div class="col-lg-8">
                            <input type="password" name="current_password" class="form-control form-control-lg form-control-solid" required />
                            @error('current_password')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">{{ __('messages.new_password') ?? 'New Password' }}</label>
                        <div class="col-lg-8">
                            <input type="password" name="password" class="form-control form-control-lg form-control-solid" required />
                            @error('password')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">{{ __('messages.confirm_password') ?? 'Confirm Password' }}</label>
                        <div class="col-lg-8">
                            <input type="password" name="password_confirmation" class="form-control form-control-lg form-control-solid" required />
                        </div>
                    </div>
                    
                    <div class="separator my-6"></div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-1"></i>{{ __('messages.update_password') ?? 'Update Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
