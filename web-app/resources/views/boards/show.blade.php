@extends('layout')

@section('title', $board->title)

@section('content')
<div style="text-align: center; margin: 10px 0;">
    <h1>{{ $board->title }}</h1>
    <p style="font-size: 11px; color: var(--ib-muted);">{{ $board->description }}</p>
</div>

<div class="nav-links">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a> |
    <a href="#bottom">Bottom</a>
</div>

<!-- New Thread Creation Interface -->
<div id="thread-creation-panel" class="haichan-form-panel">
    <div class="form-header">
        <h3>🧵 Create New Thread</h3>
        <button type="button" class="toggle-btn" id="thread-form-toggle" onclick="toggleThreadForm()">−</button>
    </div>
    
    <div id="thread-form-container" class="form-container">
        <form method="POST" action="/{{ $board->code }}" enctype="multipart/form-data" id="new-thread-form" class="haichan-unified-form">
            @csrf
            
            <!-- Subject Field -->
            <div class="field-group">
                <label class="field-label required" for="thread-title">📝 Subject</label>
                <input type="text" name="title" id="thread-title" class="field-input" 
                       maxlength="200" required placeholder="Thread subject...">
                <div class="field-hint">Required • 3-200 characters</div>
            </div>
            
            <!-- Content Field -->
            <div class="field-group">
                <label class="field-label required" for="thread-content">💬 Comment</label>
                <textarea name="content" id="thread-content" class="field-textarea" 
                          required rows="5" placeholder="What's on your mind..."></textarea>
                <div class="field-hint">Required • 5-5000 characters</div>
            </div>
            
            <!-- Image Upload -->
            <div class="field-group">
                <label class="field-label required" for="thread-image">🖼️ Image</label>
                <input type="file" name="image" id="thread-image" class="field-file" 
                       accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif"
                       onchange="previewThreadImage(this)">
                <div class="field-hint">Required • Max 25MB • Images, Videos, GIFs</div>
                
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
            <div class="form-actions">
                <button type="submit" class="submit-btn disabled" id="thread-submit-btn" disabled>
                    <span class="btn-icon">⛏️</span>
                    <span class="btn-text">Mining Required...</span>
                </button>
                <button type="button" class="reset-btn" onclick="resetThreadForm()">🔄 Reset</button>
            </div>
        </form>
    </div>
</div>

<hr>

<!-- Threads -->
@forelse($threads as $thread)
<div class="post" data-thread-id="{{ $thread->id }}">
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
    </div>
    
    @if($thread->image_path)
    <div style="float: left; margin: 5px 10px 5px 0;">
        <a href="{{ route('thread.image', $thread->id) }}" target="_blank">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}"
                 style="max-width: 125px; max-height: 125px; border: 1px solid var(--ib-border);">
        </a>
    </div>
    @endif
    
    <div class="post-content">
        <strong>{{ $thread->title ?: 'No Subject' }}</strong><br>
        {{ $thread->content }}
    </div>
    
    <div style="clear: both; font-size: 10px; color: var(--ib-muted); margin-top: 8px;">
        💬 {{ $thread->reply_count }} replies | 🖼️ {{ $thread->image_count }} images | 
        {{ $thread->bumped_at ? $thread->bumped_at->diffForHumans() : $thread->created_at->diffForHumans() }}
    </div>
</div>
@empty
<div style="text-align: center; padding: 40px;">
    <p style="color: var(--ib-muted);">No threads yet. Be the first to start the conversation!</p>
</div>
@endforelse

<div class="nav-links">
    <a name="bottom"></a>
    <a href="#">Top</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a>
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
        this.submitBtn.classList.remove('disabled');
        this.submitBtn.querySelector('.btn-icon').textContent = '🚀';
        this.submitBtn.querySelector('.btn-text').textContent = 'Post Thread';
    }
    
    disableSubmit() {
        this.submitBtn.disabled = true;
        this.submitBtn.classList.add('disabled');
        this.submitBtn.querySelector('.btn-icon').textContent = '⛏️';
        this.submitBtn.querySelector('.btn-text').textContent = 'Mining Required...';
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
    
    if (container.style.display === 'none') {
        container.style.display = 'block';
        toggle.textContent = '−';
    } else {
        container.style.display = 'none';
        toggle.textContent = '+';
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
.status-dot.mining { background: #007bff; animation: pulse 1s infinite; }
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
    background: linear-gradient(90deg, #007bff, #0056b3);
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

@endsection