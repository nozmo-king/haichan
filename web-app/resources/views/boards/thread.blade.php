@extends('layout', ['boardCode' => $board->code])

@section('title', '/'.$board->code.'/ - '.$board->name)

@section('content')
<div style="text-align: center; margin: 10px 0;">
    <h1>/{{ $board->code }}/ - {{ $board->name }}</h1>
    <p style="font-size: 12px; color: var(--ib-text-muted);">{{ $board->description }}</p>
</div>

<div class="nav-links">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a> |
    <a href="#bottom">Bottom</a>
</div>

<!-- Original Post -->
<div class="post" style="margin: 20px 0;" 
     data-mineable="true"
     data-mine-id="{{ $thread->id }}" 
     data-mine-type="thread" 
     data-mine-difficulty="21e8"
     data-mine-content="thread_{{ $thread->id }}_{{ $thread->title }}"
     data-thread-id="{{ $thread->id }}" 
     data-board-code="{{ $board->code }}">
    <div class="post-header">
        <span class="post-name">
            @if($thread->user_id && $thread->bitcoinUser)
                <a href="{{ route('user.profile', $thread->user_id) }}" style="color: inherit; text-decoration: none; font-weight: bold;">{{ $thread->bitcoinUser->getDisplayName() }}</a>
            @else
                Anonymous
            @endif
        </span>
        @if($board->code === 'pol' && $thread->ip_address)
            {!! \App\Helpers\GeoIP::formatIPWithFlag($thread->ip_address, $board->code) !!}
        @endif
        {{ $thread->created_at->format('m/d/y(D) H:i:s') }}
        <span class="post-no">No.{{ $thread->id }}</span>
        @if($thread->accumulated_points > 0)
            <span style="color: var(--ib-accent); font-weight: bold;">[⚡{{ number_format($thread->accumulated_points, 1) }}]</span>
        @endif
        <button onclick="toggleQuickReply()" id="quick-reply-btn" class="tui-btn tui-btn-sm" style="margin-left: 10px;">💬 Reply</button>
        @if(session('bitcoin_auth_id') && ($thread->user_id === session('bitcoin_auth_id') || (session('bitcoin_auth_user') && (session('bitcoin_auth_user')->is_admin || session('bitcoin_auth_user')->is_moderator))))
            <form method="POST" action="{{ route('threads.delete.user', $thread->id) }}" style="display: inline-block; margin-left: 8px;" onsubmit="return confirm('Delete this thread?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="tui-btn tui-btn-danger tui-btn-sm">🗑️ Delete</button>
            </form>
        @endif
    </div>
    
    @if($thread->image_path)
    <div style="float: left; margin: 10px 15px 10px 0;">
        <a href="{{ route('thread.image', $thread->id) }}" target="_blank">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}" data-mine-type="image"
                 style="max-width: 250px; max-height: 250px; border: 1px solid var(--ib-border);">
        </a>
    </div>
    @endif
    
    <div class="post-content">
        <strong>{{ $thread->title ?: 'No Subject' }}</strong><br>
        {{ $thread->content }}
    </div>
    
    <div style="clear: both;"></div>
</div>

<!-- Replies -->
@foreach($posts as $post)
<div class="post" 
     data-mineable="true"
     data-mine-id="{{ $post->id }}" 
     data-mine-type="post" 
     data-mine-difficulty="21e8"
     data-mine-content="post_{{ $post->id }}_{{ $thread->id }}"
     data-post-id="{{ $post->id }}" 
     data-thread-id="{{ $thread->id }}" 
     data-board-code="{{ $board->code }}">
    <div class="post-header">
        <span class="post-name">
            @if($post->user_id && $post->bitcoinUser)
                <a href="{{ route('user.profile', $post->user_id) }}" style="color: inherit; text-decoration: none; font-weight: bold;">{{ $post->bitcoinUser->getDisplayName() }}</a>
            @else
                Anonymous
            @endif
        </span>
        @if($board->code === 'pol' && $post->ip_address)
            {!! \App\Helpers\GeoIP::formatIPWithFlag($post->ip_address, $board->code) !!}
        @endif
        {{ $post->created_at->format('m/d/y(D) H:i:s') }}
        <span class="post-no clickable-hash" onclick="quotePost('{{ $post->id }}')">No.{{ $post->id }}</span>
        @if($post->accumulated_points > 0)
            <span style="color: var(--ib-accent); font-weight: bold;">[⚡{{ number_format($post->accumulated_points, 1) }}]</span>
        @endif
        <button onclick="quotePost('{{ $post->id }}')" class="tui-btn-link" style="font-size: 10px; margin-left: 8px;">» Quote</button>
    </div>
    
    @if($post->image_path)
    <div style="float: left; margin: 10px 15px 10px 0;">
        <a href="{{ route('post.image', $post->id) }}" target="_blank">
            <img src="{{ route('post.image', $post->id) }}" 
                 data-hash="{{ $post->image_hash ?? '' }}" data-post-id="{{ $post->id }}" data-mine-type="image"
                 style="max-width: 200px; max-height: 200px; border: 1px solid var(--ib-border);">
        </a>
    </div>
    @endif
    
    <div class="post-content">
        {{ $post->content }}
    </div>
    
    <div style="clear: both;"></div>
</div>
@endforeach

<!-- Unified Reply Form -->
<div id="reply-form" class="tui-reply-form" style="display: none; margin: 20px 0; background: linear-gradient(135deg, #F5F5DC, #FFFACD); border: 2px solid #708B75; border-radius: 12px; box-shadow: 0 4px 20px rgba(112, 139, 117, 0.2); overflow: hidden;">
    <div class="tui-reply-header" style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="tui-reply-title" style="font-size: 18px; font-weight: 600;">💬 Quick Reply</div>
        <button type="button" class="tui-btn-close" onclick="toggleQuickReply()" style="background: none; border: none; color: #F5F5DC; font-size: 20px; cursor: pointer; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'" onmouseout="this.style.backgroundColor='transparent'">×</button>
    </div>
    
    <div class="tui-reply-container">
        <form method="POST" action="/{{ $board->code }}/{{ $thread->id }}/reply" enctype="multipart/form-data" class="unified-post-form">
            @csrf
            
            <div class="tui-field">
                <label class="tui-label" for="post-name">Name (optional)</label>
                <input type="text" name="name" id="post-name" class="tui-input" placeholder="Anonymous">
                <div class="tui-hint">Leave blank for anonymous posting</div>
            </div>
            
            <div class="tui-field">
                <label class="tui-label" for="post-content">Comment</label>
                <textarea name="content" id="post-content" class="tui-textarea" required rows="6" cols="48" 
                          placeholder="Enter your reply... Use >>hash to quote posts"></textarea>
                <div class="tui-hint">Required. Max 5000 characters.</div>
            </div>
            
            <div class="tui-field">
                <label class="tui-label" for="post-file">File Upload (optional)</label>
                <input type="file" name="image" id="post-file" class="tui-file" accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif" 
                       onchange="previewPostImage(this)">
                <div class="tui-hint">Max 25MB. Supports: JPEG, PNG, GIF, WebP, WebM, MP4, SVG, etc.</div>
                
                <!-- Preview -->
                <div id="post-image-preview" class="tui-preview" style="display: none;">
                    <img id="post-preview-img" alt="Post Preview">
                    <div id="post-file-info" class="tui-preview-info"></div>
                </div>
            </div>
            
            <!-- Image Hash Alternative -->
            <div class="tui-alternative">
                <label class="tui-label" for="post_image_hash">OR use existing image hash:</label>
                <input type="text" name="image_hash" id="post_image_hash" class="tui-input tui-mono" 
                       placeholder="Paste image hash from library..." onchange="handlePostHashInput()">
                <div class="tui-hint">Copy hash from Image Library instead of uploading.</div>
            </div>
            
            <!-- Anonymous posting option -->
            <div class="tui-field">
                <label class="tui-checkbox-label">
                    <input type="checkbox" name="post_anonymous" value="1" id="post-anonymous">
                    <span class="tui-checkmark"></span>
                    Post anonymously (even if logged in)
                </label>
            </div>
            
            <!-- PoW fields temporarily removed - using simplified reply system -->
            
            <div class="tui-actions">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="tui-btn tui-btn-primary" id="reply-submit-btn">
                            📤 Post Reply
                        </button>
                        <button type="button" class="tui-btn tui-btn-outline" onclick="toggleQuickReply()">
                            Cancel
                        </button>
                    </div>
                    <div id="reply-mining-status" style="font-size: 12px; color: #6B7A6B;">
                        Fill content to begin mining
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="nav-links">
    <a name="bottom"></a>
    <a href="#">Top</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a>
</div>

<script>
function toggleQuickReply() {
    const replyForm = document.getElementById('reply-form');
    const quickBtn = document.getElementById('quick-reply-btn');

    if (!replyForm || !quickBtn) return;

    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        replyForm.style.display = 'block';
        replyForm.scrollIntoView({ behavior: 'smooth' });
        quickBtn.textContent = '[Hide Reply]';
    } else {
        replyForm.style.display = 'none';
        quickBtn.textContent = '[Reply]';
    }
}

function previewPostImage(input) {
    const preview = document.getElementById('post-image-preview');
    const img = document.getElementById('post-preview-img');
    const info = document.getElementById('post-file-info');
    const hashInput = document.getElementById('post_image_hash');
    
    if (input.files?.[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = (e) => {
            img.src = e.target.result;
            preview.style.display = 'block';
            info.textContent = `${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`;
        };
        
        reader.readAsDataURL(file);
        hashInput.value = '';
    } else {
        preview.style.display = 'none';
    }
}

function handlePostHashInput() {
    const hashInput = document.getElementById('post_image_hash');
    const fileInput = document.getElementById('post-file');
    const preview = document.getElementById('post-image-preview');
    
    if (hashInput.value.trim()) {
        fileInput.value = '';
        preview.style.display = 'none';
        
        if (hashInput.value.length === 64 && /^[a-f0-9]{64}$/i.test(hashInput.value)) {
            hashInput.style.borderColor = '#28a745';
        } else {
            hashInput.style.borderColor = '#dc3545';
        }
    } else {
        hashInput.style.borderColor = '';
    }
}

function quotePost(postId) {
    // Open reply form if closed
    const replyForm = document.getElementById('reply-form');
    const quickBtn = document.getElementById('quick-reply-btn');
    
    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        toggleQuickReply();
    }
    
    // Insert quote into textarea
    const contentArea = document.getElementById('post-content');
    if (contentArea) {
        const quote = `>>${postId}\n`;
        const cursorPos = contentArea.selectionStart || contentArea.value.length;
        const textBefore = contentArea.value.substring(0, cursorPos);
        const textAfter = contentArea.value.substring(contentArea.selectionEnd || cursorPos);
        
        contentArea.value = textBefore + quote + textAfter;
        contentArea.selectionStart = contentArea.selectionEnd = cursorPos + quote.length;
        contentArea.focus();
        
        // Show visual feedback
        showQuoteFeedback();
    }
}

function showQuoteFeedback() {
    const feedback = document.createElement('div');
    feedback.textContent = 'Post quoted!';
    feedback.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--ib-accent);
        color: var(--ib-bg);
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
        z-index: 10000;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(feedback);
    setTimeout(() => {
        feedback.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => feedback.remove(), 300);
    }, 2000);
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .clickable-hash {
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .clickable-hash:hover {
        color: var(--ib-accent) !important;
        text-decoration: underline;
    }
    
    /* Reply button styling */
    #reply-submit-btn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        background: #ccc !important;
        color: #666 !important;
    }
    
    #reply-submit-btn:enabled {
        opacity: 1 !important;
        cursor: pointer !important;
    }
    
    /* IP flags styling */
    .ip-flag {
        margin: 0 5px;
        font-size: 16px;
        cursor: help;
        transition: transform 0.2s ease;
    }
    
    .ip-flag:hover {
        transform: scale(1.2);
    }
`;
document.head.appendChild(style);

// Simple Reply Form Handler (PoW temporarily disabled)
document.addEventListener('DOMContentLoaded', function() {
    const replyForm = document.querySelector('.unified-post-form');
    const contentInput = document.getElementById('post-content');
    const submitBtn = document.getElementById('reply-submit-btn');
    const miningStatus = document.getElementById('reply-mining-status');
    
    if (!replyForm || !contentInput || !submitBtn) {
        console.log('Reply form elements not found');
        return;
    }
    
    console.log('Reply form initialized (PoW temporarily bypassed)');
    
    // Simple content validation
    function validateReplyContent() {
        const content = contentInput.value.trim();
        
        if (content.length >= 5) {
            // Enable submit button
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            submitBtn.style.background = 'linear-gradient(135deg, #708B75, #5a7860)';
            submitBtn.textContent = '📤 Post Reply';
            miningStatus.innerHTML = '<span style="color: #28a745;">✅ Ready to post!</span>';
        } else {
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
            submitBtn.style.background = '#ccc';
            submitBtn.textContent = '📤 Post Reply';
            miningStatus.innerHTML = '<span style="color: #666;">Enter at least 5 characters to post</span>';
        }
    }
    
    // Validate content as user types
    contentInput.addEventListener('input', validateReplyContent);
    
    // Form submission handler
    replyForm.addEventListener('submit', function(e) {
        const contentText = contentInput.value.trim();
        
        if (contentText.length < 5) {
            e.preventDefault();
            miningStatus.innerHTML = '<span style="color: #dc3545;">⚠️ Please enter at least 5 characters</span>';
            return;
        }
        
        console.log('Submitting reply (no PoW required)');
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Posting Reply...';
        miningStatus.innerHTML = '<span style="color: #708B75;">🔄 Submitting your reply...</span>';
    });
    
    // Initial validation
    validateReplyContent();
});

// Auto-focus content area when reply form is opened
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus content area when reply form is opened
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'style') {
                const replyForm = document.getElementById('reply-form');
                if (replyForm && replyForm.style.display === 'block') {
                    const contentArea = document.getElementById('post-content');
                    if (contentArea) {
                        setTimeout(() => contentArea.focus(), 100);
                    }
                }
            }
        });
    });
    
    const replyForm = document.getElementById('reply-form');
    if (replyForm) {
        observer.observe(replyForm, { attributes: true, attributeFilter: ['style'] });
    }
});
</script>

@endsection
