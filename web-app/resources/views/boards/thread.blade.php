@extends('layout')

@section('title', ($thread->title ?: 'Thread') . ' - /' . $board->code . '/')

@section('content')
<div style="text-align: center; margin: 10px 0;">
    <h1>/{{ $board->code }}/ - {{ $board->title }}</h1>
    <p style="font-size: 12px; color: var(--ib-text-muted);">{{ $board->description }}</p>
</div>

<div class="nav-links">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a> |
    <a href="#bottom">Bottom</a>
</div>

<!-- Original Post -->
<div class="post" style="margin: 20px 0;">
    <div class="post-header">
        <span class="post-name">
            @if($thread->user_id && $thread->bitcoinUser)
                {{ $thread->bitcoinUser->getDisplayName() }}
            @else
                Anonymous
            @endif
        </span>
        {{ $thread->created_at->format('m/d/y(D) H:i:s') }}
        <span class="post-no">No.{{ $thread->id }}</span>
        @if($thread->accumulated_points > 0)
            <span style="color: var(--ib-accent); font-weight: bold;">[⚡{{ number_format($thread->accumulated_points, 1) }}]</span>
        @endif
        <button onclick="toggleQuickReply()" id="quick-reply-btn" class="tui-btn tui-btn-sm" style="margin-left: 10px;">💬 Reply</button>
    </div>
    
    @if($thread->image_path)
    <div style="float: left; margin: 10px 15px 10px 0;">
        <a href="{{ route('thread.image', $thread->id) }}" target="_blank">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}"
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
@foreach($thread->posts as $post)
<div class="post">
    <div class="post-header">
        <span class="post-name">
            @if($post->user_id && $post->bitcoinUser)
                {{ $post->bitcoinUser->getDisplayName() }}
            @else
                Anonymous
            @endif
        </span>
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
                 data-hash="{{ $post->image_hash ?? '' }}" data-post-id="{{ $post->id }}"
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
<div id="reply-form" class="tui-reply-form" style="display: none; margin: 20px 0;">
    <div class="tui-reply-header">
        <div class="tui-reply-title">💬 Quick Reply</div>
        <button type="button" class="tui-btn-close" onclick="toggleQuickReply()">×</button>
    </div>
    
    <div class="tui-reply-container">
        <form method="POST" action="/{{ $board->code }}/{{ $thread->id }}" enctype="multipart/form-data" class="unified-post-form">
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
            
            <!-- Hidden PoW fields (managed by unified system) -->
            <input type="hidden" name="pow_nonce" required>
            <input type="hidden" name="pow_hash" required>
            <input type="hidden" name="pow_challenge_id" required>
            
            <div class="tui-actions">
                <button type="submit" class="tui-btn tui-btn-primary tui-btn-disabled" disabled>
                    Mine Proof First
                </button>
                <button type="button" class="tui-btn tui-btn-outline" onclick="toggleQuickReply()">
                    Cancel
                </button>
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
`;
document.head.appendChild(style);

// Unified system will automatically handle PoW mining for this form
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