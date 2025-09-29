@extends('layout')

@section('title', '/{{ $board->code }}/ - Catalog')

@section('content')
<div style="text-align: center; margin: 10px 0;">
    <h1>/{{ $board->code }}/ - Catalog</h1>
    <p style="font-size: 12px; color: var(--ib-text-muted);">{{ $board->description }}</p>
</div>

<div class="nav-links">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="#bottom">Bottom</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 15px; margin: 20px 0;">
    @forelse($threads as $thread)
    <div class="catalog-thread" style="cursor: pointer; transition: all 0.2s; background: var(--ib-panel); border: 1px solid var(--ib-border); box-shadow: 0 1px 3px rgba(0,0,0,0.1);" 
         onclick="window.location.href='/{{ $board->code }}/{{ $thread->id }}'"
         data-thread-id="{{ $thread->id }}">
        
        <div style="background: var(--ib-header); border-bottom: 1px solid var(--ib-border); padding: 8px 12px;">
            <div style="font-size: 13px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $thread->title ?: 'No Subject' }}
            </div>
            @if($thread->accumulated_points > 0)
            <div style="font-size: 11px; color: var(--ib-accent); margin-top: 2px;">
                ⚡{{ number_format($thread->accumulated_points, 1) }}
            </div>
            @endif
        </div>

        @if($thread->image_path)
        <div style="padding: 0; text-align: center;">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}"
                 style="max-width: 100%; max-height: 150px; object-fit: cover;">
        </div>
        @endif

        <div style="padding: 12px;">
            <div style="font-size: 12px; color: var(--ib-text); line-height: 1.4; margin-bottom: 8px; 
                       overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                {{ Str::limit($thread->content, 150) }}
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--ib-text-muted); border-top: 1px solid var(--ib-border-light); padding-top: 8px;">
                <span>No.{{ $thread->id }}</span>
                <div style="display: flex; gap: 10px;">
                    <span>R: {{ $thread->reply_count }}</span>
                    <span>I: {{ $thread->image_count }}</span>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--ib-panel); border: 1px solid var(--ib-border);">
        <p style="color: var(--ib-text-muted); margin: 0;">No threads in catalog yet.</p>
        <p style="color: var(--ib-text-muted); margin: 10px 0 0 0; font-size: 12px;">
            <a href="/{{ $board->code }}">Create the first thread</a>
        </p>
    </div>
    @endforelse
</div>

<div class="nav-links">
    <a name="bottom"></a>
    <a href="#">Top</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a>
</div>
@endsection