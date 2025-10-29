@props([
    'type' => 'live',
    'icon' => null,
    'label' => null
])

@php
    $icons = [
        'live' => '🟢',
        'offline' => '🔴', 
        'warning' => '🟡',
        'info' => '🔵'
    ];
    
    $classes = [
        'live' => 'status-live',
        'offline' => 'status-offline',
        'warning' => 'status-warning',
        'info' => 'status-info'
    ];
    
    $displayIcon = $icon ?? $icons[$type] ?? '⚪';
    $statusClass = $classes[$type] ?? 'status-live';
@endphp

<div class="status-indicator {{ $statusClass }}" {{ $attributes }}>
    <span class="status-icon">{{ $displayIcon }}</span>
    @if($label)
        <span class="status-label">{{ $label }}</span>
    @endif
    {{ $slot }}
</div>