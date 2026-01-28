<div class="d-flex justify-content-end flex-shrink-0">
    <a href="{{ route('accounts.show', $account) }}" 
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
       data-bs-toggle="tooltip" title="{{ __('messages.view') }}">
        <i class="bi bi-eye fs-4"></i>
    </a>
    <a href="{{ route('accounts.edit', $account) }}" 
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
       data-bs-toggle="tooltip" title="{{ __('messages.edit') }}">
        <i class="bi bi-pencil fs-4"></i>
    </a>
    <form method="POST" action="{{ route('accounts.destroy', $account) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                data-bs-toggle="tooltip" title="{{ __('messages.disconnect_account') }}"
                data-confirm="true"
                data-confirm-title="{{ __('messages.confirm_title') }}"
                data-confirm-text="{{ __('messages.confirm_disconnect') }}"
                data-confirm-button="{{ __('messages.disconnect_account') }}">
            <i class="bi bi-x-circle fs-4"></i>
        </button>
    </form>
</div>
