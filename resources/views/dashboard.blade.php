{{-- DEBUG: Updated at 21:37 --}}
@extends('layouts.app')

@section('content')
{{-- Welcome Banner --}}
<div class="card bgi-no-repeat bgi-size-contain bgi-position-x-end mb-5 mb-xl-10" style="background-color: #1e1e2d; background-image: url('/metronic/demo1/dist/assets/media/patterns/vector-1.png')">
    <div class="card-body d-flex align-items-center ps-4 py-10 py-lg-15">
        <div class="m-0">
            <div class="d-flex align-items-center mb-2">
                <h1 class="text-white fw-bold fs-2qx mb-0">{{ __('messages.welcome') ?? 'Welcome' }}, {{ auth()->user()->name }}!</h1>
            </div>
            <p class="text-gray-400 fs-5 fw-semibold m-0">
                {{ __('messages.dashboard_subtitle') ?? 'Manage your social media accounts and content from here.' }}
            </p>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    {{-- Workspaces Stat --}}
    <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-100" 
             style="background-color: #f1416c; background-image: url('/metronic/demo1/dist/assets/media/patterns/vector-1.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ auth()->user()->workspaces()->count() }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('messages.workspaces') }}</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>{{ __('messages.active') ?? 'Active' }}</span>
                        <i class="bi bi-briefcase fs-2 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Current Workspace Stat --}}
    <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-100" 
             style="background-color: #7239ea; background-image: url('/metronic/demo1/dist/assets/media/patterns/vector-1.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    @if(auth()->user()->currentWorkspace)
                        <span class="fs-2x fw-bold text-white me-2 lh-1 ls-n2">{{ auth()->user()->currentWorkspace->name }}</span>
                    @else
                        <span class="fs-2x fw-bold text-white me-2 lh-1 ls-n2">-</span>
                    @endif
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('messages.current_workspace') }}</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        @if(auth()->user()->currentWorkspace)
                            <span>{{ auth()->user()->currentWorkspace->users()->count() }} {{ __('messages.members') }}</span>
                        @else
                            <span>{{ __('messages.select_workspace') ?? 'Select a workspace' }}</span>
                        @endif
                        <i class="bi bi-check-circle fs-2 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Team Members Stat --}}
    <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-100" 
             style="background-color: #009ef7; background-image: url('/metronic/demo1/dist/assets/media/patterns/vector-1.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    @php
                        $totalMembers = auth()->user()->currentWorkspace ? auth()->user()->currentWorkspace->users()->count() : 0;
                    @endphp
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $totalMembers }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('messages.team_members') ?? 'Team Members' }}</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>{{ __('messages.in_workspace') ?? 'In workspace' }}</span>
                        <i class="bi bi-people fs-2 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions Row --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    {{-- Quick Actions Card --}}
    <div class="col-xl-6">
        <div class="card card-flush h-lg-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">{{ __('messages.quick_actions') ?? 'Quick Actions' }}</span>
                </h3>
            </div>
            <div class="card-body pt-5">
                <div class="d-flex flex-stack">
                    <div class="d-flex align-items-center me-5">
                        <div class="symbol symbol-50px me-4">
                            <span class="symbol-label bg-light-primary">
                                <i class="bi bi-briefcase text-primary fs-2"></i>
                            </span>
                        </div>
                        <div class="me-5">
                            <span class="text-gray-800 fw-bold fs-6">{{ __('messages.workspaces') }}</span>
                            <span class="text-gray-500 fw-semibold fs-7 d-block">{{ __('messages.manage_workspaces') ?? 'Manage your workspaces' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('workspaces.index') }}" class="btn btn-sm btn-light-primary">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="separator separator-dashed my-4"></div>
                <div class="d-flex flex-stack">
                    <div class="d-flex align-items-center me-5">
                        <div class="symbol symbol-50px me-4">
                            <span class="symbol-label bg-light-success">
                                <i class="bi bi-people text-success fs-2"></i>
                            </span>
                        </div>
                        <div class="me-5">
                            <span class="text-gray-800 fw-bold fs-6">{{ __('messages.users') }}</span>
                            <span class="text-gray-500 fw-semibold fs-7 d-block">{{ __('messages.manage_team') ?? 'Manage team members' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-light-success">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="separator separator-dashed my-4"></div>
                <div class="d-flex flex-stack">
                    <div class="d-flex align-items-center me-5">
                        <div class="symbol symbol-50px me-4">
                            <span class="symbol-label bg-light-warning">
                                <i class="bi bi-person text-warning fs-2"></i>
                            </span>
                        </div>
                        <div class="me-5">
                            <span class="text-gray-800 fw-bold fs-6">{{ __('messages.profile') ?? 'Profile' }}</span>
                            <span class="text-gray-500 fw-semibold fs-7 d-block">{{ __('messages.update_profile') ?? 'Update your profile' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('profile') }}" class="btn btn-sm btn-light-warning">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Workspaces List Card --}}
    <div class="col-xl-6">
        <div class="card card-flush h-lg-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">{{ __('messages.your_workspaces') ?? 'Your Workspaces' }}</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ auth()->user()->workspaces()->count() }} {{ __('messages.total') ?? 'total' }}</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('workspaces.create') }}" class="btn btn-sm btn-light-primary">
                        <i class="bi bi-plus me-1"></i>{{ __('messages.create') }}
                    </a>
                </div>
            </div>
            <div class="card-body pt-5">
                @forelse(auth()->user()->workspaces()->take(5)->get() as $workspace)
                    <div class="d-flex flex-stack {{ !$loop->last ? 'mb-5' : '' }}">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-{{ auth()->user()->current_workspace_id == $workspace->id ? 'primary' : 'info' }}">
                                    <i class="bi bi-briefcase text-{{ auth()->user()->current_workspace_id == $workspace->id ? 'primary' : 'info' }} fs-4"></i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="{{ route('workspaces.show', $workspace) }}" class="text-gray-800 text-hover-primary fw-bold fs-6">{{ $workspace->name }}</a>
                                <span class="text-gray-500 fw-semibold fs-7">{{ $workspace->users()->count() }} {{ __('messages.members') }}</span>
                            </div>
                        </div>
                        @if(auth()->user()->current_workspace_id == $workspace->id)
                            <span class="badge badge-light-success fs-8">{{ __('messages.current') }}</span>
                        @else
                            <form action="{{ route('workspaces.switch', $workspace) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-icon btn-light-primary">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    @if(!$loop->last)
                        <div class="separator separator-dashed my-3"></div>
                    @endif
                @empty
                    <div class="text-center py-10">
                        <div class="symbol symbol-80px mb-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="bi bi-briefcase text-primary fs-1"></i>
                            </span>
                        </div>
                        <p class="text-gray-500 fs-6 mb-5">{{ __('messages.no_workspaces') }}</p>
                        <a href="{{ route('workspaces.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus me-1"></i>{{ __('messages.create_workspace') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection