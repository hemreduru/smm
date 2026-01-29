<div class="d-flex justify-content-end flex-shrink-0">
    {{-- View --}}
    <a href="{{ route('topics.show', $topic) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
       data-bs-toggle="tooltip" title="{{ __('messages.view') }}">
        <i class="bi bi-eye fs-6"></i>
    </a>

    {{-- Edit --}}
    @if($topic->canBeEdited())
        <a href="{{ route('topics.edit', $topic) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
           data-bs-toggle="tooltip" title="{{ __('messages.edit') }}">
            <i class="bi bi-pencil fs-6"></i>
        </a>
    @endif

    {{-- Approve --}}
    @if($topic->isDraft())
        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1"
                data-bs-toggle="tooltip" title="{{ __('messages.topics.approve') }}"
                onclick="approveTopic({{ $topic->id }})">
            <i class="bi bi-check-circle fs-6"></i>
        </button>
    @endif

    {{-- Send to n8n --}}
    @if($topic->canBeSentToN8n())
        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1"
                data-bs-toggle="tooltip" title="{{ __('messages.topics.send_to_n8n') }}"
                onclick="sendToN8n({{ $topic->id }})">
            <i class="bi bi-send fs-6"></i>
        </button>
    @endif

    {{-- Reset to Draft --}}
    @if($topic->isFailed())
        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1"
                data-bs-toggle="tooltip" title="{{ __('messages.topics.reset_to_draft') }}"
                onclick="resetToDraft({{ $topic->id }})">
            <i class="bi bi-arrow-counterclockwise fs-6"></i>
        </button>
    @endif

    {{-- Delete --}}
    @if($topic->canBeEdited())
        <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                data-bs-toggle="tooltip" title="{{ __('messages.delete') }}"
                onclick="deleteTopic({{ $topic->id }})">
            <i class="bi bi-trash fs-6"></i>
        </button>
    @endif
</div>

<script>
// These functions need to be available globally for inline onclick handlers
if (typeof window.approveTopic === 'undefined') {
    window.approveTopic = async function(id) {
        const confirmed = await confirmAction({
            title: '{{ __('messages.topics.approve') }}',
            text: '{{ __('messages.confirm_action') ?? 'Are you sure?' }}',
            confirmButtonText: '{{ __('messages.yes') }}',
        });

        if (!confirmed) return;

        try {
            const response = await fetch(`/topics/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                } else {
                    location.reload();
                }
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('{{ __('messages.error') }}');
        }
    };

    window.sendToN8n = async function(id) {
        const confirmed = await confirmAction({
            title: '{{ __('messages.topics.send_to_n8n') }}',
            text: '{{ __('messages.confirm_action') ?? 'Are you sure?' }}',
            confirmButtonText: '{{ __('messages.yes') }}',
        });

        if (!confirmed) return;

        try {
            const response = await fetch(`/topics/${id}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                } else {
                    location.reload();
                }
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('{{ __('messages.error') }}');
        }
    };

    window.resetToDraft = async function(id) {
        const confirmed = await confirmAction({
            title: '{{ __('messages.topics.reset_to_draft') }}',
            text: '{{ __('messages.confirm_action') ?? 'Are you sure?' }}',
            confirmButtonText: '{{ __('messages.yes') }}',
        });

        if (!confirmed) return;

        try {
            const response = await fetch(`/topics/${id}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                } else {
                    location.reload();
                }
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('{{ __('messages.error') }}');
        }
    };

    window.deleteTopic = async function(id) {
        const confirmed = await confirmAction({
            title: '{{ __('messages.delete') }}',
            text: '{{ __('messages.confirm_delete') ?? 'This action cannot be undone.' }}',
            confirmButtonText: '{{ __('messages.delete') }}',
            icon: 'warning'
        });

        if (!confirmed) return;

        try {
            const response = await fetch(`/topics/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                } else {
                    window.location.href = '{{ route('topics.index') }}';
                }
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('{{ __('messages.error') }}');
        }
    };
}
</script>
