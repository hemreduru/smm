<div class="symbol symbol-65px">
    @if($content->thumbnail_path)
        <img src="{{ $content->getThumbnailUrl() }}" alt="{{ $content->title }}" class="rounded"/>
    @else
        <div class="symbol-label bg-light-primary rounded">
            <i class="bi bi-film text-primary fs-2"></i>
        </div>
    @endif
</div>
