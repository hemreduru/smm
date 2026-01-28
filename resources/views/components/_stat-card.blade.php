@props([
    'title',
    'value',
    'icon' => null,
    'iconClass' => 'bi bi-graph-up',
    'color' => 'primary',
    'trend' => null,
    'trendUp' => true,
    'description' => null
])

<div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-100" 
     style="background-color: var(--kt-{{ $color }}-light); background-image: url('/metronic/demo1/dist/assets/media/patterns/vector-1.png')">
    <div class="card-header pt-5">
        <div class="card-title d-flex flex-column">
            <span class="fs-2hx fw-bold text-{{ $color }} me-2 lh-1 ls-n2">{{ $value }}</span>
            @if($trend)
                <span class="text-{{ $trendUp ? 'success' : 'danger' }} pt-1 fw-semibold fs-6">
                    <i class="bi bi-arrow-{{ $trendUp ? 'up' : 'down' }} text-{{ $trendUp ? 'success' : 'danger' }}"></i>
                    {{ $trend }}
                </span>
            @endif
        </div>
    </div>
    <div class="card-body d-flex align-items-end pt-0">
        <div class="d-flex align-items-center flex-column mt-3 w-100">
            <div class="d-flex justify-content-between fw-bold fs-6 text-{{ $color }} opacity-75 w-100 mt-auto mb-2">
                <span>{{ $title }}</span>
                @if($icon)
                    <i class="{{ $iconClass }} fs-2"></i>
                @endif
            </div>
            @if($description)
                <div class="text-gray-500 fs-7 w-100">{{ $description }}</div>
            @endif
        </div>
    </div>
</div>
