@extends('layout')

@section('title', 'Haichan - Boards')

@section('content')
<x-board-header 
    title="Board Directory" 
    description="Choose a board for anonymous discussion • Mining required to post" 
/>

<div class="container">
    <div class="grid grid-3">
        @foreach($boards as $board)
        <article class="board-card interactive-card hover-lift hover-glow" onclick="location.href='{{ $board->url }}'">
            <header class="board-card-header">
                <div class="board-code">/{{ $board->code }}/</div>
                <h3 class="board-title">{{ $board->title }}</h3>
                <p class="board-description">{{ $board->description }}</p>
            </header>
            
            <div class="board-stats">
                <div class="stat">
                    <div class="stat-value">{{ number_format($board->threads_count) }}</div>
                    <div class="stat-label">Threads</div>
                </div>
                
                <div class="stat">
                    <div class="stat-value">{{ number_format($board->post_count ?? 0) }}</div>
                    <div class="stat-label">Posts</div>
                </div>
                
                <div class="stat">
                    <div class="stat-indicator {{ $board->is_active ? 'active' : 'inactive' }}"></div>
                    <div class="stat-label">Status</div>
                </div>
            </div>
            
            <footer class="board-actions">
                <a href="{{ $board->url }}" class="btn btn-primary btn-small">View Board</a>
                <a href="{{ $board->url }}/catalog" class="btn btn-ghost btn-small">Catalog</a>
            </footer>
        </article>
        @endforeach
    </div>

    @if(count($boards) === 0)
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>No Boards Available</h3>
        <p class="text-sm">Check back later or contact an administrator to create boards.</p>
    </div>
    @endif
</div>

<style>
/* BOARD CARDS - SURGICAL PRECISION */
.board-card {
    background: var(--neutral-0);
    border: var(--border-width) solid var(--neutral-4);
    border-radius: var(--border-radius);
    overflow: hidden;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}

.board-card-header {
    background: var(--accent-5);
    color: var(--neutral-0);
    padding: var(--space-4);
    position: relative;
}

.board-code {
    position: absolute;
    top: var(--space-3);
    right: var(--space-4);
    background: rgba(255, 255, 255, 0.2);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    border: var(--border-width) solid rgba(255, 255, 255, 0.3);
}

.board-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-medium);
    margin: 0 0 var(--space-2) 0;
    color: var(--neutral-0);
}

.board-description {
    font-size: var(--font-size-sm);
    margin: 0;
    opacity: 0.9;
    line-height: var(--line-height-normal);
    color: var(--neutral-0);
}

.board-stats {
    padding: var(--space-4);
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: var(--space-4);
    flex: 1;
}

.stat {
    text-align: center;
}

.stat-value {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-medium);
    color: var(--accent-6);
    margin-bottom: var(--space-1);
}

.stat-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin: 0 auto var(--space-1) auto;
}

.stat-indicator.active {
    background: var(--accent-5);
}

.stat-indicator.inactive {
    background: var(--neutral-5);
}

.stat-label {
    font-size: var(--font-size-xs);
    color: var(--neutral-6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: var(--font-weight-medium);
}

.board-actions {
    padding: var(--space-4);
    border-top: var(--border-width) solid var(--neutral-3);
    display: flex;
    gap: var(--space-2);
}

.board-actions .btn {
    flex: 1;
}

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: var(--space-8);
    background: var(--neutral-0);
    border: var(--border-width) solid var(--neutral-4);
    border-radius: var(--border-radius);
}

.empty-icon {
    font-size: 48px;
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    color: var(--neutral-7);
    margin-bottom: var(--space-2);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .grid-3 {
        grid-template-columns: 1fr;
    }
    
    .board-stats {
        grid-template-columns: 1fr;
        gap: var(--space-2);
    }
    
    .board-actions {
        flex-direction: column;
    }
}
</style>
@endsection
