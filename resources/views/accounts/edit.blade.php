@extends('layouts.app')

@section('title', __('messages.edit') . ' - ' . $account->username)

@section('toolbar')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            {{ __('messages.edit') }} {{ $account->username }}
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
            <li class="breadcrumb-item text-muted">{{ __('messages.edit') }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="card card-flush">
        <div class="card-header">
            <h3 class="card-title">
                <span class="badge {{ $account->platform->badgeClass() }} me-3">
                    <i class="bi {{ $account->platform->icon() }} me-1"></i>
                    {{ $account->platform->label() }}
                </span>
                {{ $account->username }}
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('accounts.update', $account) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Display Name --}}
                <div class="fv-row mb-7">
                    <label class="fs-6 fw-semibold mb-2">{{ __('messages.display_name') }}</label>
                    <input type="text" class="form-control form-control-solid @error('display_name') is-invalid @enderror" 
                           name="display_name" value="{{ old('display_name', $account->display_name) }}" 
                           placeholder="Display Name (optional)"/>
                    @error('display_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('accounts.show', $account) }}" class="btn btn-light me-3">{{ __('messages.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1"></i>
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
