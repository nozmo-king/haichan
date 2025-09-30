@extends('layout')

@section('title', '/{{ $board->code }}/ - Catalog')

@section('content')
<div style="text-align: center; margin: 20px 0; padding: 15px; background: var(--ib-header); border-radius: 8px;">
    <h1 style="margin: 0; font-size: 24px; font-weight: 600;">/{{ $board->code }}/ - Catalog</h1>
    <p style="font-size: 14px; color: var(--ib-text-muted); margin: 8px 0 0 0;">{{ $board->description }}</p>
    <p style="font-size: 12px; color: var(--ib-accent); margin: 5px 0 0 0;">⚡ Threads ordered by Proof-of-Work score</p>
</div>

<div class="nav-links" style="margin-bottom: 20px;">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="#bottom">Bottom</a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin: 0 auto; max-width: 1400px;">
    @forelse($threads as $thread)
    <div class="catalog-thread" 
         style="cursor: pointer; 
                transition: all 0.15s ease; 
                background: #fff; 
                border: 1px solid #ddd; 
                border-radius: 4px;
                overflow: hidden;
                position: relative;
                box-shadow: 0 1px 2px rgba(0,0,0,0.1);" 
         onclick="window.location.href='/{{ $board->code }}/{{ $thread->id }}'"
         data-thread-id="{{ $thread->id }}"
         onmouseover="this.style.borderColor='#999'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'"
         onmouseout="this.style.borderColor='#ddd'; this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.1)'">
        
        @if($thread->accumulated_points > 0)
        <div style="position: absolute; top: 8px; right: 8px; 
                    background: #2e7d32; color: white; 
                    font-size: 10px; font-weight: bold; 
                    padding: 2px 6px; border-radius: 10px; 
                    z-index: 10;">
            ⚡{{ number_format($thread->accumulated_points, 1) }}
        </div>
        @endif

        @if($thread->image_path)
        <div style="height: 140px; overflow: hidden; background: #f5f5f5;">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        @endif

        <div style="padding: 10px;">
            <div style="font-size: 13px; font-weight: 600; color: #333; 
                       margin-bottom: 6px; line-height: 1.3;
                       overflow: hidden; text-overflow: ellipsis; 
                       white-space: nowrap;">
                {{ $thread->title ?: 'No Subject' }}
            </div>
            
            <div style="font-size: 11px; color: #666; line-height: 1.4; 
                       margin-bottom: 8px; height: 45px; overflow: hidden;">
                {{ Str::limit($thread->content, 100) }}
            </div>
            
            <div style="display: flex; justify-content: space-between; 
                       align-items: center; font-size: 10px; color: #888; 
                       padding-top: 6px; border-top: 1px solid #eee;">
                <span>No.{{ $thread->id }}</span>
                <div style="display: flex; gap: 8px;">
                    <span>R:{{ $thread->reply_count ?? 0 }}</span>
                    @if($thread->image_path)<span>I:1</span>@endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; 
                background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
        <p style="color: #666; margin: 0 0 15px 0; font-size: 16px;">No threads in catalog yet.</p>
        <a href="/{{ $board->code }}" 
           style="color: #2e7d32; text-decoration: none; font-weight: 600; 
                  padding: 10px 20px; border: 1px solid #2e7d32; border-radius: 4px;">
            Create the first thread
        </a>
    </div>
    @endforelse
</div>

<div class="nav-links" style="margin: 30px 0; text-align: center;">
    <a name="bottom"></a>
    <a href="#">Top</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a>
</div>
@endsection