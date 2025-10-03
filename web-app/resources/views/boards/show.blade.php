@extends('layout')

@section('title', '/'.$board->code.'/ - '.$board->name)

@section('content')
<div style="background: var(--primary-bg); padding: 30px; border-radius: 12px; border: 2px solid var(--border-color); margin-bottom: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); text-align: center;">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: var(--text-primary); margin: 0 0 10px 0;">
        📋 /{{ $board->code }}/ - {{ $board->name }}
    </h1>
    <p style="color: var(--text-secondary); font-size: 14px; margin: 0 0 20px 0;">
        {{ $board->description }}
    </p>
    
    <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <a href="/" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 10px 15px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: bold; transition: all 0.3s ease;">
            🏠 Board List
        </a>
        <a href="/{{ $board->code }}/catalog" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 10px 15px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: bold; transition: all 0.3s ease;">
            📑 Catalog
        </a>
        <a href="#bottom" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 10px 15px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: bold; transition: all 0.3s ease;">
            ⬇️ Bottom
        </a>
    </div>
</div>

<!-- New Thread Creation Interface -->
<div style="background: var(--primary-bg); border: 2px solid var(--border-color); border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
    <div style="background: linear-gradient(135deg, var(--border-color), var(--accent-color)); color: var(--primary-bg); padding: 15px 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🧵 Create New Thread
        </h3>
        <button type="button" style="background: rgba(245, 245, 220, 0.2); border: 1px solid rgba(245, 245, 220, 0.3); color: var(--primary-bg); width: 28px; height: 28px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold;" id="thread-form-toggle" onclick="toggleThreadForm()">−</button>
    </div>
    
    <div id="thread-form-container" style="padding: 25px;">
        <form method="POST" action="/{{ $board->code }}" enctype="multipart/form-data" id="new-thread-form" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            
            <!-- Subject Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px; font-size: 13px;">
                    📝 Subject <span style="color: #dc3545;">*</span>
                </label>
                <input type="text" name="title" id="thread-title" required maxlength="200" placeholder="Thread subject..."
                       style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                <div style="color: var(--text-secondary); font-size: 12px; font-style: italic;">Required • 3-200 characters</div>
            </div>
            
            <!-- Content Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px; font-size: 13px;">
                    💬 Comment <span style="color: #dc3545;">*</span>
                </label>
                <textarea name="content" id="thread-content" required rows="5" placeholder="What's on your mind..."
                          style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
                <div style="color: var(--text-secondary); font-size: 12px; font-style: italic;">Required • 5-5000 characters</div>
            </div>
            
            <!-- Image Upload -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px; font-size: 13px;">
                    🖼️ Image <span style="color: #dc3545;">*</span>
                </label>
                <input type="file" name="image" id="thread-image" onchange="previewThreadImage(this)"
                       accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif"
                       style="width: 100%; padding: 10px; border: 2px dashed var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 13px; box-sizing: border-box;">
                <div style="color: var(--text-secondary); font-size: 12px; font-style: italic;">Required • Max 25MB • Images, Videos, GIFs</div>
                
                <!-- Image Preview -->
                <div id="thread-image-preview" class="image-preview" style="display: none;">
                    <img id="thread-preview-img" class="preview-image" alt="Thread Image Preview">
                    <div id="thread-file-info" class="file-info"></div>
                    <button type="button" class="remove-preview" onclick="removeThreadPreview()">×</button>
                </div>
            </div>
            
            <!-- Library Hash Alternative -->
            <div class="field-group alternative">
                <label class="field-label" for="thread-image-hash">🔗 OR Use Library Image</label>
                <input type="text" name="image_hash" id="thread-image-hash" class="field-input mono" 
                       placeholder="Paste 64-character image hash from library..."
                       onchange="handleThreadHashInput()">
                <div class="field-hint">Alternative to file upload • Paste hash from Image Library</div>
            </div>
            
            <!-- Author Options -->
            <div class="field-group">
                <label class="field-label" for="thread-name">👤 Name (Optional)</label>
                <input type="text" name="name" id="thread-name" class="field-input" 
                       placeholder="Anonymous">
                <div class="field-hint">Leave blank for anonymous posting</div>
            </div>
            
            <!-- Mining Status -->
            <div class="mining-status-panel">
                <div class="mining-indicator">
                    <span class="status-dot" id="thread-status-dot"></span>
                    <span class="status-text" id="thread-mining-status">⏳ Complete form to start mining...</span>
                </div>
                <div class="mining-progress" id="thread-mining-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="thread-progress-fill"></div>
                    </div>
                    <div class="mining-stats" id="thread-mining-stats"></div>
                </div>
            </div>
            
            <!-- Hidden PoW Fields -->
            <input type="hidden" name="pow_nonce" id="thread-pow-nonce" required>
            <input type="hidden" name="pow_hash" id="thread-pow-hash" required>
            <input type="hidden" name="pow_challenge_id" id="thread-pow-challenge-id" required>
            
            <!-- Submit Actions -->
            <div style="display: flex; gap: 15px; justify-content: center; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <button type="submit" id="thread-submit-btn" disabled
                        style="background: linear-gradient(135deg, var(--border-color), var(--accent-color)); color: var(--primary-bg); border: none; padding: 12px 25px; border-radius: 8px; font-size: 14px; font-weight: bold; cursor: not-allowed; opacity: 0.6; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                    <span>⛏️</span>
                    <span>Mining Required...</span>
                </button>
                <button type="button" onclick="resetThreadForm()" 
                        style="background: var(--content-bg); color: var(--text-primary); border: 1px solid var(--border-color); padding: 12px 20px; border-radius: 8px; font-size: 14px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                    <span>🔄</span>
                    <span>Reset</span>
                </button>
            </div>
        </form>
    </div>
</div>

<hr>

<!-- Threads -->
@forelse($threads as $thread)
<div class="post" data-thread-id="{{ $thread->id }}" data-mine-type="thread" data-board-code="{{ $board->code }}">
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
        <a href="/{{ $board->code }}/{{ $thread->id }}" style="margin-left: 10px;">[Reply]</a>
        @if(session('bitcoin_auth_id') && ($thread->user_id === session('bitcoin_auth_id') || (session('bitcoin_auth_user') && (session('bitcoin_auth_user')->is_admin || session('bitcoin_auth_user')->is_moderator))))
            <form method="POST" action="{{ route('threads.delete.user', $thread->id) }}" style="display: inline; margin-left: 10px;" onsubmit="return confirm('Delete this thread?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: none; border: none; color: #c86b4a; cursor: pointer; font-size: 12px;">[Delete]</button>
            </form>
        @endif
    </div>
    
    @if($thread->image_path)
    <div style="float: left; margin: 5px 10px 5px 0;">
        <a href="{{ route('thread.image', $thread->id) }}" target="_blank">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}" data-mine-type="image"
                 style="max-width: 125px; max-height: 125px; border: 1px solid var(--ib-border);">
        </a>
    </div>
    @endif
    
    <div class="post-content">
        <strong>{{ $thread->title ?: 'No Subject' }}</strong><br>
        {{ $thread->content }}
    </div>
    
    <div style="clear: both; font-size: 10px; color: var(--ib-text-muted); margin-top: 8px;">
        💬 {{ $thread->reply_count }} replies | 🖼️ {{ $thread->image_count }} images | 
        {{ $thread->bumped_at ? $thread->bumped_at->diffForHumans() : $thread->created_at->diffForHumans() }}
    </div>
</div>
@empty
<div style="text-align: center; padding: 40px;">
    <p style="color: var(--ib-text-muted);">No threads yet. Be the first to start the conversation!</p>
</div>
@endforelse

<div style="background: var(--primary-bg); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); margin-top: 30px; text-align: center;">
    <a name="bottom"></a>
    <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <a href="#" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: bold; transition: all 0.3s ease;">
            ⬆️ Top
        </a>
        <a href="/" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: bold; transition: all 0.3s ease;">
            🏠 Board List
        </a>
        <a href="/{{ $board->code }}/catalog" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-weight: bold; transition: all 0.3s ease;">
            📑 Catalog
        </a>
    </div>
</div>

<script>
// New Thread Creation System
class HaichanThreadCreator {
    constructor() {
        this.form = document.getElementById('new-thread-form');
        this.isMining = false;
        this.currentChallenge = null;
        this.miningStartTime = 0;
        this.hashCount = 0;
        
        this.init();
    }
    
    init() {
        if (!this.form) return;
        
        // Get form elements
        this.titleInput = document.getElementById('thread-title');
        this.contentInput = document.getElementById('thread-content');
        this.imageInput = document.getElementById('thread-image');
        this.hashInput = document.getElementById('thread-image-hash');
        this.submitBtn = document.getElementById('thread-submit-btn');
        this.statusDot = document.getElementById('thread-status-dot');
        this.statusText = document.getElementById('thread-mining-status');
        this.progressPanel = document.getElementById('thread-mining-progress');
        this.progressFill = document.getElementById('thread-progress-fill');
        this.statsDisplay = document.getElementById('thread-mining-stats');
        
        // Setup event listeners
        this.setupEventListeners();
        
        console.log('🧵 Thread Creator initialized');
    }
    
    setupEventListeners() {
        // Form field validation and mining trigger
        this.titleInput.addEventListener('input', () => this.debounce(() => this.validateAndMine(), 500));
        this.contentInput.addEventListener('input', () => this.debounce(() => this.validateAndMine(), 500));
        this.imageInput.addEventListener('change', () => this.validateAndMine());
        this.hashInput.addEventListener('input', () => this.validateAndMine());
        
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    debounce(func, wait) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, wait);
    }
    
    validateForm() {
        const title = this.titleInput.value.trim();
        const content = this.contentInput.value.trim();
        const hasImage = this.imageInput.files[0];
        const hasHash = this.hashInput.value.trim();
        
        const isValid = title.length >= 3 && 
                       content.length >= 5 && 
                       (hasImage || hasHash);
        
        return { isValid, title, content, hasImage, hasHash };
    }
    
    async validateAndMine() {
        const validation = this.validateForm();
        
        if (!validation.isValid) {
            this.updateStatus('incomplete', '⏳ Complete all required fields...');
            this.disableSubmit();
            return;
        }
        
        if (this.isMining) {
            this.stopMining();
        }
        
        await this.startMining(validation.title, validation.content);
    }
    
    async startMining(title, content) {
        this.isMining = true;
        this.currentChallenge = this.generateChallengeId();
        this.miningStartTime = Date.now();
        this.hashCount = 0;
        
        // Set challenge ID
        document.getElementById('thread-pow-challenge-id').value = this.currentChallenge;
        
        // Update UI
        this.updateStatus('mining', '⛏️ Mining proof of work...');
        this.showMiningProgress();
        
        const challengeData = `thread:{{ $board->code }}:${title}:${this.currentChallenge}`;
        const targetPattern = '21e8';
        
        console.log(`🎯 Mining thread: ${challengeData}`);
        
        await this.mineProof(challengeData, targetPattern);
    }
    
    async mineProof(data, pattern) {
        let nonce = 0;
        const batchSize = 1000;
        
        while (this.isMining && nonce < 1000000) {
            for (let i = 0; i < batchSize && this.isMining; i++) {
                const testData = `${data}:${nonce}`;
                const hash = await this.calculateHash(testData);
                this.hashCount++;
                
                if (hash.startsWith(pattern.toLowerCase())) {
                    // Found proof!
                    document.getElementById('thread-pow-nonce').value = nonce;
                    document.getElementById('thread-pow-hash').value = hash;
                    
                    this.updateStatus('success', `✅ Proof found! ${hash.substring(0, 16)}...`);
                    this.hideMiningProgress();
                    this.enableSubmit();
                    this.isMining = false;
                    
                    console.log(`💎 Thread proof found: ${hash}`);
                    return;
                }
                
                nonce++;
            }
            
            // Update progress
            this.updateMiningProgress();
            
            // Yield control
            await new Promise(resolve => setTimeout(resolve, 1));
        }
        
        // Mining failed or stopped
        if (this.isMining) {
            this.updateStatus('error', '❌ Mining timeout - try simpler content');
            this.hideMiningProgress();
            this.isMining = false;
        }
    }
    
    async calculateHash(data) {
        const encoder = new TextEncoder();
        const dataBuffer = encoder.encode(data);
        const hashBuffer = await crypto.subtle.digest('SHA-256', dataBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    generateChallengeId() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }
    
    updateStatus(type, message) {
        this.statusDot.className = `status-dot ${type}`;
        this.statusText.textContent = message;
    }
    
    showMiningProgress() {
        this.progressPanel.style.display = 'block';
    }
    
    hideMiningProgress() {
        this.progressPanel.style.display = 'none';
    }
    
    updateMiningProgress() {
        const elapsed = (Date.now() - this.miningStartTime) / 1000;
        const hashrate = Math.floor(this.hashCount / elapsed);
        
        this.statsDisplay.textContent = `${hashrate.toLocaleString()} H/s • ${this.hashCount.toLocaleString()} hashes`;
        
        // Animate progress bar
        const progress = Math.min(90, (this.hashCount / 10000) * 100);
        this.progressFill.style.width = `${progress}%`;
    }
    
    enableSubmit() {
        this.submitBtn.disabled = false;
        this.submitBtn.style.cursor = 'pointer';
        this.submitBtn.style.opacity = '1';
        this.submitBtn.querySelector('span:first-child').textContent = '🚀';
        this.submitBtn.querySelector('span:last-child').textContent = 'Post Thread';
    }
    
    disableSubmit() {
        this.submitBtn.disabled = true;
        this.submitBtn.style.cursor = 'not-allowed';
        this.submitBtn.style.opacity = '0.6';
        this.submitBtn.querySelector('span:first-child').textContent = '⛏️';
        this.submitBtn.querySelector('span:last-child').textContent = 'Mining Required...';
    }
    
    stopMining() {
        this.isMining = false;
        this.hideMiningProgress();
    }
    
    handleSubmit(e) {
        const powHash = document.getElementById('thread-pow-hash').value;
        const powNonce = document.getElementById('thread-pow-nonce').value;
        
        if (!powHash || !powNonce) {
            e.preventDefault();
            alert('Proof of work mining is required!');
            return false;
        }
        
        console.log('🚀 Thread submission with PoW:', powHash.substring(0, 16) + '...');
        return true;
    }
}

// Thread Form Management Functions
function toggleThreadForm() {
    const container = document.getElementById('thread-form-container');
    const toggle = document.getElementById('thread-form-toggle');
    if (!container || !toggle) return;

    if (container.style.display === 'none' || container.dataset.state === 'collapsed') {
        container.style.display = 'block';
        container.dataset.state = 'expanded';
        toggle.textContent = '−';
        localStorage.setItem('haichan-thread-form-collapsed', 'false');
    } else {
        container.style.display = 'none';
        container.dataset.state = 'collapsed';
        toggle.textContent = '+';
        localStorage.setItem('haichan-thread-form-collapsed', 'true');
    }
}

function previewThreadImage(input) {
    const preview = document.getElementById('thread-image-preview');
    const img = document.getElementById('thread-preview-img');
    const info = document.getElementById('thread-file-info');
    const hashInput = document.getElementById('thread-image-hash');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            info.textContent = `${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(file);
        hashInput.value = ''; // Clear hash input
    } else {
        preview.style.display = 'none';
    }
}

function removeThreadPreview() {
    document.getElementById('thread-image').value = '';
    document.getElementById('thread-image-preview').style.display = 'none';
}

function handleThreadHashInput() {
    const hashInput = document.getElementById('thread-image-hash');
    const fileInput = document.getElementById('thread-image');
    const preview = document.getElementById('thread-image-preview');
    
    if (hashInput.value.trim()) {
        fileInput.value = ''; // Clear file input
        preview.style.display = 'none';
        
        // Validate hash format
        if (hashInput.value.length === 64 && /^[a-f0-9]{64}$/i.test(hashInput.value)) {
            hashInput.style.borderColor = 'var(--success-color, #28a745)';
        } else {
            hashInput.style.borderColor = 'var(--error-color, #dc3545)';
        }
    } else {
        hashInput.style.borderColor = '';
    }
}

function resetThreadForm() {
    if (window.threadCreator) {
        window.threadCreator.stopMining();
    }
    
    document.getElementById('new-thread-form').reset();
    document.getElementById('thread-image-preview').style.display = 'none';
    document.getElementById('thread-image-hash').style.borderColor = '';
    
    // Clear PoW fields
    document.getElementById('thread-pow-nonce').value = '';
    document.getElementById('thread-pow-hash').value = '';
    document.getElementById('thread-pow-challenge-id').value = '';
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.threadCreator = new HaichanThreadCreator();

    const container = document.getElementById('thread-form-container');
    const toggle = document.getElementById('thread-form-toggle');
    const collapsed = localStorage.getItem('haichan-thread-form-collapsed') === 'true';

    if (container && toggle) {
        if (collapsed) {
            container.style.display = 'none';
            container.dataset.state = 'collapsed';
            toggle.textContent = '+';
        } else {
            container.style.display = 'block';
            container.dataset.state = 'expanded';
            toggle.textContent = '−';
        }
    }

    console.log('🎯 Thread creation system loaded');
});
</script>

<!-- Thread Creation Styles -->
<style>
.haichan-form-panel {
    background: var(--ib-bg-alt, #f5f5dc);
    border: 2px solid var(--ib-border, #d4af37);
    border-radius: 8px;
    margin: 20px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-header {
    background: var(--ib-header-bg, #e6d8b5);
    border-bottom: 1px solid var(--ib-border, #d4af37);
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 6px 6px 0 0;
}

.form-header h3 {
    margin: 0;
    font-family: 'Nova Cut', serif;
    font-size: 16px;
    color: var(--ib-text, #444b6e);
}

.toggle-btn {
    background: none;
    border: 1px solid var(--ib-border, #d4af37);
    border-radius: 4px;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.2s ease;
}

.toggle-btn:hover {
    background: var(--ib-accent, #d4af37);
    color: white;
}

.form-container {
    padding: 20px;
}

.haichan-unified-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.field-group.alternative {
    border-top: 1px dashed var(--ib-border, #d4af37);
    padding-top: 16px;
    margin-top: 8px;
}

.field-label {
    font-weight: bold;
    font-size: 12px;
    color: var(--ib-text, #444b6e);
    font-family: 'Courier New', monospace;
}

.field-label.required::after {
    content: ' *';
    color: #dc3545;
}

.field-input, .field-textarea {
    padding: 8px 10px;
    border: 1px solid var(--ib-border, #d4af37);
    border-radius: 4px;
    font-family: inherit;
    font-size: 13px;
    background: white;
    transition: border-color 0.2s ease;
}

.field-input:focus, .field-textarea:focus {
    outline: none;
    border-color: var(--ib-accent, #d4af37);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
}

.field-input.mono {
    font-family: 'Courier New', monospace;
    font-size: 11px;
}

.field-file {
    padding: 4px;
    border: 1px dashed var(--ib-border, #d4af37);
    border-radius: 4px;
    font-size: 12px;
}

.field-hint {
    font-size: 10px;
    color: var(--ib-text-muted, #666);
    font-style: italic;
}

.image-preview {
    position: relative;
    margin-top: 8px;
    border: 1px solid var(--ib-border, #d4af37);
    border-radius: 4px;
    padding: 8px;
    background: white;
}

.preview-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 4px;
    display: block;
}

.file-info {
    font-size: 10px;
    color: var(--ib-text-muted, #666);
    margin-top: 4px;
    font-family: 'Courier New', monospace;
}

.remove-preview {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(220, 53, 69, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
}

.mining-status-panel {
    background: var(--ib-bg, #fffacd);
    border: 1px solid var(--ib-border, #d4af37);
    border-radius: 4px;
    padding: 12px;
}

.mining-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-family: 'Courier New', monospace;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ccc;
    display: inline-block;
    transition: background-color 0.2s ease;
}

.status-dot.incomplete { background: #ffc107; }
.status-dot.mining { background: #708B75; animation: pulse 1s infinite; }
.status-dot.success { background: #28a745; }
.status-dot.error { background: #dc3545; }

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.mining-progress {
    margin-top: 8px;
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #708B75, #9AB87A);
    width: 0%;
    transition: width 0.3s ease;
}

.mining-stats {
    font-size: 10px;
    color: var(--ib-text-muted, #666);
    margin-top: 4px;
    text-align: center;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    padding-top: 16px;
    border-top: 1px dashed var(--ib-border, #d4af37);
}

.submit-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    background: var(--ib-accent, #d4af37);
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'Courier New', monospace;
}

.submit-btn:hover:not(.disabled) {
    background: var(--ib-accent-dark, #b8941f);
    transform: translateY(-1px);
}

.submit-btn.disabled {
    background: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}

.reset-btn {
    padding: 10px 16px;
    background: transparent;
    color: var(--ib-text, #444b6e);
    border: 1px solid var(--ib-border, #d4af37);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'Courier New', monospace;
}

.reset-btn:hover {
    background: var(--ib-bg-alt, #f5f5dc);
}

.btn-icon {
    font-size: 14px;
}

.btn-text {
    font-size: 12px;
}
</style>

@include('components.mining-dashboard')
@endsection
