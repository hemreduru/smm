@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('messages.create_workspace') }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('workspaces.store') }}" method="POST">
            @csrf
            <div class="mb-10">
                <label class="form-label required">{{ __('messages.workspace_name') }}</label>
                <input type="text" 
                       class="form-control form-control-solid @error('name') is-invalid @enderror" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="{{ __('messages.workspace_name_placeholder') }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-end gap-3">
                <a href="{{ route('workspaces.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
