@extends('layout')

@section('title', $board->code . ' - ' . $board->name)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > {{ $board->code }}
</div>

<h2>{{ $board->code }} - {{ $board->name }}</h2>
<p>{{ $board->description }}</p>

<div style="margin: 15px 0;">
    <a href="{{ route('forum.create', $board->code) }}" 
       id="create-thread-btn"
       class="emoji-animated-btn"
       style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(112, 139, 117, 0.3);"
       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px rgba(112, 139, 117, 0.4)'; if(window.emojiAnimator) window.emojiAnimator.startElementAnimation(this.querySelector('#create-thread-emoji'), ['🌱', '✨', '📝', '🌟'], 120);"
       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(112, 139, 117, 0.3)'; if(window.emojiAnimator) window.emojiAnimator.stopElementAnimation(this.querySelector('#create-thread-emoji')); this.querySelector('#create-thread-emoji').textContent='🌱';">
        <span id="create-thread-emoji">🌱</span> Create New Thread
    </a>
</div>

<table class="thread-list">
    <thead>
        <tr>
            <th>Thread</th>
            <th>Replies</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
        @forelse($threads as $thread)
            <tr>
                <td colspan="3">
                    <div class="thread-preview" data-mine-type="thread" data-thread-id="{{ $thread->id }}" data-board-code="{{ $board->code }}">
                        <div class="thread-header" style="cursor: pointer;" onclick="toggleThread({{ $thread->id }})">
                            <div class="thread-title">
                                <span class="expand-icon emoji-animated" id="icon-{{ $thread->id }}" style="display: inline-block; transition: transform 0.2s ease; cursor: pointer; margin-right: 6px; font-size: 14px;" 
                                      onmouseover="this.style.transform='scale(1.2)'; if(this.textContent === '📁') { if(window.emojiAnimator) window.emojiAnimator.startElementAnimation(this, ['📁', '📂', '✨', '📁'], 150); } else { if(window.emojiAnimator) window.emojiAnimator.startElementAnimation(this, ['📂', '📁', '⭐', '📂'], 150); }"
                                      onmouseout="this.style.transform='scale(1)'; if(window.emojiAnimator) window.emojiAnimator.stopElementAnimation(this);">📁</span>
                                <a href="{{ route('forum.thread', [$board->code, $thread->id]) }}">{{ $thread->title }}</a>
                            </div>
                            <div class="thread-meta">
                                by {{ $thread->getAuthorDisplayName() }} - {{ $thread->created_at->format('m/d/y H:i') }} | {{ $thread->posts_count }} replies
                            </div>
                        </div>
                        
                        <div class="thread-content" id="content-{{ $thread->id }}" style="display: none;">
                            <div class="post-preview">
                                @if($thread->image_path)
                                    <div class="thread-preview-image" style="float: left; margin: 0 20px 20px 0;">
                                        <img src="{{ route('thread.image', $thread->id) }}" alt="{{ $thread->image_filename }}" 
                                             style="width: 180px; height: 180px; border: 1px solid #ccc; border-radius: 5px; object-fit: cover;">
                                    </div>
                                @endif
                                {{ Str::limit($thread->content, 800) }}
                                <div style="clear: both;"></div>
                            </div>
                            
                            @if($thread->posts->count() > 0)
                                <div class="replies-preview">
                                    @foreach($thread->posts->take(7) as $post)
                                        <div class="reply-preview" data-mine-type="post" data-post-id="{{ $post->id }}" data-thread-id="{{ $thread->id }}" data-board-code="{{ $board->code }}">
                                            @if($post->image_path)
                                                <div class="post-preview-image" style="float: left; margin: 0 15px 15px 0;">
                                                    <img src="{{ route('post.image', $post->id) }}" alt="{{ $post->image_filename }}" 
                                                         style="width: 120px; height: 120px; border: 1px solid #ccc; border-radius: 4px; object-fit: cover;">
                                                </div>
                                            @endif
                                            <span class="reply-author">{{ $post->getAuthorDisplayName() }}</span>: 
                                            {{ Str::limit($post->content, 350) }}
                                            <div style="clear: both;"></div>
                                            
                                            @if($post->replies->count() > 0)
                                                @foreach($post->replies->take(3) as $reply)
                                                    <div class="nested-reply" data-mine-type="post" data-post-id="{{ $reply->id }}" data-thread-id="{{ $thread->id }}" data-board-code="{{ $board->code }}">
                                                        @if($reply->image_path)
                                                            <div class="reply-preview-image" style="float: left; margin: 0 12px 12px 0;">
                                                                <img src="{{ route('post.image', $reply->id) }}" alt="{{ $reply->image_filename }}" 
                                                                     style="width: 80px; height: 80px; border: 1px solid #ccc; border-radius: 3px; object-fit: cover;">
                                                            </div>
                                                        @endif
                                                        <span class="reply-author">{{ $reply->getAuthorDisplayName() }}</span>: 
                                                        {{ Str::limit($reply->content, 280) }}
                                                        <div style="clear: both;"></div>
                                                        
                                                        @if($reply->replies->count() > 0)
                                                            @foreach($reply->replies->take(2) as $nestedReply)
                                                                <div class="deeply-nested-reply" data-mine-type="post" data-post-id="{{ $nestedReply->id }}" data-thread-id="{{ $thread->id }}" data-board-code="{{ $board->code }}" style="margin-left: 30px; margin-top: 3px; font-size: 12px; color: #777;">
                                                                    @if($nestedReply->image_path)
                                                                        <div style="float: left; margin: 0 10px 10px 0;">
                                                                            <img src="{{ route('post.image', $nestedReply->id) }}" alt="{{ $nestedReply->image_filename }}" 
                                                                                 style="width: 60px; height: 60px; border: 1px solid #ccc; border-radius: 3px; object-fit: cover;">
                                                                        </div>
                                                                    @endif
                                                                    <span class="reply-author">{{ $nestedReply->getAuthorDisplayName() }}</span>: 
                                                                    {{ Str::limit($nestedReply->content, 240) }}
                                                                    <div style="clear: both;"></div>
                                                                </div>
                                                            @endforeach
                                                            @if($reply->replies->count() > 2)
                                                                <div class="more-replies" style="margin-left: 30px; font-size: 11px;">... {{ $reply->replies->count() - 2 }} more nested replies</div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endforeach
                                                @if($post->replies->count() > 3)
                                                    <div class="more-replies">... {{ $post->replies->count() - 3 }} more replies</div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($thread->posts_count > 7)
                                        <div class="more-posts">
                                            <a href="{{ route('forum.thread', [$board->code, $thread->id]) }}">
                                                View all {{ $thread->posts_count }} replies →
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No threads yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 30px; text-align: center; padding: 20px; border-top: 1px solid #ddd;">
    {{ $threads->links() }}
</div>
<script>
function toggleThread(threadId) {
    const content = document.getElementById('content-' + threadId);
    const icon = document.getElementById('icon-' + threadId);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.textContent = '📂';
        // Celebration animation for opening
        if (window.emojiAnimator) {
            window.emojiAnimator.startElementAnimation(icon, ['📂', '✨', '🌟', '📂'], 200);
            setTimeout(() => {
                if (window.emojiAnimator) window.emojiAnimator.stopElementAnimation(icon);
                icon.textContent = '📂';
            }, 800);
        }
    } else {
        content.style.display = 'none';
        icon.textContent = '📁';
        // Closing animation
        if (window.emojiAnimator) {
            window.emojiAnimator.startElementAnimation(icon, ['📁', '💨', '📁'], 150);
            setTimeout(() => {
                if (window.emojiAnimator) window.emojiAnimator.stopElementAnimation(icon);
                icon.textContent = '📁';
            }, 450);
        }
    }
}

// Add expand/collapse all functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add expand all / collapse all buttons
    const controlsDiv = document.createElement('div');
    controlsDiv.style.cssText = 'margin: 10px 0; text-align: center;';
    controlsDiv.innerHTML = `
        <button onclick="expandAll()" style="margin: 0 5px; padding: 5px 10px; background: #708B75; color: white; border: none; border-radius: 3px; cursor: pointer;">Expand All</button>
        <button onclick="collapseAll()" style="margin: 0 5px; padding: 5px 10px; background: #9AB87A; color: white; border: none; border-radius: 3px; cursor: pointer;">Collapse All</button>
    `;
    
    const table = document.querySelector('.thread-list');
    table.parentNode.insertBefore(controlsDiv, table);
});

function expandAll() {
    const contents = document.querySelectorAll('[id^="content-"]');
    const icons = document.querySelectorAll('[id^="icon-"]');
    
    contents.forEach(content => content.style.display = 'block');
    icons.forEach(icon => icon.textContent = '▼');
}

function collapseAll() {
    const contents = document.querySelectorAll('[id^="content-"]');
    const icons = document.querySelectorAll('[id^="icon-"]');
    
    contents.forEach(content => content.style.display = 'none');
    icons.forEach(icon => icon.textContent = '▶');
}
</script>

@endsection