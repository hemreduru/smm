@extends('layouts.app')

@section('title', $group->name)

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ $group->name }}
        </h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('messages.dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('groups.index') }}" class="text-muted text-hover-primary">{{ __('messages.account_groups') }}</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-gray-500 w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">{{ $group->name }}</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a href="{{ route('groups.edit', $group) }}" class="btn btn-sm btn-light-primary">
            <i class="bi bi-pencil me-1"></i>
            {{ __('messages.edit') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-8">
        {{-- Group Details --}}
        <div class="col-xl-8">
            <div class="card card-flush mb-5">
                <div class="card-header pt-7">
                    <h3 class="card-title">
                        <span class="card-label fw-bold text-gray-900">{{ __('messages.group_accounts') }}</span>
                        <span class="text-muted ms-2 fs-6">({{ $group->accounts->count() }} accounts)</span>
                    </h3>
                </div>
                <div class="card-body">
                    @if($group->accounts->count() > 0)
                        <div class="row g-5">
                            @foreach($group->accounts as $account)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card card-bordered h-100">
                                        <div class="card-body p-5">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="symbol symbol-50px me-3">
                                                    @if($account->profile_picture_url)
                                                        <img src="{{ $account->profile_picture_url }}" alt="{{ $account->username }}"/>
                                                    @else
                                                        <div class="symbol-label bg-light-primary text-primary fw-bold fs-5">
                                                            {{ strtoupper(substr($account->username, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <a href="{{ route('accounts.show', $account) }}" 
                                                       class="text-gray-800 fw-bold text-hover-primary fs-6">
                                                        {{ $account->username }}
                                                    </a>
                                                    <span class="badge {{ $account->platform->badgeClass() }} fs-8">
                                                        <i class="bi {{ $account->platform->icon() }} me-1"></i>
                                                        {{ $account->platform->label() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="badge {{ $account->status->badgeClass() }}">
                                                    {{ $account->status->label() }}
                                                </span>
                                                <a href="{{ route('accounts.show', $account) }}" 
                                                   class="btn btn-sm btn-icon btn-light-primary">
                                                    <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-15">
                            <i class="bi bi-inbox text-gray-400 fs-3x mb-5"></i>
                            <div class="text-gray-600 fs-5 fw-semibold mb-3">No accounts in this group</div>
                            <a href="{{ route('groups.edit', $group) }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                Add Accounts
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-xl-4">
            {{-- Group Info --}}
            <div class="card card-flush mb-5">
                <div class="card-header pt-7">
                    <h3 class="card-title">{{ __('messages.group_description') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-5">
                        <div class="symbol symbol-60px me-4">
                            <div class="symbol-label bg-light-primary">
                                <i class="bi bi-collection text-primary fs-1"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-4">{{ $group->name }}</span>
                            <span class="badge {{ $group->is_active ? 'badge-light-success' : 'badge-light-secondary' }}">
                                {{ $group->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @if($group->description)
                        <p class="text-gray-600 mb-0">{{ $group->description }}</p>
                    @else
                        <p class="text-gray-400 fst-italic mb-0">No description</p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="card card-flush">
                <div class="card-header pt-7">
                    <h3 class="card-title">{{ __('messages.quick_actions') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('groups.edit', $group) }}" class="btn btn-light-primary">
                            <i class="bi bi-pencil me-2"></i>
                            {{ __('messages.edit_group') }}
                        </a>
                        <form method="POST" action="{{ route('groups.destroy', $group) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light-danger w-100"
                                    data-confirm="true"
                                    data-confirm-title="{{ __('messages.confirm_title') }}"
                                    data-confirm-text="{{ __('messages.confirm_delete_group') }}"
                                    data-confirm-button="{{ __('messages.delete') }}">
                                <i class="bi bi-trash me-2"></i>
                                {{ __('messages.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
