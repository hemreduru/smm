<span class="badge {{ $topic->status->badgeClass() }}">
    <i class="bi {{ $topic->status->icon() }} me-1"></i>
    {{ $topic->status->label() }}
</span>
