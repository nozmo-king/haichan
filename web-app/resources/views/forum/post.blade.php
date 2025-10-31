<div class="tui-post {{ $level > 0 ? 'tui-post-nested' : '' }}">
    <div class="tui-post-header">
        <div class="tui-post-meta">
            <span class="tui-author">{{ $post->getAuthorDisplayName() }}</span>
            <span class="tui-timestamp">{{ $post->created_at->format('m/d/y(D)H:i') }}</span>
            <span class="tui-post-id">No.{{ $post->id }}</span>
            @if($post->parent_id)
                <span class="tui-reply-to">>>{{ $post->parent_id }}</span>
            @endif
        </div>
        <div class="tui-post-actions">
            <button class="tui-btn-link" onclick="toggleReplyForm({{ $post->id }}, 'post')">[Reply]</button>
        </div>
    </div>
    
    <div class="tui-post-content">
        @if($post->image_path)
            <div class="tui-post-image">
                <img src="{{ route('post.image', $post->id) }}" 
                     alt="{{ $post->image_filename }}" 
                     onclick="toggleImageSize(this)"
                     class="tui-image">
                <div class="tui-image-filename">{{ $post->image_filename }}</div>
            </div>
        @endif
        <div class="tui-post-text">{{ $post->content }}</div>
    </div>
</div>

<div id="reply-form-post-{{ $post->id }}" class="tui-reply-form" style="display: none;">
    <div class="tui-reply-container">
        <div class="tui-reply-header">
            <span class="tui-reply-title">Reply to No.{{ $post->id }}</span>
            <button type="button" class="tui-btn-close" onclick="toggleReplyForm({{ $post->id }}, 'post')">×</button>
        </div>
        
        <form action="{{ strtolower('/' . $board->code . '/' . $thread->id . '/reply') }}" 
              method="POST" enctype="multipart/form-data" 
              id="reply-form-{{ $post->id }}"
              class="tui-form">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $post->id }}">
            
            <!-- Content Field -->
            <div class="tui-field">
                <label class="tui-label" for="content-{{ $post->id }}">Your Reply</label>
                <textarea name="content" id="content-{{ $post->id }}" 
                          class="tui-textarea" rows="4" 
                          placeholder="Write your reply..." 
                          required maxlength="5000"></textarea>
            </div>
            
            <!-- Image Upload -->
            <div class="tui-field">
                <label class="tui-label" for="image-post-{{ $post->id }}">Image (optional)</label>
                <input type="file" name="image" id="image-post-{{ $post->id }}" 
                       class="tui-file" accept="image/*,video/*" 
                       onchange="previewReplyImage(this, {{ $post->id }})">
                <div class="tui-hint">Max 25MB. Supports: JPEG, PNG, GIF, WebP, WebM, MP4, etc.</div>
                
                <!-- Preview -->
                <div id="reply-preview-{{ $post->id }}" class="tui-preview" style="display: none;">
                    <img id="reply-preview-img-{{ $post->id }}" alt="Preview">
                    <div id="reply-file-info-{{ $post->id }}" class="tui-preview-info"></div>
                </div>
            </div>
            
            <!-- Mining Section -->
            <div class="tui-mining tui-mining-compact">
                <div class="tui-mining-header">
                    <span class="tui-mining-title">⛏️ PoW Required</span>
                    <span id="reply-mining-status-{{ $post->id }}" class="tui-mining-status tui-status-waiting">Ready</span>
                </div>
                
                <div class="tui-mining-stats tui-stats-compact">
                    <div class="tui-stat">
                        <span>Rate:</span>
                        <span id="reply-hash-rate-{{ $post->id }}" class="tui-mono">0 H/s</span>
                    </div>
                    <div class="tui-stat">
                        <span>Hash:</span>
                        <span id="reply-current-hash-{{ $post->id }}" class="tui-mono tui-hash">None</span>
                    </div>
                </div>
                
                <div class="tui-progress tui-progress-small">
                    <div id="reply-progress-bar-{{ $post->id }}" class="tui-progress-bar"></div>
                </div>
                
                <div class="tui-mining-controls">
                    <button type="button" id="reply-start-mining-{{ $post->id }}" class="tui-btn tui-btn-success tui-btn-sm">
                        🚀 Mine
                    </button>
                    <button type="button" id="reply-stop-mining-{{ $post->id }}" class="tui-btn tui-btn-danger tui-btn-sm" disabled>
                        ⛔ Stop
                    </button>
                </div>
            </div>
            
            <!-- Hidden PoW Fields -->
            <input type="hidden" name="pow_nonce" id="pow_nonce_post_{{ $post->id }}" required>
            <input type="hidden" name="pow_hash" id="pow_hash_post_{{ $post->id }}" required>
            <input type="hidden" name="pow_challenge_id" id="pow_challenge_id_post_{{ $post->id }}" required>
            
            <!-- Actions -->
            <div class="tui-actions">
                <button type="submit" id="reply-submit-{{ $post->id }}" 
                        class="tui-btn tui-btn-primary tui-btn-disabled" disabled>
                    Complete PoW First
                </button>
                <button type="button" class="tui-btn tui-btn-outline" 
                        onclick="toggleReplyForm({{ $post->id }}, 'post')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ app('csp_nonce') }}">
// Reply miner for post {{ $post->id }}
window.replyMiner{{ $post->id }} = {
    postId: {{ $post->id }},
    isMining: false,
    nonce: 0,
    challengeId: null,
    hashCount: 0,
    startTime: 0,
    pattern: '21e8',
    statsInterval: null,
    
    init() {
        this.challengeId = this.generateChallenge();
        document.getElementById('pow_challenge_id_post_{{ $post->id }}').value = this.challengeId;
        
        // Bind events
        document.getElementById('reply-start-mining-{{ $post->id }}').onclick = () => this.startMining();
        document.getElementById('reply-stop-mining-{{ $post->id }}').onclick = () => this.stopMining();
        document.getElementById('content-{{ $post->id }}').oninput = () => this.checkAutoStart();
        
        document.getElementById('reply-form-{{ $post->id }}').onsubmit = (e) => {
            if (!document.getElementById('pow_hash_post_{{ $post->id }}').value) {
                e.preventDefault();
                alert('Complete proof of work first!');
            }
        };
    },
    
    generateChallenge() {
        return Array.from(crypto.getRandomValues(new Uint8Array(16)), 
            b => b.toString(16).padStart(2, '0')).join('');
    },
    
    checkAutoStart() {
        const content = document.getElementById('content-{{ $post->id }}').value.trim();
        if (content && !this.isMining && !document.getElementById('pow_hash_post_{{ $post->id }}').value) {
            this.startMining();
        }
    },
    
    async startMining() {
        const content = document.getElementById('content-{{ $post->id }}').value.trim();
        if (!content) {
            alert('Enter reply content first!');
            return;
        }
        
        this.isMining = true;
        this.startTime = Date.now();
        this.hashCount = 0;
        this.nonce = Math.floor(Math.random() * 1000000);
        
        this.updateUI('mining');
        this.statsInterval = setInterval(() => this.updateStats(), 1000);
        this.mine();
    },
    
    async mine() {
        const boardCode = '{{ $board->code }}';
        const threadId = {{ $thread->id }};
        const challengeData = `reply:${boardCode}:${threadId}:{{ $post->id }}:${this.challengeId}`;
        
        while (this.isMining) {
            const testData = `${challengeData}:${this.nonce}`;
            const hash = await this.sha256(testData);
            
            this.hashCount++;
            document.getElementById('reply-current-hash-{{ $post->id }}').textContent = hash;
            
            const progress = (this.hashCount % 1000) / 10;
            document.getElementById('reply-progress-bar-{{ $post->id }}').style.width = progress + '%';
            
            if (hash.startsWith(this.pattern)) {
                this.foundProof(hash);
                break;
            }
            
            this.nonce++;
            
            if (this.hashCount % 1000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
    },
    
    async sha256(message) {
        const buffer = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(message));
        return Array.from(new Uint8Array(buffer), b => b.toString(16).padStart(2, '0')).join('');
    },
    
    foundProof(hash) {
        this.isMining = false;
        clearInterval(this.statsInterval);
        
        document.getElementById('pow_nonce_post_{{ $post->id }}').value = this.nonce;
        document.getElementById('pow_hash_post_{{ $post->id }}').value = hash;
        
        this.updateUI('success');
    },
    
    stopMining() {
        this.isMining = false;
        clearInterval(this.statsInterval);
        this.updateUI('stopped');
    },
    
    updateUI(state) {
        const status = document.getElementById('reply-mining-status-{{ $post->id }}');
        const startBtn = document.getElementById('reply-start-mining-{{ $post->id }}');
        const stopBtn = document.getElementById('reply-stop-mining-{{ $post->id }}');
        const submitBtn = document.getElementById('reply-submit-{{ $post->id }}');
        const progressBar = document.getElementById('reply-progress-bar-{{ $post->id }}');
        
        if (state === 'mining') {
            status.textContent = 'Mining';
            status.className = 'tui-mining-status tui-status-active';
            startBtn.disabled = true;
            stopBtn.disabled = false;
        } else if (state === 'success') {
            status.textContent = 'Ready';
            status.className = 'tui-mining-status tui-status-success';
            startBtn.disabled = true;
            stopBtn.disabled = true;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Post Reply';
            submitBtn.className = 'tui-btn tui-btn-primary';
            progressBar.style.width = '100%';
            progressBar.className = 'tui-progress-bar tui-progress-success';
        } else if (state === 'stopped') {
            status.textContent = 'Stopped';
            status.className = 'tui-mining-status tui-status-error';
            startBtn.disabled = false;
            stopBtn.disabled = true;
        }
    },
    
    updateStats() {
        if (!this.startTime) return;
        const elapsed = (Date.now() - this.startTime) / 1000;
        const rate = Math.floor(this.hashCount / elapsed);
        document.getElementById('reply-hash-rate-{{ $post->id }}').textContent = `${rate.toLocaleString()}`;
    }
};

// Initialize when this post is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (window.replyMiner{{ $post->id }}) {
        window.replyMiner{{ $post->id }}.init();
    }
});
</script>

@if($post->allReplies)
    @foreach($post->allReplies as $reply)
        @include('forum.post', ['post' => $reply, 'level' => $level + 1])
    @endforeach
@endif
