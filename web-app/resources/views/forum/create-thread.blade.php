@extends('layout')

@section('title', 'Create Thread - ' . $board->code)

@section('content')
<div class="tui-window">
    <div class="tui-header">
        <div class="tui-dots">
            <div class="tui-dot"></div>
            <div class="tui-dot"></div>
            <div class="tui-dot"></div>
        </div>
        <div class="tui-title">Create Thread in /{{ $board->code }}/</div>
        <div class="tui-badge">PoW Required</div>
    </div>
    
    <div class="tui-p">
        <form action="{{ route('board.store', $board->code) }}" method="POST" enctype="multipart/form-data" id="unified-thread-form">
            @csrf
            
            <!-- Title Field -->
            <div class="tui-field">
                <label class="tui-label" for="title">Thread Title</label>
                <input type="text" name="title" id="title" class="tui-input" 
                       required maxlength="255" value="{{ old('title') }}" 
                       placeholder="Enter thread title...">
                @error('title')
                    <div class="tui-error">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Content Field -->
            <div class="tui-field">
                <label class="tui-label" for="content">Thread Content</label>
                <textarea name="content" id="content" class="tui-textarea" 
                          required maxlength="5000" rows="8" 
                          placeholder="Enter your message...">{{ old('content') }}</textarea>
                @error('content')
                    <div class="tui-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Anonymous Option -->
            @if(session('bitcoin_auth_id'))
            <div class="tui-field">
                <label class="tui-checkbox">
                    <input type="checkbox" name="post_anonymous" value="1">
                    <span class="checkmark"></span>
                    Post as Anonymous
                </label>
            </div>
            @endif
            
            <!-- Image Upload - Cleaner Layout -->
            <div class="tui-field">
                <label class="tui-label" for="image">Image (Required)</label>
                
                <!-- File Upload -->
                <div class="image-upload-section">
                    <input type="file" name="image" id="image" class="tui-file" 
                           accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif" 
                           onchange="previewImage(this)">
                    <div class="tui-hint">Upload: Max 25MB • Formats: JPEG, PNG, GIF, WebP, WebM, MP4, SVG</div>
                    
                    <!-- Preview -->
                    <div id="image-preview" class="tui-preview" style="display: none;">
                        <img id="preview-img" alt="Preview">
                        <div id="file-info" class="tui-preview-info"></div>
                    </div>
                </div>
                
                <!-- OR Divider -->
                <div class="tui-divider">
                    <span>OR</span>
                </div>
                
                <!-- Hash Input -->
                <div class="hash-input-section">
                    <input type="text" name="image_hash" id="image_hash" class="tui-input tui-mono" 
                           placeholder="Enter 64-character image hash from library..." onchange="handleHashInput()">
                    <div class="tui-hint">Use existing image from <a href="/image-library" target="_blank">Image Library</a></div>
                </div>
                
                @error('image')
                    <div class="tui-error">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Hidden PoW Fields (Managed by Unified System) -->
            <input type="hidden" name="pow_nonce" required>
            <input type="hidden" name="pow_hash" required>
            <input type="hidden" name="pow_challenge_id" required>
            
            <!-- Submit -->
            <div class="tui-actions">
                <button type="submit" class="tui-btn tui-btn-primary tui-btn-disabled" disabled>
                    Mine Proof First
                </button>
                <a href="{{ route('forum.board', $board->code) }}" class="tui-btn tui-btn-outline">
                    ← Back to Board
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Unified system handles all mining - just add form validation
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = document.getElementById('preview-img');
    const info = document.getElementById('file-info');
    const hashInput = document.getElementById('image_hash');
    
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

function handleHashInput() {
    const hashInput = document.getElementById('image_hash');
    const fileInput = document.getElementById('image');
    const preview = document.getElementById('image-preview');
    
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

// Form validation
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('unified-thread-form');
    form.addEventListener('submit', (e) => {
        const hasImage = document.getElementById('image').files.length > 0;
        const hasHash = document.getElementById('image_hash').value.trim().length > 0;
        const hasProof = document.querySelector('input[name="pow_hash"]').value;
        
        if (!hasProof) {
            e.preventDefault();
            alert('Complete proof of work first!');
            return;
        }
        
        if (!hasImage && !hasHash) {
            e.preventDefault();
            alert('Either upload an image or provide an image hash!');
            return;
        }
    });
});
</script>

@include('components.mining-dashboard')
@endsection