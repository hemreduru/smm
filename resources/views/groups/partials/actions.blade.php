<div class="d-flex justify-content-end flex-shrink-0">
    <a href="{{ route('groups.show', $group) }}" 
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
       data-bs-toggle="tooltip" title="{{ __('messages.view') }}">
        <i class="bi bi-eye fs-4"></i>
    </a>
    <a href="{{ route('groups.edit', $group) }}" 
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
       data-bs-toggle="tooltip" title="{{ __('messages.edit') }}">
        <i class="bi bi-pencil fs-4"></i>
    </a>
    <form method="POST" action="{{ route('groups.destroy', $group) }}" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
                data-confirm="true"
                data-confirm-title="{{ __('messages.confirm_title') }}"
                data-confirm-text="{{ __('messages.confirm_delete_group') }}"
                data-confirm-button="{{ __('messages.delete') }}">
            <i class="bi bi-trash fs-4"></i>
        </button>
    </form>
</div>
