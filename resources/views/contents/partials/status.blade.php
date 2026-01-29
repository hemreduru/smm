<span class="badge {{ $content->status->badgeClass() }}">
    <i class="bi {{ $content->status->icon() }} me-1"></i>
    {{ $content->status->label() }}
</span>
