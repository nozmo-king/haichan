<div class="post {{ $level > 0 ? 'nested-post' : 'reply-post' }} @if($post->is_anonymous_post ?? false) anonymous-garbage-post @endif" id="post{{ $post->id }}"
     data-mine-type="reply"
     data-mine-target="{{ $post->id }}"
     data-mine-weight="40"
     data-thread-id="{{ $thread->id }}"
     data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}"
     data-post-id="{{ $post->id }}">

    <div class="post-header">
        <span class="poster-info">
            @if($board->code === 'pol' && $post->country_flag)
                <span style="font-size: 18px; margin-right: 5px; vertical-align: middle;">{{ $post->country_flag }}</span>
            @endif
            @if($post->is_anonymous_post ?? false)
                <span style="color: #8B008B; font-weight: bold;">Anonymous</span>
            @else
                Anonymous
            @endif
            {{ $post->created_at->format('m/d/y H:i:s') }} No.{{ $post->id }}
            @if($post->user_id)
                @include('components.admin-badge', ['user' => $post->bitcoinUser])
            @endif
            @if($post->parent_id)
                <a href="#post{{ $post->parent_id }}" class="quote-link">&gt;&gt;{{ $post->parent_id }}</a>
            @endif
            <a href="javascript:void(0)" onclick="quotePost({{ $post->id }})" class="reply-link">[Reply]</a>
            @if(session('bitcoin_auth_id') && ($post->user_id === session('bitcoin_auth_id') || (session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin)))
                <form method="POST" action="{{ route('posts.delete.user', $post->id) }}" style="display: inline; margin-left: 10px;" onsubmit="return confirm('Delete this post?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #F44336; cursor: pointer; font-size: 12px;">[Delete]</button>
                </form>
            @endif
        </span>
    </div>

    <!-- Reply PoW Hash Display -->
    <div id="hash-{{ $post->id }}" style="font-family: 'Courier New', monospace; font-size: 9px; color: #888; margin: 5px 0; padding: 5px; background: #F8F8F8; border: 1px solid #DDD; word-break: break-all;">
        <span style="color: #666;">SHA256:</span> <span class="hash-value">calculating...</span>
        <span class="hash-bump-indicator" style="display: none; color: #ff6b35; font-weight: bold; margin-left: 10px;">🔥 21e8 BUMP!</span>
    </div>

    <div class="post-content">
        @if($post->image_filename)
        <div class="post-image"
             data-mine-type="image"
             data-mine-target="image-{{ $post->id }}"
             data-mine-weight="60"
             data-mine-title="Image #{{ $post->id }}">
            <div class="image-info">
                File: {{ $post->image_original_name }}
            </div>
            <img src="{{ route('post.image', $post->id) }}" class="thumbnail"
                 style="max-width: 125px; max-height: 125px; cursor: pointer; border: 1px solid #ccc;"
                 onclick="expandImage(this)" alt="Post image {{ $post->id }}">
        </div>
        @endif

        <div class="post-text @if($post->user_id && $post->bitcoinUser && $post->bitcoinUser->is_admin) admin-post-content @endif">
            {!! App\Helpers\MarkdownHelper::parseContent($post->content) !!}
        </div>
    </div>

</div>

@if($post->user_id && $post->bitcoinUser && $post->bitcoinUser->is_admin)
<style>
.admin-post-content {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FFD700 100%);
    background-size: 200% 200%;
    animation: adminScintillate 3s ease-in-out infinite;
    padding: 15px !important;
    border-radius: 8px;
    border: 2px solid #B8860B;
    font-weight: bold !important;
    font-family: Impact, Arial Black, sans-serif !important;
    color: #000 !important;
    text-shadow: 1px 1px 2px rgba(255, 215, 0, 0.3);
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
}

@keyframes adminScintillate {
    0% {
        background-position: 0% 50%;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
    }
    50% {
        background-position: 100% 50%;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
    }
    100% {
        background-position: 0% 50%;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
    }
}
</style>
@endif

@if($post->allReplies && $post->allReplies->count() > 0)
    @foreach($post->allReplies as $reply)
        @include('forum.post-recursive', ['post' => $reply, 'level' => $level + 1, 'thread' => $thread, 'board' => $board])
    @endforeach
@endif

@if($post->is_anonymous_post ?? false)
<style>
.anonymous-garbage-post {
    background: linear-gradient(135deg, #2F2F2F 0%, #1A1A1A 100%) !important;
    border: 2px solid #555 !important;
    color: #CCC !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5) !important;
}

.anonymous-garbage-post .post-header {
    background: #3E3E3E !important;
    border-bottom: 1px solid #555 !important;
    color: #999 !important;
}

.anonymous-garbage-post .post-content {
    background: #1A1A1A !important;
    color: #AAA !important;
}

.anonymous-garbage-post .post-text {
    color: #BBB !important;
}

.anonymous-garbage-post .reply-link,
.anonymous-garbage-post .quote-link {
    color: #8B008B !important;
}

.anonymous-garbage-post #hash-{{ $post->id }} {
    background: #333 !important;
    border: 1px solid #555 !important;
    color: #999 !important;
}
</style>
@endif