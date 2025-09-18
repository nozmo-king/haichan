<div class="post {{ $level > 0 ? 'nested-post' : 'reply-post' }}" id="post{{ $post->id }}"
     data-mine-type="reply"
     data-mine-target="reply-{{ $post->id }}"
     data-mine-weight="40"
     data-thread-id="{{ $thread->id }}"
     data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}"
     data-post-id="{{ $post->id }}">

    <div class="post-header">
        <span class="poster-info">
            Anonymous {{ $post->created_at->format('m/d/y H:i:s') }} No.{{ $post->id }}
            @if($post->parent_id)
                <a href="#post{{ $post->parent_id }}" class="quote-link">&gt;&gt;{{ $post->parent_id }}</a>
            @endif
            <a href="#reply-form">[Reply]</a>
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
                 onclick="this.style.maxWidth = this.style.maxWidth === 'none' ? '125px' : 'none'">
        </div>
        @endif

        <div class="post-text">
            {!! App\Helpers\MarkdownHelper::parseContent($post->content) !!}
        </div>
    </div>

</div>

@if($post->allReplies && $post->allReplies->count() > 0)
    @foreach($post->allReplies as $reply)
        @include('forum.post-recursive', ['post' => $reply, 'level' => $level + 1, 'thread' => $thread, 'board' => $board])
    @endforeach
@endif