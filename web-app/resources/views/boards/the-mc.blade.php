@extends('layout')

@section('title', 'The MC - All Threads')

@section('content')
<div class="page-content">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px;">
        <h1 style="color: #3D315B; font-family: 'Nova Cut', serif; font-size: 28px; margin: 0 0 10px 0; letter-spacing: 2px;">
            🎯 THE MC 🎯
        </h1>
        <p style="color: #708B75; font-size: 14px; margin: 0;">
            Master Catalog - All threads from all boards, sorted by mining energy
        </p>
        <div style="margin-top: 15px; font-size: 12px; color: #666;">
            <span style="margin-right: 20px;">📊 {{ $totalBoards }} Boards</span>
            <span style="margin-right: 20px;">🧵 {{ $totalThreads }} Total Threads</span>
            <span>⚡ Top {{ count($threads) }} by PoW</span>
        </div>
    </div>

    <!-- Navigation -->
    <div style="text-align: center; margin-bottom: 25px;">
        <a href="/" style="color: #708B75; text-decoration: none; margin: 0 10px; font-size: 12px;">🏠 Home</a>
        <a href="/boards" style="color: #708B75; text-decoration: none; margin: 0 10px; font-size: 12px;">📋 Boards</a>
        <a href="/library" style="color: #708B75; text-decoration: none; margin: 0 10px; font-size: 12px;">🖼️ Library</a>
        <a href="/mining" style="color: #708B75; text-decoration: none; margin: 0 10px; font-size: 12px;">⛏️ Mining</a>
    </div>

    <!-- Thread Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($threads as $thread)
        <div style="background: #F5F5DC; border: 2px solid #9AB87A; border-radius: 8px; padding: 20px; transition: all 0.3s; position: relative;"
             data-thread-id="{{ $thread->id }}"
             data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}"
             onmouseover="this.style.borderColor='#708B75'; this.style.boxShadow='0 4px 12px rgba(68, 75, 110, 0.2)';"
             onmouseout="this.style.borderColor='#9AB87A'; this.style.boxShadow='none';">

            @if($thread->accumulated_points > 0)
            <!-- Energy Badge -->
            <div style="position: absolute; top: -8px; right: -8px; background: linear-gradient(135deg, #FF6B6B, #FFD93D); color: #FFF; padding: 4px 8px; font-size: 9px; font-weight: bold; border-radius: 12px; border: 2px solid #FFF; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                ⚡{{ $thread->accumulated_points }} PoW
            </div>
            @endif

            <!-- Board Tag -->
            <div style="display: inline-block; background: #708B75; color: #FFFFEE; padding: 2px 8px; font-size: 10px; font-weight: bold; border-radius: 3px; margin-bottom: 10px;">
                /{{ $thread->board->code }}/
            </div>

            <!-- Thread Title -->
            <h3 style="color: #3D315B; font-size: 14px; margin: 0 0 10px 0; line-height: 1.3;">
                <a href="/{{ $thread->board->code }}/{{ $thread->id }}" style="color: inherit; text-decoration: none;">
                    {{ $thread->title ?: 'Thread #' . $thread->id }}
                </a>
            </h3>

            <!-- Thread Content Preview -->
            @if($thread->content)
            <div style="color: #666; font-size: 11px; line-height: 1.4; margin-bottom: 10px; max-height: 60px; overflow: hidden; position: relative;">
                {{ Str::limit($thread->content, 120) }}
                @if(strlen($thread->content) > 120)
                <div style="position: absolute; bottom: 0; right: 0; background: linear-gradient(to right, transparent, #F5F5DC); padding-left: 20px;">...</div>
                @endif
            </div>
            @endif

            <!-- Thread Image -->
            @if($thread->image_path)
            <div style="margin-bottom: 10px;">
                <img src="{{ route('thread.image', $thread->id) }}" alt="Thread image"
                     data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}"
                     style="max-width: 100%; height: auto; max-height: 80px; border-radius: 4px; object-fit: cover;">
            </div>
            @endif

            <!-- Thread Stats -->
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: #888; border-top: 1px solid #DDD; padding-top: 8px;">
                <div>
                    💬 {{ $thread->posts_count }} replies
                </div>
                <div>
                    📅 {{ $thread->created_at->diffForHumans() }}
                </div>
                <div style="color: #708B75; font-weight: bold;">
                    by @if($thread->user_id && $thread->bitcoinUser)
                        {{ $thread->bitcoinUser->getDisplayName() }}
                    @else
                        Anonymous
                    @endif
                    @if($thread->user_id)
                        @include('components.admin-badge', ['user' => $thread->bitcoinUser])
                    @endif
                </div>
            </div>

            <!-- Hash Display -->
            @if($thread->pow_hash)
            <div style="margin-top: 8px; font-family: monospace; font-size: 8px; color: #9AB87A; background: #FFFACD; padding: 5px; border-radius: 3px; word-break: break-all;">
                🔗 {{ $thread->pow_hash }}
            </div>
            @endif
        </div>
        @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #888;">
            <div style="font-size: 48px; margin-bottom: 20px;">🏜️</div>
            <h3 style="color: #666; margin-bottom: 10px;">No threads found</h3>
            <p style="font-size: 14px;">Start mining some threads to see them here!</p>
            <a href="/gen/create" style="display: inline-block; margin-top: 20px; background: #708B75; color: #FFFFEE; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 12px;">
                Create First Thread
            </a>
        </div>
        @endforelse
    </div>

    @if(count($threads) > 0)
    <!-- Footer Stats -->
    <div style="margin-top: 40px; text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px; font-size: 12px; color: #666;">
        Showing top {{ count($threads) }} threads by proof-of-work energy across {{ $totalBoards }} boards
    </div>
    @endif
</div>
@endsection