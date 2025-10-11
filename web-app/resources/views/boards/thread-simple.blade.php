@extends('layout')

@section('title', $thread->title . ' - /' . $board->code . '/')

@section('content')
<div class="nav-links">
    <a href="#bottom">Bottom</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a>
</div>

<hr>

@php
    $authorTag = $thread->user_id ? 
        '!!' . substr(hash('sha256', $thread->user_id . $thread->created_at->timestamp), 0, 6) : 
        null;
@endphp

<div class="thread" id="thread-{{ $thread->id }}">
    <div class="post op-post" id="op">
        <div class="post-header">
            <input type="checkbox" class="post-checkbox" name="post[]" value="{{ $thread->id }}">
            <span class="post-subject">{{ $thread->title }}</span>
            <span class="post-author">{{ $thread->author_name ?? 'Anonymous' }}</span>
            @if($authorTag && $thread->user)
                <span class="author-tag">{{ $authorTag }}</span>
            @endif
            <span class="post-date">{{ $thread->created_at->format('m/d/y(D)H:i:s') }}</span>
            <span class="post-number">
                <a href="#op" class="post-link">No.</a><a href="#op" class="post-link-ref">{{ $thread->id }}</a>
            </span>
            @auth
                @if(session('bitcoin_auth_id') == $thread->user_id || session('bitcoin_auth_user')->is_mod)
                    <span class="post-menu-toggle" onclick="togglePostMenu(this, {{ $thread->id }}, 'thread')">[▾]</span>
                    <div class="post-menu" id="menu-thread-{{ $thread->id }}" style="display: none;">
                        <button class="delete-btn" onclick="deleteItem({{ $thread->id }}, 'thread')">Delete</button>
                    </div>
                @endif
            @endauth
            <a href="#" onclick="toggleQuickReply(); return false;" id="quick-reply-btn">[Reply]</a>
        </div>
        <div class="post-content">
            @if($thread->image_path)
                <div class="post-image">
                    <div class="image-info">
                        <a href="{{ Storage::url($thread->image_path) }}" target="_blank">{{ $thread->image_filename ?? 'image' }}</a>
                        @if($thread->image_hash)
                            <span class="image-hash-info">{{ substr($thread->image_hash, 0, 8) }}...</span>
                        @endif
                    </div>
                    <a href="{{ Storage::url($thread->image_path) }}" target="_blank">
                        <img src="{{ Storage::url($thread->image_path) }}" alt="Thread image" class="thread-image">
                    </a>
                </div>
            @endif
            <div class="post-text">{!! nl2br(e($thread->content)) !!}</div>
        </div>
    </div>
</div>

@foreach($thread->posts as $post)
<div class="reply" id="reply-{{ $post->id }}">
    <div class="post">
        <div class="post-header">
            <input type="checkbox" class="post-checkbox" name="post[]" value="{{ $post->id }}">
            <span class="post-author">{{ $post->author_name ?? 'Anonymous' }}</span>
            @if($post->user_id)
                @php
                    $postAuthorTag = '!!' . substr(hash('sha256', $post->user_id . $post->created_at->timestamp), 0, 6);
                @endphp
                <span class="author-tag">{{ $postAuthorTag }}</span>
            @endif
            <span class="post-date">{{ $post->created_at->format('m/d/y(D)H:i:s') }}</span>
            <span class="post-number">
                <a href="#reply-{{ $post->id }}" class="post-link">No.</a><a href="#reply-{{ $post->id }}" class="post-link-ref">{{ $post->id }}</a>
            </span>
            @auth
                @if(session('bitcoin_auth_id') == $post->user_id || session('bitcoin_auth_user')->is_mod)
                    <span class="post-menu-toggle" onclick="togglePostMenu(this, {{ $post->id }}, 'post')">[▾]</span>
                    <div class="post-menu" id="menu-post-{{ $post->id }}" style="display: none;">
                        <button class="delete-btn" onclick="deleteItem({{ $post->id }}, 'post')">Delete</button>
                    </div>
                @endif
            @endauth
        </div>
        <div class="post-content">
            @if($post->image_path)
                <div class="post-image">
                    <div class="image-info">
                        <a href="{{ Storage::url($post->image_path) }}" target="_blank">{{ $post->image_filename ?? 'image' }}</a>
                        @if($post->image_hash)
                            <span class="image-hash-info">{{ substr($post->image_hash, 0, 8) }}...</span>
                        @endif
                    </div>
                    <a href="{{ Storage::url($post->image_path) }}" target="_blank">
                        <img src="{{ Storage::url($post->image_path) }}" alt="Post image">
                    </a>
                </div>
            @endif
            <div class="post-text">{!! nl2br(e($post->content)) !!}</div>
        </div>
    </div>
</div>
@endforeach

<hr>

<!-- Quick Reply Form -->
<div class="haichan-form-panel" id="reply-form" style="display: none; margin: 20px 0;">
    <h3 class="panel-title">📝 Post Reply</h3>
    
    <form action="/{{ $board->code }}/{{ $thread->id }}/reply" method="POST" enctype="multipart/form-data" class="unified-post-form">
        @csrf
        
        <!-- Error display -->
        @if($errors->any())
            <div class="tui-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <div class="tui-form">
            <div class="tui-field">
                <label class="tui-label" for="post-content">Comment <span class="required">*</span></label>
                <textarea name="content" id="post-content" required minlength="5" maxlength="5000" 
                          class="tui-textarea" rows="6" 
                          placeholder="Enter your reply...">{{ old('content') }}</textarea>
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
                    <div id="reply-status" style="font-size: 12px; color: #6B7A6B;">
                        <!-- Status messages -->
                    </div>
                </div>
            </div>
        </div>
    </form>
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

// Simple form validation
document.addEventListener('DOMContentLoaded', function() {
    const replyForm = document.querySelector('.unified-post-form');
    const contentInput = document.getElementById('post-content');
    const submitBtn = document.getElementById('reply-submit-btn');
    const statusDiv = document.getElementById('reply-status');
    
    if (!replyForm) return;
    
    // Basic form validation
    replyForm.addEventListener('submit', function(e) {
        const content = contentInput.value.trim();
        
        if (content.length < 5) {
            e.preventDefault();
            statusDiv.textContent = 'Content must be at least 5 characters.';
            statusDiv.style.color = '#dc3545';
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Posting...';
        statusDiv.textContent = 'Submitting reply...';
        statusDiv.style.color = '#6B7A6B';
    });
});

// Image preview functions
function previewPostImage(input) {
    const preview = document.getElementById('post-image-preview');
    const img = document.getElementById('post-preview-img');
    const info = document.getElementById('post-file-info');
    const hashInput = document.getElementById('post_image_hash');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            info.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;
            preview.style.display = 'block';
            
            // Clear hash input when file is selected
            hashInput.value = '';
        };
        
        reader.readAsDataURL(file);
    }
}

function handlePostHashInput() {
    const hashInput = document.getElementById('post_image_hash');
    const fileInput = document.getElementById('post-file');
    const preview = document.getElementById('post-image-preview');
    
    if (hashInput.value) {
        fileInput.value = '';
        preview.style.display = 'none';
    }
}

// Delete functions
function togglePostMenu(element, id, type) {
    const menu = document.getElementById('menu-' + type + '-' + id);
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}

function deleteItem(id, type) {
    if (!confirm('Are you sure you want to delete this ' + type + '?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = type === 'thread' ? 
        `/threads/${id}/delete` : 
        `/posts/${id}/delete`;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection