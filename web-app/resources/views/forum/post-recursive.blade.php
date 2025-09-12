<div class="post reply-post {{ $level > 0 ? 'nested-post' : '' }}" id="post{{ $post->id }}" 
     data-mine-type="reply" 
     data-mine-target="reply-{{ $post->id }}"
     data-mine-weight="40"
     data-thread-id="{{ $thread->id }}"
     data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}"
     data-post-id="{{ $post->id }}"
     style="{{ $level > 0 ? 'margin-left: ' . ($level * 20) . 'px; border-left: 2px solid #ddd; padding-left: 10px;' : '' }}">
    <div class="post-header">
        <span class="poster-info">
            Anonymous {{ $post->created_at->format('m/d/y H:i:s') }} No.{{ $post->id }}
            @if($post->parent_id)
                <a href="#post{{ $post->parent_id }}" class="quote-link">&gt;&gt;{{ $post->parent_id }}</a>
            @endif
            <a href="javascript:void(0)" class="reply-link" onclick="showReplyForm({{ $post->id }}, '{{ addslashes($post->content) }}')">[Reply]</a>
            <a href="javascript:void(0)" class="quote-link" onclick="quotePost({{ $post->id }}, '{{ addslashes($post->content) }}')">[Quote]</a>
        </span>
    </div>
    
    @if($post->image_filename)
    <div style="float: left; margin: 5px 15px 10px 0;">
        <div style="font-size: 8pt; margin-bottom: 3px;">
            File: {{ $post->image_original_name }}
        </div>
        <img src="{{ route('post.image', $post->id) }}" style="max-width: 200px; max-height: 200px;">
    </div>
    @endif
    
    <div class="post-content">
        {!! preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', 
             preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>', 
             nl2br(e($post->content)))) !!}
    </div>
    <div class="post-hash-preview" id="hash-{{ $post->id }}" style="font-family: monospace; font-size: 8pt; color: #888; margin-top: 5px; opacity: 0.6;">
        <span class="hash-label">sha256:</span>
        <span class="hash-value">calculating...</span>
        <span class="hash-bump-indicator" style="display: none; color: #ff6b35; font-weight: bold; margin-left: 10px;">🔥 21e8 BUMP!</span>
    </div>
    <div style="clear: both;"></div>
</div>

@if($post->allReplies && $post->allReplies->count() > 0)
    @foreach($post->allReplies as $reply)
        @include('forum.post-recursive', ['post' => $reply, 'level' => $level + 1, 'thread' => $thread, 'board' => $board])
    @endforeach
@endif