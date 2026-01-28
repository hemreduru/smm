@extends('layouts.app')

@section('title', __('messages.create_group'))

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.create_group') }}
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
            <li class="breadcrumb-item text-muted">{{ __('messages.create_group') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">{{ __('messages.create_group') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('groups.store') }}" method="POST">
                @csrf

                {{-- Group Name --}}
                <div class="fv-row mb-7">
                    <label class="required fs-6 fw-semibold mb-2">{{ __('messages.group_name') }}</label>
                    <input type="text" class="form-control form-control-solid @error('name') is-invalid @enderror" 
                           name="name" value="{{ old('name') }}" 
                           placeholder="Enter group name"/>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="fv-row mb-7">
                    <label class="fs-6 fw-semibold mb-2">{{ __('messages.group_description') }}</label>
                    <textarea class="form-control form-control-solid @error('description') is-invalid @enderror" 
                              name="description" rows="3" 
                              placeholder="Optional description for this group">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Select Accounts --}}
                <div class="fv-row mb-7">
                    <label class="fs-6 fw-semibold mb-2">{{ __('messages.select_accounts') }}</label>
                    
                    @if($accounts->count() > 0)
                        <div class="mh-300px scroll-y">
                            @foreach($accounts->groupBy('platform') as $platform => $platformAccounts)
                                <div class="mb-5">
                                    <h6 class="fw-semibold text-gray-600 mb-3">
                                        <i class="bi bi-{{ $platformAccounts->first()->platform->icon() }} me-1" 
                                           style="color: {{ $platformAccounts->first()->platform->color() }}"></i>
                                        {{ $platformAccounts->first()->platform->label() }}
                                    </h6>
                                    @foreach($platformAccounts as $account)
                                        <label class="d-flex flex-stack cursor-pointer mb-2">
                                            <span class="d-flex align-items-center me-2">
                                                <span class="symbol symbol-40px me-3">
                                                    @if($account->profile_picture_url)
                                                        <img src="{{ $account->profile_picture_url }}" alt="{{ $account->username }}"/>
                                                    @else
                                                        <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                            {{ strtoupper(substr($account->username, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </span>
                                                <span class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-800 fs-6">{{ $account->username }}</span>
                                                    @if($account->display_name)
                                                        <span class="fs-7 text-gray-500">{{ $account->display_name }}</span>
                                                    @endif
                                                </span>
                                            </span>
                                            <span class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="account_ids[]" value="{{ $account->id }}"
                                                       {{ in_array($account->id, old('account_ids', [])) ? 'checked' : '' }}/>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                            <i class="bi bi-exclamation-triangle fs-2tx text-warning me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">{{ __('messages.no_accounts') }}</h4>
                                    <div class="fs-6 text-gray-700">
                                        {{ __('messages.connect_first_account') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @error('account_ids')
                        <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('groups.index') }}" class="btn btn-light me-3">{{ __('messages.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1"></i>
                        {{ __('messages.create_group') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
