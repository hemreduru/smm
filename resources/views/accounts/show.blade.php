@extends('layouts.app')

@section('title', $account->username)

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ $account->username }}
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
            <li class="breadcrumb-item text-muted">{{ $account->username }}</li>
        </ul>
    </div>
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-light-primary">
            <i class="bi bi-pencil me-1"></i>
            {{ __('messages.edit') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-8">
        {{-- Account Details Card --}}
        <div class="col-xl-8">
            <div class="card card-flush mb-5">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">{{ __('messages.profile_details') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge {{ $account->platform->badgeClass() }} fs-6">
                            <i class="bi {{ $account->platform->icon() }} me-1"></i>
                            {{ $account->platform->label() }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                        <div class="me-7 mb-4">
                            <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                @if($account->profile_picture_url)
                                    <img src="{{ $account->profile_picture_url }}" alt="{{ $account->username }}"/>
                                @else
                                    <div class="symbol-label fs-1 bg-light-primary text-primary fw-bold">
                                        {{ strtoupper(substr($account->username, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="position-absolute translate-middle bottom-0 start-100 mb-6 rounded-circle border border-4 border-body h-20px w-20px 
                                    {{ $account->isHealthy() ? 'bg-success' : 'bg-warning' }}"></div>
                            </div>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="text-gray-900 fs-2 fw-bold me-3">{{ $account->username }}</span>
                                        <span class="badge {{ $account->status->badgeClass() }}">
                                            {{ $account->status->label() }}
                                        </span>
                                    </div>
                                    @if($account->display_name)
                                        <span class="text-gray-500 fw-semibold fs-5 mb-2">{{ $account->display_name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex flex-wrap py-5 border-bottom">
                                <div class="d-flex align-items-center me-5 mb-2">
                                    <i class="bi bi-clock text-gray-500 me-2 fs-4"></i>
                                    <span class="text-gray-800">{{ __('messages.joined') }}: {{ $account->created_at->format('M d, Y') }}</span>
                                </div>
                                @if($account->last_synced_at)
                                    <div class="d-flex align-items-center me-5 mb-2">
                                        <i class="bi bi-arrow-repeat text-gray-500 me-2 fs-4"></i>
                                        <span class="text-gray-800">{{ __('messages.last_synced') }}: {{ $account->last_synced_at->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Token Status --}}
                    <div class="notice d-flex bg-light-{{ $account->isHealthy() ? 'success' : 'warning' }} rounded border-{{ $account->isHealthy() ? 'success' : 'warning' }} border border-dashed p-6">
                        <i class="bi bi-{{ $account->isHealthy() ? 'check-circle' : 'exclamation-triangle' }} fs-2tx text-{{ $account->isHealthy() ? 'success' : 'warning' }} me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">{{ __('messages.token_status') }}</h4>
                                <div class="fs-6 text-gray-700">
                                    @if($account->isHealthy())
                                        Account is healthy and ready for publishing.
                                        @if($account->token_expires_at)
                                            Token expires {{ $account->token_expires_at->diffForHumans() }}.
                                        @endif
                                    @else
                                        Account needs attention. Token may be expired or revoked.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Groups Card --}}
            <div class="card card-flush">
                <div class="card-header pt-7">
                    <h3 class="card-title">
                        <span class="card-label fw-bold text-gray-900">{{ __('messages.account_groups') }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    @if($account->groups->count() > 0)
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($account->groups as $group)
                                <a href="{{ route('groups.show', $group) }}" 
                                   class="badge badge-lg badge-light-primary p-3 text-hover-primary">
                                    <i class="bi bi-collection me-1"></i>
                                    {{ $group->name }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="bi bi-collection text-gray-400 fs-3x mb-3"></i>
                            <div class="text-gray-600 fs-6">This account is not part of any group</div>
                            <a href="{{ route('groups.create') }}" class="btn btn-sm btn-light-primary mt-3">
                                <i class="bi bi-plus-lg me-1"></i>
                                {{ __('messages.create_group') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions Sidebar --}}
        <div class="col-xl-4">
            <div class="card card-flush">
                <div class="card-header pt-7">
                    <h3 class="card-title">{{ __('messages.quick_actions') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-light-primary">
                            <i class="bi bi-pencil me-2"></i>
                            {{ __('messages.edit') }}
                        </a>
                        <form method="POST" action="{{ route('accounts.destroy', $account) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light-danger w-100"
                                    data-confirm="true"
                                    data-confirm-title="{{ __('messages.confirm_title') }}"
                                    data-confirm-text="{{ __('messages.confirm_disconnect') }}"
                                    data-confirm-button="{{ __('messages.disconnect_account') }}">
                                <i class="bi bi-x-circle me-2"></i>
                                {{ __('messages.disconnect_account') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
