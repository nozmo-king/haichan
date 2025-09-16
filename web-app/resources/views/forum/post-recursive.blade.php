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

    <div class="post-content">
        @if($post->image_filename)
        <div class="post-image">
            <div class="image-info">
                File: {{ $post->image_original_name }}
            </div>
            <img src="{{ route('post.image', $post->id) }}" class="thumbnail"
                 onclick="this.style.maxWidth = this.style.maxWidth === 'none' ? '125px' : 'none'">
        </div>
        @endif

        <div class="post-text">
            {!! preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>',
                 preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>',
                 nl2br(e($post->content)))) !!}
        </div>
    </div>

</div>

@if($post->allReplies && $post->allReplies->count() > 0)
    @foreach($post->allReplies as $reply)
        @include('forum.post-recursive', ['post' => $reply, 'level' => $level + 1, 'thread' => $thread, 'board' => $board])
    @endforeach
@endif