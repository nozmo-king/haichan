@props([
    'type' => 'button',
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
    'icon' => null,
    'loading' => false,
    'disabled' => false
])

@php
    $baseClasses = 'btn transition';
    
    $variantClasses = [
        'default' => '',
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary', 
        'ghost' => 'btn-ghost'
    ];
    
    $sizeClasses = [
        'small' => 'btn-small',
        'default' => '',
        'large' => 'btn-large'
    ];
    
    $classes = collect([$baseClasses])
        ->push($variantClasses[$variant] ?? '')
        ->push($sizeClasses[$size] ?? '')
        ->filter()
        ->join(' ');
        
    $tag = $href ? 'a' : 'button';
    $typeAttr = $href ? null : $type;
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @if($typeAttr) type="{{ $typeAttr }}" @endif
    @if($disabled) disabled @endif
    class="{{ $classes }}"
    {{ $attributes }}
>
    @if($loading)
        <span class="loading-spinner">⏳</span>
    @elseif($icon)
        <span class="btn-icon">{{ $icon }}</span>
    @endif
    
    {{ $slot }}
</{{ $tag }}>