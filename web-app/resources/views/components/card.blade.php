@props([
    'title' => null,
    'subtitle' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => ''
])

<div class="card" {{ $attributes }}>
    @if($title || $subtitle || isset($header))
        <div class="card-header {{ $headerClass }}">
            @if($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="card-subtitle">{{ $subtitle }}</p>
            @endif
            {{ $header ?? '' }}
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="card-footer {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endisset
</div>