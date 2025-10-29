<!-- Unified Reply Form -->
<div class="tui-reply-form">
    <div class="tui-reply-header">
        <div class="tui-reply-title">💬 Reply to Thread</div>
        <button type="button" class="tui-btn-close" onclick="this.closest('.tui-reply-form').style.display='none'">×</button>
    </div>
    
    <div class="tui-reply-container">
        <form action="{{ route('forum.reply', [$board->code, $thread->id]) }}" method="POST" enctype="multipart/form-data" class="unified-post-form unified-reply-form">
            @csrf
            
            <div class="tui-field">
                <label class="tui-label" for="reply_content">Reply Content</label>
                <textarea name="reply_content" id="post-content" class="tui-textarea" rows="6" 
                          required maxlength="3000" 
                          placeholder="Enter your reply... Use >>hash to quote posts"></textarea>
                <div class="tui-hint">Max 3000 characters. Use >>hash to quote posts.</div>
            </div>
            
            <div class="tui-field">
                <label class="tui-label" for="reply_image">Image Upload (optional)</label>
                <input type="file" name="image" id="reply_image" class="tui-file" 
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
            <x-image-picker 
                name="image_hash" 
                label="OR Choose from Image Library"
                placeholder="Browse library or enter image hash manually..."
                pattern="[a-fA-F0-9]{64}"
                style="font-family: 'Courier New', monospace;"
            />
            
            <!-- Hidden PoW fields (managed by unified system) -->
            <input type="hidden" name="pow_nonce">
            <input type="hidden" name="pow_hash">
            <input type="hidden" name="pow_challenge_id">
            
            <!-- Mining Status Display -->
            <div id="reply-mining-status" class="tui-mining-status" style="margin-bottom: var(--space-md); min-height: 1.5rem;">
                <span style="color: var(--text-muted);">🦀 Start typing to begin WASM mining...</span>
            </div>
            
            <div class="tui-actions">
                <button type="submit" id="reply-submit-btn" class="tui-btn tui-btn-primary tui-btn-disabled" disabled>
                    🦀 Mine Proof First
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


// WASM PoW mining for reply form
document.addEventListener('DOMContentLoaded', function() {
    const replyContent = document.getElementById('post-content');
    const replySubmitBtn = document.getElementById('reply-submit-btn');
    const replyMiningStatus = document.getElementById('reply-mining-status');
    
    let isReplyMining = false;
    let replyMiningTimeout;
    
    // Auto-mine when content is filled
    function checkAndMineReply() {
        if (isReplyMining) return;
        
        clearTimeout(replyMiningTimeout);
        const content = replyContent?.value?.trim() || '';
        
        if (content.length >= 5) {
            replyMiningTimeout = setTimeout(async () => {
                await mineReply();
            }, 1000);
        } else {
            if (replyMiningStatus) {
                replyMiningStatus.innerHTML = '<span style="color: var(--text-muted);">🦀 Start typing to begin WASM mining...</span>';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = true;
                replySubmitBtn.textContent = '🦀 Mine Proof First';
            }
        }
    }
    
    async function mineReply() {
        if (isReplyMining || !window.wasmPowMiner) return;
        
        const content = replyContent?.value?.trim() || '';
        if (content.length < 5) return;
        
        isReplyMining = true;
        
        try {
            if (replyMiningStatus) {
                replyMiningStatus.innerHTML = '<span style="color: var(--color-amber-500);">🦀 WASM mining reply...</span>';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = true;
                replySubmitBtn.textContent = '⛏️ Mining...';
            }
            
            const formData = {
                title: '',
                body: content,
                attachments: [],
                refs: []
            };
            
            // Get thread and parent IDs from form or URL
            const threadId = getThreadIdFromPage();
            const parentId = getParentIdFromForm();
            
            const proof = await window.wasmPowMiner.mineForForm('reply', formData, {
                difficulty: '21e8',
                threadId: threadId,
                parentId: parentId,
                useWasm: true
            });
            
            console.log('✅ Reply WASM mining complete:', proof);
            
            // Fill hidden fields
            const form = replyContent.closest('form');
            if (form) {
                const nonceInput = form.querySelector('input[name="pow_nonce"]');
                const hashInput = form.querySelector('input[name="pow_hash"]');
                const challengeInput = form.querySelector('input[name="pow_challenge_id"]');
                
                if (nonceInput) nonceInput.value = proof.nonce;
                if (hashInput) hashInput.value = proof.hash;
                if (challengeInput) challengeInput.value = proof.challenge_id;
            }
            
            if (replyMiningStatus) {
                replyMiningStatus.innerHTML = '<span style="color: var(--color-green-600);">✅ WASM mining complete!</span>';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = false;
                replySubmitBtn.textContent = '🦀 Post Reply';
            }
            
        } catch (error) {
            console.error('Reply WASM mining failed:', error);
            
            if (replyMiningStatus) {
                replyMiningStatus.innerHTML = '<span style="color: var(--color-red-500);">❌ Mining failed</span>';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = true;
                replySubmitBtn.textContent = '❌ Mining Error';
            }
        }
        
        isReplyMining = false;
    }
    
    function getThreadIdFromPage() {
        // Try to extract thread ID from URL or page data
        const urlMatch = window.location.pathname.match(/\/thread\/(\d+)/);
        if (urlMatch) return parseInt(urlMatch[1]);
        
        // Fallback to finding it in the DOM
        const threadElement = document.querySelector('[data-thread-id]');
        if (threadElement) return parseInt(threadElement.dataset.threadId);
        
        return 1; // Fallback
    }
    
    function getParentIdFromForm() {
        // Try to get parent ID from form data or page context
        const replyTo = document.querySelector('[data-reply-to]');
        if (replyTo) return parseInt(replyTo.dataset.replyTo);
        
        return null; // Top-level reply
    }
    
    // Bind to content input
    if (replyContent) {
        replyContent.addEventListener('input', checkAndMineReply);
        replyContent.addEventListener('paste', () => {
            setTimeout(checkAndMineReply, 100);
        });
    }
});
</script>

@section('scripts')
<script src="/js/simple-pow.js?v={{ time() }}"></script>
@endsection