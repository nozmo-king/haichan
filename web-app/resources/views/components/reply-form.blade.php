<!-- Clean Minimal Reply Form -->
<div class="clean-reply-form">
    <div class="reply-header">
        <span class="reply-title">Reply</span>
        <button type="button" class="close-btn" onclick="this.closest('.clean-reply-form').style.display='none'">×</button>
    </div>
    
    <form action="{{ route('forum.reply', [$board->code, $thread->id]) }}" method="POST" enctype="multipart/form-data" class="reply-form">
        @csrf
        
        <div class="form-field">
            <textarea name="reply_content" id="post-content" class="reply-textarea" rows="4" 
                      required maxlength="3000" 
                      placeholder="Write your reply..."></textarea>
            <div class="char-count">0/3000</div>
        </div>
        
        <div class="form-field compact">
            <input type="file" name="image" id="reply_image" class="file-input" 
                   accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif"
                   onchange="previewReplyImage(this)">
            <label for="reply_image" class="file-label">Attach Image</label>
            
            <div id="reply-image-preview" class="image-preview" style="display: none;">
                <img id="reply-preview-img" alt="Preview">
                <div id="reply-file-info" class="file-info"></div>
            </div>
        </div>
        
        <!-- Hidden PoW fields -->
        <input type="hidden" name="pow_nonce">
        <input type="hidden" name="pow_hash">
        <input type="hidden" name="pow_challenge_id">
        
        <!-- Minimal Mining Status -->
        <div id="reply-mining-status" class="mining-status">
            Start typing to mine...
        </div>
        
        <div class="form-actions">
            <button type="submit" id="reply-submit-btn" class="submit-btn" disabled>
                Mine First
            </button>
            <button type="button" class="cancel-btn" onclick="this.closest('.clean-reply-form').style.display='none'">
                Cancel
            </button>
        </div>
    </form>
</div>

<style nonce="{{ app('csp_nonce') }}">
/* Clean Reply Form - Minimal & Functional */
.clean-reply-form {
    background: var(--neutral-0);
    border: var(--border-width) solid var(--neutral-4);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-2);
    margin: var(--space-4) 0;
    font-family: var(--font-family);
    font-size: var(--font-size-md);
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3) var(--space-4);
    border-bottom: var(--border-width) solid var(--neutral-3);
    background: var(--neutral-2);
}

.reply-title {
    font-weight: var(--font-weight-medium);
    color: var(--neutral-8);
}

.close-btn {
    background: none;
    border: none;
    font-size: var(--font-size-lg);
    color: var(--neutral-6);
    cursor: pointer;
    padding: 0;
    width: var(--space-6);
    height: var(--space-6);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius);
    transition: all var(--transition);
}

.close-btn:hover {
    background: var(--neutral-3);
    color: var(--neutral-8);
}

.reply-form {
    padding: var(--space-4);
}

.form-field {
    margin-bottom: var(--space-4);
}

.form-field.compact {
    margin-bottom: var(--space-3);
}

.reply-textarea {
    width: 100%;
    border: var(--border-width) solid var(--neutral-4);
    border-radius: var(--border-radius);
    padding: var(--space-3);
    font-family: var(--font-family);
    font-size: var(--font-size-md);
    line-height: var(--line-height-normal);
    resize: vertical;
    background: var(--neutral-1);
    color: var(--neutral-8);
    transition: border-color var(--transition);
}

.reply-textarea:focus {
    outline: none;
    border-color: var(--accent-6);
}

.char-count {
    text-align: right;
    font-size: var(--font-size-xs);
    color: var(--neutral-6);
    margin-top: var(--space-1);
}

.file-input {
    display: none;
}

.file-label {
    display: inline-block;
    padding: var(--space-2) var(--space-3);
    border: var(--border-width) solid var(--neutral-4);
    border-radius: var(--border-radius);
    background: var(--neutral-1);
    color: var(--neutral-7);
    cursor: pointer;
    font-size: var(--font-size-sm);
    transition: all var(--transition);
}

.file-label:hover {
    background: var(--neutral-2);
    border-color: var(--neutral-5);
}

.image-preview {
    margin-top: var(--space-3);
    padding: var(--space-3);
    background: var(--neutral-2);
    border-radius: var(--border-radius);
}

.image-preview img {
    max-width: 200px;
    max-height: 150px;
    border-radius: var(--border-radius);
}

.file-info {
    font-size: var(--font-size-xs);
    color: var(--neutral-6);
    margin-top: var(--space-2);
}

.mining-status {
    padding: var(--space-2) var(--space-3);
    background: var(--neutral-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-sm);
    color: var(--neutral-6);
    margin-bottom: var(--space-3);
    text-align: center;
}

.mining-status.active {
    background: var(--accent-1);
    color: var(--accent-7);
}

.mining-status.complete {
    background: var(--accent-2);
    color: var(--accent-8);
}

.form-actions {
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
}

.submit-btn, .cancel-btn {
    padding: var(--space-2) var(--space-4);
    border: var(--border-width) solid var(--neutral-4);
    border-radius: var(--border-radius);
    font-family: var(--font-family);
    font-size: var(--font-size-md);
    cursor: pointer;
    transition: all var(--transition);
}

.submit-btn {
    background: var(--accent-6);
    color: white;
    border-color: var(--accent-6);
}

.submit-btn:hover:not(:disabled) {
    background: var(--accent-7);
    border-color: var(--accent-7);
}

.submit-btn:disabled {
    background: var(--neutral-4);
    color: var(--neutral-6);
    border-color: var(--neutral-4);
    cursor: not-allowed;
}

.cancel-btn {
    background: var(--neutral-1);
    color: var(--neutral-7);
}

.cancel-btn:hover {
    background: var(--neutral-2);
    border-color: var(--neutral-5);
}

/* Dark theme support */
[data-theme="dark"] .clean-reply-form {
    background: var(--neutral-1);
    border-color: var(--neutral-3);
}

[data-theme="dark"] .reply-header {
    background: var(--neutral-2);
    border-bottom-color: var(--neutral-3);
}

[data-theme="dark"] .reply-textarea {
    background: var(--neutral-0);
    color: var(--neutral-9);
}

[data-theme="dark"] .file-label {
    background: var(--neutral-0);
    color: var(--neutral-8);
}

[data-theme="dark"] .mining-status {
    background: var(--neutral-2);
    color: var(--neutral-7);
}
</style>

<script nonce="{{ app('csp_nonce') }}">
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


// Clean Reply Form - Minimal Mining Integration
document.addEventListener('DOMContentLoaded', function() {
    const replyContent = document.getElementById('post-content');
    const replySubmitBtn = document.getElementById('reply-submit-btn');
    const replyMiningStatus = document.getElementById('reply-mining-status');
    const charCount = document.querySelector('.char-count');
    
    let isReplyMining = false;
    let replyMiningTimeout;
    
    // Character counter
    function updateCharCount() {
        if (charCount && replyContent) {
            const count = replyContent.value.length;
            charCount.textContent = `${count}/3000`;
            charCount.style.color = count > 2500 ? 'var(--accent-6)' : 'var(--neutral-6)';
        }
    }
    
    // Mining logic
    function checkAndMineReply() {
        if (isReplyMining) return;
        
        updateCharCount();
        clearTimeout(replyMiningTimeout);
        const content = replyContent?.value?.trim() || '';
        
        if (content.length >= 5) {
            replyMiningTimeout = setTimeout(async () => {
                await mineReply();
            }, 1000);
        } else {
            if (replyMiningStatus) {
                replyMiningStatus.textContent = 'Start typing to mine...';
                replyMiningStatus.className = 'mining-status';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = true;
                replySubmitBtn.textContent = 'Mine First';
            }
        }
    }
    
    async function mineReply() {
        if (isReplyMining || !window.powInstance) return;
        
        const content = replyContent?.value?.trim() || '';
        if (content.length < 5) return;
        
        isReplyMining = true;
        
        try {
            if (replyMiningStatus) {
                replyMiningStatus.textContent = 'Mining proof...';
                replyMiningStatus.className = 'mining-status active';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = true;
                replySubmitBtn.textContent = 'Mining...';
            }
            
            const proof = await window.powInstance.generateProof({
                difficulty: '21e8',
                target_type: 'reply',
                target_id: getThreadIdFromPage(),
                content: content
            });
            
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
                replyMiningStatus.textContent = 'Mining complete!';
                replyMiningStatus.className = 'mining-status complete';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = false;
                replySubmitBtn.textContent = 'Post Reply';
            }
            
            // Update minimal toolbar if it exists
            if (window.MinimalHashrateToolbar) {
                window.MinimalHashrateToolbar.updateHashrate(proof.hashrate || 0);
            }
            
        } catch (error) {
            console.error('Reply mining failed:', error);
            
            if (replyMiningStatus) {
                replyMiningStatus.textContent = 'Mining failed';
                replyMiningStatus.className = 'mining-status';
            }
            if (replySubmitBtn) {
                replySubmitBtn.disabled = true;
                replySubmitBtn.textContent = 'Mining Error';
            }
        }
        
        isReplyMining = false;
    }
    
    function getThreadIdFromPage() {
        const urlMatch = window.location.pathname.match(/\/thread\/(\d+)/);
        if (urlMatch) return parseInt(urlMatch[1]);
        
        const threadElement = document.querySelector('[data-thread-id]');
        if (threadElement) return parseInt(threadElement.dataset.threadId);
        
        return 1;
    }
    
    function getParentIdFromForm() {
        const replyTo = document.querySelector('[data-reply-to]');
        if (replyTo) return parseInt(replyTo.dataset.replyTo);
        return null;
    }
    
    // Event listeners
    if (replyContent) {
        replyContent.addEventListener('input', checkAndMineReply);
        replyContent.addEventListener('paste', () => {
            setTimeout(checkAndMineReply, 100);
        });
        
        // Initial char count
        updateCharCount();
    }
});
</script>

@section('scripts')

@endsection