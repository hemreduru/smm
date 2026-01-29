<div class="d-flex justify-content-end flex-shrink-0">
    <a href="{{ route('contents.show', $content) }}"
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
       data-bs-toggle="tooltip" title="{{ __('messages.view') }}">
        <i class="bi bi-eye fs-4"></i>
    </a>

    @if($content->isEditable())
        <a href="{{ route('contents.edit', $content) }}"
           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
           data-bs-toggle="tooltip" title="{{ __('messages.edit') }}">
            <i class="bi bi-pencil fs-4"></i>
        </a>
    @endif

    @if($content->isDeletable())
        <form method="POST" action="{{ route('contents.destroy', $content) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                    data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
                    data-confirm="true"
                    data-confirm-title="{{ __('messages.confirm_title') }}"
                    data-confirm-text="{{ __('messages.confirm_delete_content') }}"
                    data-confirm-button="{{ __('messages.delete') }}">
                <i class="bi bi-trash fs-4"></i>
            </button>
        </form>
    @endif
</div>
