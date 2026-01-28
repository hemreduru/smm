<div class="d-flex justify-content-end gap-2">
    @if(!$isCurrentUser)
        <button type="button" 
                class="btn btn-sm btn-icon btn-light-danger"
                data-confirm="true"
                data-confirm-title="{{ __('messages.confirm_title') }}"
                data-confirm-text="{{ __('messages.confirm_remove_user') }}"
                data-confirm-button="{{ __('messages.confirm_button') }}"
                data-confirm-method="delete"
                data-confirm-url="{{ route('workspaces.users.remove', [$workspaceId, $user]) }}">
            <i class="bi bi-trash"></i>
        </button>
    @endif
</div>
