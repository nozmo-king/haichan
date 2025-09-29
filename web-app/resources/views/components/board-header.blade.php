@props(['board' => null, 'title' => null, 'description' => null, 'showNav' => true])

<!-- Consistent Board Header Component -->
<div class="tui-window" style="margin: 20px auto; max-width: 900px;">
    <div class="tui-header">
        <div class="tui-dots">
            <div class="tui-dot"></div>
            <div class="tui-dot"></div>
            <div class="tui-dot"></div>
        </div>
        <div class="tui-title">{{ $board ? '/' . $board->code . '/' : ($title ?? 'Haichan') }}</div>
        <div style="margin-left: auto; background: var(--hc-accent); color: #000; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
            β版
        </div>
    </div>

    @if($showNav)
    <div class="tui-tabs">
        <a href="{{ route('boards.index') }}" class="tui-tab">📋 Boards</a>
        @if($board)
            <a href="/{{ $board->code }}" class="tui-tab" aria-selected="true">{{ $board->title }}</a>
            <a href="/{{ $board->code }}/catalog" class="tui-tab">📑 Catalog</a>
        @endif
        <a href="/mining" class="tui-tab">⛏️ Mining</a>
        <a href="{{ route('chat.index') }}" class="tui-tab">💬 PoW Chat</a>
    </div>
    @endif

    <div class="tui-p">
        <h1 style="margin: 0 0 10px 0; color: var(--hc-ink); font-size: 24px; font-weight: 300;">
            @if($board)
                <span style="color: var(--hc-accent);">⛏</span>
                {{ $board->title }}
                <span style="color: var(--hc-accent);">⚡</span>
            @else
                {{ $title ?? 'Board Directory' }}
            @endif
        </h1>

        @if($board && $board->description)
            <p style="color: var(--hc-muted); margin: 10px 0; font-size: 13px;">{{ $board->description }}</p>
        @elseif($description)
            <p style="color: var(--hc-muted); margin: 10px 0; font-size: 13px;">{{ $description }}</p>
        @endif
    </div>
</div>