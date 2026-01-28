@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('messages.edit_workspace') }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('workspaces.update', $workspace->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-10">
                <label class="form-label required">{{ __('messages.workspace_name') }}</label>
                <input type="text" 
                       class="form-control form-control-solid @error('name') is-invalid @enderror" 
                       name="name" 
                       value="{{ old('name', $workspace->name) }}" 
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" 
                        class="btn btn-danger"
                        data-confirm="true"
                        data-confirm-title="{{ __('messages.confirm_delete_title') }}"
                        data-confirm-text="{{ __('messages.confirm_delete_workspace') }}"
                        data-confirm-button="{{ __('messages.confirm_delete_button') }}"
                        data-confirm-method="delete"
                        data-confirm-url="{{ route('workspaces.destroy', $workspace->id) }}">
                    <i class="bi bi-trash me-2"></i>{{ __('messages.delete') }}
                </button>
                <div class="d-flex gap-3">
                    <a href="{{ route('workspaces.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
