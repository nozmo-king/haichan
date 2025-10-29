@props(['board' => null, 'title' => null, 'description' => null, 'showNav' => true])

<x-board-header :board="$board" :title="$title" :description="$description" :showNav="$showNav" />

<div class="container">
    @if($errors->any())
    <div class="error-panel">
        @foreach($errors->all() as $error)
            <p class="error-message">{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="board-content">
        {{ $slot }}
    </div>
</div>

<style>
/* BOARD LAYOUT - AESTHETIC EXTREMIST */
.error-panel {
    background: #FEF2F2;
    border: var(--border-width) solid #FCA5A5;
    border-radius: var(--border-radius);
    padding: var(--space-4);
    margin-bottom: var(--space-4);
}

.error-message {
    color: #DC2626;
    font-size: var(--font-size-sm);
    margin: var(--space-1) 0;
}

.error-message:before {
    content: "• ";
    color: #DC2626;
    font-weight: var(--font-weight-medium);
}

.board-content {
    /* Container for board-specific content */
}
</style>