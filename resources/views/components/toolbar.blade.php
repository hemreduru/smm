@props([
    'title',
    'breadcrumbs' => []
])

<div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
        {{ $title }}
    </h1>
    @if(count($breadcrumbs) > 0)
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            @foreach($breadcrumbs as $breadcrumb)
                <li class="breadcrumb-item text-muted">
                    @if(isset($breadcrumb['url']))
                        <a href="{{ $breadcrumb['url'] }}" class="text-muted text-hover-primary">{{ $breadcrumb['label'] }}</a>
                    @else
                        {{ $breadcrumb['label'] }}
                    @endif
                </li>
                @if(!$loop->last)
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                @endif
            @endforeach
        </ul>
    @endif
</div>
