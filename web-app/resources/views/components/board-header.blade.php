@props(['board' => null, 'title' => null, 'description' => null, 'showNav' => true])

<!-- AESTHETIC EXTREMIST HEADER - SURGICAL PRECISION -->
<header class="toolbar">
    <div class="flex-between" style="width: 100%;">
        <div class="flex">
            <a href="{{ route('boards.index') }}" class="toolbar-brand">
                {{ $board ? '/' . $board->code . '/' : 'Haichan' }}
            </a>
            <div class="status status-active">
                <span class="sr-only">Status:</span>
                Beta
            </div>
        </div>
        
        @if($showNav)
        <nav class="toolbar-nav">
            <a href="{{ route('boards.index') }}" class="toolbar-link">Boards</a>
            @if($board)
                <a href="/{{ $board->code }}" class="toolbar-link active">{{ $board->title }}</a>
                <a href="/{{ $board->code }}/catalog" class="toolbar-link">Catalog</a>
            @endif
            <a href="/mining" class="toolbar-link">Mining</a>
            <a href="{{ route('chat.index') }}" class="toolbar-link">Chat</a>
        </nav>
        @endif
    </div>
</header>

@if($board || $title || $description)
<div class="container">
    <div class="card">
        <h1>
            @if($board)
                {{ $board->title }}
            @else
                {{ $title ?? 'Board Directory' }}
            @endif
        </h1>
        
        @if($board && $board->description)
            <p class="text-sm">{{ $board->description }}</p>
        @elseif($description)
            <p class="text-sm">{{ $description }}</p>
        @endif
    </div>
</div>
@endif