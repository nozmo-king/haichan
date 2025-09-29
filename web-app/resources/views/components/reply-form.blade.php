<!-- Unified Reply Form -->
<div class="tui-reply-form">
    <div class="tui-reply-header">
        <div class="tui-reply-title">💬 Reply to Thread</div>
        <button type="button" class="tui-btn-close" onclick="this.closest('.tui-reply-form').style.display='none'">×</button>
    </div>
    
    <div class="tui-reply-container">
        <form action="{{ route('forum.reply', [$board->code, $thread->id]) }}" method="POST" enctype="multipart/form-data" class="unified-reply-form">
            @csrf
            
            <div class="tui-field">
                <label class="tui-label" for="reply_content">Reply Content</label>
                <textarea name="reply_content" id="reply_content" class="tui-textarea" rows="6" 
                          required maxlength="3000" 
                          placeholder="Enter your reply... Use >>hash to quote posts"></textarea>
                <div class="tui-hint">Max 3000 characters. Use >>hash to quote posts.</div>
            </div>
            
            <div class="tui-field">
                <label class="tui-label" for="reply_image">Image Upload (optional)</label>
                <input type="file" name="reply_image" id="reply_image" class="tui-file" 
                       accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif"
                       onchange="previewReplyImage(this)">
                <div class="tui-hint">Optional. Max 25MB. Supports: JPEG, PNG, GIF, WebP, WebM, MP4, SVG, etc.</div>
                
                <!-- Preview -->
                <div id="reply-image-preview" class="tui-preview" style="display: none;">
                    <img id="reply-preview-img" alt="Reply Preview">
                    <div id="reply-file-info" class="tui-preview-info"></div>
                </div>
            </div>
            
            <!-- Image Hash Alternative -->
            <div class="tui-alternative">
                <label class="tui-label" for="reply_image_hash">OR use existing image hash:</label>
                <input type="text" name="reply_image_hash" id="reply_image_hash" class="tui-input tui-mono" 
                       placeholder="Paste image hash from library..." onchange="handleReplyHashInput()">
                <div class="tui-hint">Copy hash from Image Library instead of uploading.</div>
            </div>
            
            <!-- Hidden PoW fields (managed by unified system) -->
            <input type="hidden" name="pow_nonce">
            <input type="hidden" name="pow_hash">
            <input type="hidden" name="pow_challenge_id">
            
            <div class="tui-actions">
                <button type="submit" class="tui-btn tui-btn-primary tui-btn-disabled" disabled>
                    Mine Proof First
                </button>
                <button type="button" class="tui-btn tui-btn-outline" onclick="this.closest('.tui-reply-form').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewReplyImage(input) {
    const preview = document.getElementById('reply-image-preview');
    const img = document.getElementById('reply-preview-img');
    const info = document.getElementById('reply-file-info');
    const hashInput = document.getElementById('reply_image_hash');
    
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

function handleReplyHashInput() {
    const hashInput = document.getElementById('reply_image_hash');
    const fileInput = document.getElementById('reply_image');
    const preview = document.getElementById('reply-image-preview');
    
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

// Unified system will automatically handle PoW mining for this form
</script>
</script>