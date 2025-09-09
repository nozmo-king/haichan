@extends('layout')

@section('title', ($thread->title ?: 'Thread') . ' - ' . $board->title)

@section('content')
    <style>
        .reply-popout {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reply-popout-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
        }
        .reply-popout-content {
            position: relative;
            background: #F5F5DC;
            border: 2px solid #708B75;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .reply-popout-header {
            background: linear-gradient(135deg, #708B75, #5A7B5F);
            color: #F5F5DC;
            padding: 15px 20px;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .reply-popout-header h3 {
            margin: 0;
            font-size: 14pt;
        }
        .close-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            color: #F5F5DC;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .reply-popout form {
            padding: 20px;
        }
        .reply-form-field {
            margin-bottom: 15px;
        }
        .reply-form-field label {
            display: block;
            color: var(--text-primary);
            font-weight: bold;
            margin-bottom: 5px;
        }
        .reply-form-field textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: monospace;
            resize: vertical;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        .reply-form-field input[type="file"] {
            width: 100%;
            padding: 5px;
        }
        .reply-form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-primary, .btn-secondary {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background: var(--accent-color);
            color: var(--bg-primary);
        }
        .btn-primary:hover {
            background: var(--border-color);
        }
        .btn-secondary {
            background: #ccc;
            color: #333;
        }
        .btn-secondary:hover {
            background: #bbb;
        }
    </style>
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">📋 Boards</a>
                <a href="/{{ $board->name }}">{{ $board->name }}/</a>
                <a href="/{{ $board->name }}/catalog">📑 Catalog</a>
                <a href="/mining">⛏️ Mining</a>
            </nav>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif



        <!-- Original post -->
        <div class="post op-post" id="post{{ $thread->id }}" 
             data-mine-type="thread" 
             data-mine-target="thread-{{ $thread->id }}"
             data-mine-weight="60"
             data-thread-id="{{ $thread->id }}"
             data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}">
            <div class="post-header">
                <span class="subject">{{ $thread->title ?: 'No Subject' }}</span>
                <span class="poster-info">
                    Anonymous {{ $thread->created_at->format('m/d/y H:i:s') }} No.{{ $thread->id }}
                    <a href="javascript:void(0)" class="reply-link" onclick="showReplyForm({{ $thread->id }}, '{{ addslashes($thread->content) }}')">[Reply]</a>
                    <a href="javascript:void(0)" class="quote-link" onclick="quotePost({{ $thread->id }}, '{{ addslashes($thread->content) }}')">[Quote]</a>
                </span>
            </div>
            
            @if($thread->image_path)
            <div style="float: left; margin: 5px 15px 10px 0;">
                <div style="font-size: 8pt; margin-bottom: 3px;">
                    File: {{ $thread->image_filename ?: 'image' }}
                </div>
                <img src="{{ route('thread.image', $thread->id) }}" style="max-width: 200px; max-height: 200px;">
            </div>
            @endif
            
            <div class="post-content">
                {!! nl2br(e($thread->content)) !!}
            </div>
            <div class="post-hash-preview" id="hash-{{ $thread->id }}" style="font-family: monospace; font-size: 8pt; color: #888; margin-top: 5px; opacity: 0.6;">
                <span class="hash-label">sha256:</span>
                <span class="hash-value">calculating...</span>
                <span class="hash-bump-indicator" style="display: none; color: #ff6b35; font-weight: bold; margin-left: 10px;">🔥 21e8 BUMP!</span>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Replies -->
        @foreach($posts as $post)
        <div class="post reply-post" id="post{{ $post->id }}" 
             data-mine-type="reply" 
             data-mine-target="reply-{{ $post->id }}"
             data-mine-weight="40"
             data-thread-id="{{ $thread->id }}"
             data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}"
             data-post-id="{{ $post->id }}">
            <div class="post-header">
                <span class="poster-info">
                    Anonymous {{ $post->created_at->format('m/d/y H:i:s') }} No.{{ $post->id }}
                    <a href="javascript:void(0)" class="reply-link" onclick="showReplyForm({{ $post->id }}, '{{ addslashes($post->content) }}')">[Reply]</a>
                    <a href="javascript:void(0)" class="quote-link" onclick="quotePost({{ $post->id }}, '{{ addslashes($post->content) }}')">[Quote]</a>
                </span>
            </div>
            
            @if($post->image_filename)
            <div style="float: left; margin: 5px 15px 10px 0;">
                <div style="font-size: 8pt; margin-bottom: 3px;">
                    File: {{ $post->image_original_name }}
                </div>
                <img src="{{ route('post.image', $post->id) }}" style="max-width: 200px; max-height: 200px;">
            </div>
            @endif
            
            <div class="post-content">
                {!! preg_replace('/&gt;&gt;(\d+)/', '<a href="#post$1" class="quote-link">&gt;&gt;$1</a>', 
                     preg_replace('/^&gt;(.+)/m', '<span class="greentext">&gt;$1</span>', 
                     nl2br(e($post->content)))) !!}
            </div>
            <div class="post-hash-preview" id="hash-{{ $post->id }}" style="font-family: monospace; font-size: 8pt; color: #888; margin-top: 5px; opacity: 0.6;">
                <span class="hash-label">sha256:</span>
                <span class="hash-value">calculating...</span>
                <span class="hash-bump-indicator" style="display: none; color: #ff6b35; font-weight: bold; margin-left: 10px;">🔥 21e8 BUMP!</span>
            </div>
            <div style="clear: both;"></div>
        </div>
        @endforeach

        <!-- Reply form -->
        @if(!$thread->locked)
        <div class="reply-form">
            <h3>[Post a Reply]</h3>
            <form method="POST" action="/{{ $board->code }}/{{ $thread->id }}/reply" enctype="multipart/form-data" id="reply-form">
                @csrf
                <!-- Hidden PoW fields -->
                <input type="hidden" name="pow_nonce" id="reply-pow-nonce" value="">
                <input type="hidden" name="pow_hash" id="reply-pow-hash" value="">
                <input type="hidden" name="pow_challenge_id" id="reply-pow-challenge-id" value="">
                
                <table>
                    <tr>
                        <td>Comment</td>
                        <td><textarea name="content" id="reply-content" rows="5" cols="50" required></textarea></td>
                    </tr>
                    <tr>
                        <td>File</td>
                        <td><input type="file" name="image" accept="image/*"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="button" id="mine-reply-btn" class="btn-primary">Mine & Submit Reply</button>
                            <div id="reply-mining-status" style="margin-top: 10px; font-size: 10px; color: #666; display: none;">
                                <div>Mining proof of work...</div>
                                <div>Pattern: <span style="color: #8B0000; font-weight: bold;">21e8</span></div>
                                <div>Hashes: <span id="reply-hash-count">0</span></div>
                                <div>Rate: <span id="reply-hash-rate">0</span> H/s</div>
                                <div style="margin-top: 5px;">
                                    <button type="button" id="stop-reply-mining" class="btn-stop">Stop Mining</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        @endif
    </div>


    <script>
        
        // Post hash calculation and 21e8 detection
        class PostHashSystem {
            constructor() {
                this.bumpMultiplier = 10; // 10x bump for 21e8 hashes
            }
            
            async sha256(text) {
                const encoder = new TextEncoder();
                const data = encoder.encode(text);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }
            
            async calculatePostHash(postId, content) {
                try {
                    const hash = await this.sha256(content);
                    const hashElement = document.querySelector(`#hash-${postId} .hash-value`);
                    const bumpIndicator = document.querySelector(`#hash-${postId} .hash-bump-indicator`);
                    
                    if (hashElement) {
                        // Show first 16 characters of hash
                        hashElement.textContent = hash.substring(0, 16) + '...';
                        hashElement.title = hash; // Full hash on hover
                        
                        // Check for 21e8 bump
                        if (hash.startsWith('21e8')) {
                            this.trigger21e8Bump(postId, hash, bumpIndicator);
                        }
                    }
                } catch (error) {
                    console.error('Hash calculation failed:', error);
                    const hashElement = document.querySelector(`#hash-${postId} .hash-value`);
                    if (hashElement) {
                        hashElement.textContent = 'error';
                    }
                }
            }
            
            trigger21e8Bump(postId, hash, bumpIndicator) {
                console.log(`🔥 21e8 BUMP DETECTED! Post #${postId}: ${hash}`);
                
                // Show bump indicator
                if (bumpIndicator) {
                    bumpIndicator.style.display = 'inline';
                    bumpIndicator.style.animation = 'bumpPulse 1s ease-in-out 3';
                }
                
                // Apply visual effects to post
                const post = document.querySelector(`#post${postId}`);
                if (post) {
                    post.style.border = '2px solid #ff6b35';
                    post.style.boxShadow = '0 0 20px rgba(255, 107, 53, 0.5)';
                    post.style.animation = 'bumpGlow 2s ease-in-out';
                }
                
                // Submit bump to server
                this.submitBumpToServer(postId, hash);
                
                // Show celebration
                this.celebrateBump(postId, hash);
            }
            
            async submitBumpToServer(postId, hash) {
                try {
                    const response = await fetch('/api/post-bump', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            post_id: postId,
                            hash: hash,
                            multiplier: this.bumpMultiplier,
                            thread_id: {{ $thread->id }}
                        })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        console.log(`✅ 21e8 bump applied! Post #${postId} +${result.bump_points} points`);
                    }
                } catch (error) {
                    console.error('Bump submission failed:', error);
                }
            }
            
            celebrateBump(postId, hash) {
                // Create floating text
                const post = document.querySelector(`#post${postId}`);
                if (post) {
                    const celebration = document.createElement('div');
                    celebration.style.cssText = `
                        position: absolute;
                        top: -30px;
                        left: 50%;
                        transform: translateX(-50%);
                        color: #ff6b35;
                        font-weight: bold;
                        font-size: 14pt;
                        text-shadow: 0 0 10px #ff6b35;
                        pointer-events: none;
                        z-index: 1000;
                        animation: floatUp 3s ease-out forwards;
                    `;
                    celebration.textContent = '🔥 +' + this.bumpMultiplier + 'x BUMP!';
                    
                    post.style.position = 'relative';
                    post.appendChild(celebration);
                    
                    setTimeout(() => celebration.remove(), 3000);
                }
            }
            
            async processAllPosts() {
                // Process thread (OP)
                const threadContent = `{!! addslashes($thread->content) !!}`;
                await this.calculatePostHash({{ $thread->id }}, threadContent);
                
                // Process all replies
                @foreach($posts as $post)
                const post{{ $post->id }}Content = `{!! addslashes($post->content) !!}`;
                await this.calculatePostHash({{ $post->id }}, post{{ $post->id }}Content);
                @endforeach
            }
        }

        // CSS animations for bump effects
        const bumpStyles = document.createElement('style');
        bumpStyles.textContent = `
            @keyframes bumpPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            @keyframes bumpGlow {
                0%, 100% { box-shadow: 0 0 20px rgba(255, 107, 53, 0.5); }
                50% { box-shadow: 0 0 40px rgba(255, 107, 53, 0.8); }
            }
            @keyframes floatUp {
                0% { opacity: 1; transform: translateX(-50%) translateY(0); }
                100% { opacity: 0; transform: translateX(-50%) translateY(-50px); }
            }
            
            /* Theme integration for hash previews */
            .theme-business .post-hash-preview {
                color: #666;
                background: rgba(192, 192, 192, 0.1);
                border-radius: 2px;
                padding: 2px 4px;
            }
            .theme-pleasure .post-hash-preview {
                color: #00ff41;
                text-shadow: 0 0 3px currentColor;
                background: rgba(0, 255, 65, 0.05);
                border: 1px solid rgba(0, 255, 65, 0.2);
                border-radius: 2px;
                padding: 2px 4px;
            }
        `;
        document.head.appendChild(bumpStyles);

        // Initialize hash system
        const postHashSystem = new PostHashSystem();

        // Set default mining target for this thread page
        document.addEventListener('DOMContentLoaded', function() {
            // Calculate all post hashes
            postHashSystem.processAllPosts();
            
            // Initialize mining to target this thread by default
            function initThreadMining() {
                if (window.haichanMiner) {
                    window.haichanMiner.switchTarget('thread', {{ $thread->id }}, '{{ addslashes($thread->title ?: "Thread #" . $thread->id) }}');
                } else {
                    // Retry after global mining script loads
                    setTimeout(initThreadMining, 100);
                }
            }
            initThreadMining();
            
            // Auto-refresh PoW score every 5 seconds
            setInterval(() => {
                fetch(window.location.href + '?pow_only=1', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.pow_score !== undefined) {
                        const powDisplay = document.querySelector('.pow-display-section h3');
                        if (powDisplay) {
                            powDisplay.innerHTML = `⚡ Thread Proof of Work: ${data.pow_score} points`;
                        }
                    }
                })
                .catch(err => console.log('PoW refresh failed:', err));
            }, 5000);
        });

        // Popout reply form
        function showReplyForm(postId, content = '') {
            // Remove existing popout if any
            const existingPopout = document.querySelector('.reply-popout');
            if (existingPopout) {
                existingPopout.remove();
            }

            const popout = document.createElement('div');
            popout.className = 'reply-popout';
            popout.innerHTML = `
                <div class="reply-popout-backdrop" onclick="closeReplyForm()"></div>
                <div class="reply-popout-content">
                    <div class="reply-popout-header">
                        <h3>Reply to Post #${postId}</h3>
                        <button onclick="closeReplyForm()" class="close-btn">✕</button>
                    </div>
                    <form method="POST" action="/{{ $board->name }}/{{ $thread->id }}/reply" enctype="multipart/form-data">
                        @csrf
                        <div class="reply-form-field">
                            <label>Comment</label>
                            <textarea name="content" rows="5" placeholder="Enter your reply..." required></textarea>
                        </div>
                        <div class="reply-form-field">
                            <label>File</label>
                            <input type="file" name="image" accept="image/*">
                        </div>
                        <div class="reply-form-actions">
                            <button type="submit" class="btn-primary">Submit Reply</button>
                            <button type="button" onclick="closeReplyForm()" class="btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(popout);
            popout.querySelector('textarea').focus();
        }

        function closeReplyForm() {
            const popout = document.querySelector('.reply-popout');
            if (popout) {
                popout.remove();
            }
        }

        // Quote functionality
        function quotePost(postId, content = '') {
            showReplyForm(postId, content);
            setTimeout(() => {
                const textarea = document.querySelector('.reply-popout textarea[name="content"]');
                if (textarea) {
                    // Create a clean quote from the content
                    const quotedContent = content.split('\n').map(line => '>' + line).join('\n');
                    textarea.value = `>>${postId}\n${quotedContent}\n\n`;
                    textarea.focus();
                    // Position cursor after the quote
                    textarea.selectionStart = textarea.selectionEnd = textarea.value.length;
                }
            }, 100);
        }

        document.addEventListener('click', (e) => {
            if (e.target.closest('.poster-info') && !e.target.closest('.reply-link') && !e.target.closest('.quote-link')) {
                const post = e.target.closest('.post');
                const postId = post.id.replace('post', '');
                showReplyForm(postId);
            }
        });

        // Close popout on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeReplyForm();
            }
        });

        // Theme switching
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const theme = e.target.dataset.theme;
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('haichan-theme', theme);
                
                // Update theme selector active state
                document.querySelectorAll('.theme-option').forEach(opt => {
                    opt.style.transform = 'scale(1)';
                    opt.style.boxShadow = 'none';
                });
                e.target.style.transform = 'scale(1.2)';
                e.target.style.boxShadow = '0 0 8px rgba(255,255,255,0.5)';
            });
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('haichan-theme') || 'haichan';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        // Set active theme selector
        const activeTheme = document.querySelector(`[data-theme="${savedTheme}"]`);
        if (activeTheme) {
            activeTheme.style.transform = 'scale(1.2)';
            activeTheme.style.boxShadow = '0 0 8px rgba(255,255,255,0.5)';
        }

        // Reply Mining System
        let replyMiningInProgress = false;
        let replyMiningWorker = null;

        function generateChallengeId() {
            const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < 32; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        async function mineReplyProof(threadId, content, pattern) {
            const challengeId = generateChallengeId();
            const challengeData = `post:${threadId}:${content}:${challengeId}`;
            let nonce = 0;
            let startTime = Date.now();
            let hashCount = 0;

            document.getElementById('reply-pow-challenge-id').value = challengeId;

            const hashCountEl = document.getElementById('reply-hash-count');
            const hashRateEl = document.getElementById('reply-hash-rate');
            const statusEl = document.getElementById('reply-mining-status');
            statusEl.style.display = 'block';

            async function mineStep() {
                if (!replyMiningInProgress) return;

                const batchSize = 500;
                for (let i = 0; i < batchSize && replyMiningInProgress; i++) {
                    const testData = challengeData + ':' + nonce;
                    const encoder = new TextEncoder();
                    const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(testData));
                    const hashHex = Array.from(new Uint8Array(hashBuffer), b => b.toString(16).padStart(2, '0')).join('');
                    
                    hashCount++;
                    const elapsed = (Date.now() - startTime) / 1000;
                    const rate = Math.round(hashCount / elapsed);
                    
                    hashCountEl.textContent = hashCount.toLocaleString();
                    hashRateEl.textContent = rate.toLocaleString();
                    
                    if (hashHex.startsWith(pattern.toLowerCase())) {
                        document.getElementById('reply-pow-nonce').value = nonce;
                        document.getElementById('reply-pow-hash').value = hashHex;
                        replyMiningInProgress = false;
                        statusEl.style.display = 'none';
                        
                        // Auto-submit the form
                        document.getElementById('reply-form').submit();
                        return;
                    }
                    nonce++;
                }
                
                if (replyMiningInProgress) {
                    setTimeout(mineStep, 1);
                }
            }
            
            await mineStep();
        }

        // Reply mining button event
        document.getElementById('mine-reply-btn').addEventListener('click', async () => {
            const content = document.getElementById('reply-content').value.trim();
            if (!content) {
                alert('Please enter a reply first!');
                return;
            }
            
            replyMiningInProgress = true;
            document.getElementById('mine-reply-btn').disabled = true;
            document.getElementById('mine-reply-btn').textContent = 'Mining...';
            
            await mineReplyProof({{ $thread->id }}, content, '21e8');
        });

        // Stop reply mining button
        document.getElementById('stop-reply-mining').addEventListener('click', () => {
            replyMiningInProgress = false;
            document.getElementById('reply-mining-status').style.display = 'none';
            document.getElementById('mine-reply-btn').disabled = false;
            document.getElementById('mine-reply-btn').textContent = 'Mine & Submit Reply';
        });
    </script>
@endsection
