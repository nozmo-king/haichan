@extends('layout')

@section('title', $board->code . ' - ' . $board->name)

@section('content')
<div style="text-align: center; margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #708B75, #5a7860); border-radius: 8px; box-shadow: 0 2px 8px rgba(112, 139, 117, 0.3);">
    <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #F5F5DC;">/{{ $board->code }}/ - {{ $board->name }}</h1>
    <p style="font-size: 14px; color: #E6E1D6; margin: 8px 0 0 0;">{{ $board->description }}</p>
    <p style="font-size: 12px; color: #FAFA0B; margin: 5px 0 0 0;">⚡ Threads with Proof-of-Work mining</p>
</div>

<div style="margin: 15px 0;">
    <a href="{{ route('forum.create', $board->code) }}" 
       id="create-thread-btn"
       class="emoji-animated-btn"
       style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(112, 139, 117, 0.3);"
>
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
                        <div class="thread-header" style="cursor: pointer;" data-thread-id="{{ $thread->id }}">
                            <div class="thread-title">
                                <span class="expand-icon emoji-animated" id="icon-{{ $thread->id }}" style="display: inline-block; transition: transform 0.2s ease; cursor: pointer; margin-right: 6px; font-size: 14px;">📁</span>
                                <a href="{{ route('forum.thread', [$board->code, $thread->id]) }}">{{ $thread->title }}</a>
                            </div>
                            <div class="thread-meta">
                                by {{ $thread->getAuthorDisplayName() }} - {{ $thread->created_at->format('m/d/y H:i') }} | {{ $thread->posts_count }} replies
                                @if($thread->accumulated_points > 0)
                                    | <span id="board-thread-points-{{ $thread->id }}" class="pow-points" style="color: #2e7d32; font-weight: bold;">[⚡{{ number_format($thread->accumulated_points, 1) }}]</span>
                                @else
                                    <span id="board-thread-points-{{ $thread->id }}" class="pow-points" style="color: #2e7d32; font-weight: bold; display: none;">[⚡0.0]</span>
                                @endif
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
<script nonce="{{ app('csp_nonce') }}">
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
        <button id="expand-all-btn" style="margin: 0 5px; padding: 5px 10px; background: #708B75; color: white; border: none; border-radius: 3px; cursor: pointer;">Expand All</button>
        <button id="collapse-all-btn" style="margin: 0 5px; padding: 5px 10px; background: #9AB87A; color: white; border: none; border-radius: 3px; cursor: pointer;">Collapse All</button>
    `;
    
    const table = document.querySelector('.thread-list');
    table.parentNode.insertBefore(controlsDiv, table);
    
    // Add event listeners to buttons
    document.getElementById('expand-all-btn').addEventListener('click', expandAll);
    document.getElementById('collapse-all-btn').addEventListener('click', collapseAll);
    
    // Add event listeners to thread headers
    document.querySelectorAll('.thread-header').forEach(header => {
        header.addEventListener('click', function() {
            const threadId = this.dataset.threadId;
            if (threadId) {
                toggleThread(threadId);
            }
        });
    });
    
    // Listen for mining events to update board view
    document.addEventListener('proofSubmitted', function(e) {
        console.log('🎯 Board: Mining proof submitted, updating points');
        updateBoardPoints();
    });
    
    window.addEventListener('mining:complete', function(e) {
        console.log('🎯 Board: Mining complete, updating points');
        setTimeout(() => updateBoardPoints(), 500);
    });
});

// Function to update board point displays
async function updateBoardPoints() {
    try {
        const response = await fetch('/api/boards/{{ $board->code }}/thread-order');
        const data = await response.json();
        
        let updateCount = 0;
        data.threads.forEach(thread => {
            const pointSpan = document.getElementById(`board-thread-points-${thread.id}`);
            if (pointSpan) {
                const newPoints = parseFloat(thread.accumulated_points).toFixed(1);
                pointSpan.innerHTML = `[⚡${newPoints}]`;
                pointSpan.style.display = 'inline';
                
                // Add animation
                pointSpan.style.backgroundColor = 'rgba(46, 125, 50, 0.2)';
                setTimeout(() => {
                    pointSpan.style.backgroundColor = '';
                }, 1000);
                
                updateCount++;
            }
        });
        
        console.log(`✅ Board: Updated ${updateCount} thread point displays`);
    } catch (error) {
        console.error('❌ Board: Failed to update points:', error);
    }
}

// Test function for board
window.testBoardUpdate = function() {
    console.log('🧪 Testing board update...');
    updateBoardPoints();
};

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