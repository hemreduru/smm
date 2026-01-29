@props([
    'title' => null,
    'searchable' => false,
    'searchPlaceholder' => null,
    'searchId' => 'table-search'
])

<div {{ $attributes->merge(['class' => 'card-header border-0 pt-6']) }}>
    <div class="card-title">
        @if($searchable)
            <div class="d-flex align-items-center position-relative my-1">
                <i class="bi bi-search fs-3 position-absolute ms-5"></i>
                <input type="text" 
                       id="{{ $searchId }}"
                       data-table-filter="search" 
                       class="form-control form-control-solid w-250px ps-12" 
                       placeholder="{{ $searchPlaceholder ?? __('messages.search') }}..."/>
            </div>
        @elseif($title)
            <h3 class="card-title">{{ $title }}</h3>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="card-toolbar">
            {{ $slot }}
        </div>
    @endif
</div>
