<!DOCTYPE html>
<html>
<head>
    <title>{{ $board->title }} - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/global-mining.js')
</head>
<body>
    <!-- Mining Status Bar -->
    <div id="mining-status-bar" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #9AB87A;
        color: #444B6E;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        padding: 8px 0;
        z-index: 9999;
        border-bottom: 1px solid #708B75;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 20px;
    ">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <span id="mining-indicator" style="
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    background: #708B75;
                    border-radius: 50%;
                    animation: pulse 1s infinite;
                "></span>
                <span style="color: #444B6E; font-weight: bold;">HAICHAN MINING NETWORK</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">HASH RATE:</span>
                <span id="network-hashrate" style="color: #006400; font-weight: bold;">0 H/s</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">TOTAL HASHES:</span>
                <span id="network-total-hashes" style="color: #006400; font-weight: bold;">0</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">VALID PROOFS:</span>
                <span id="network-valid-proofs" style="color: #708B75; font-weight: bold;">0</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">ACTIVE MINERS:</span>
                <span id="network-active-miners" style="color: #8B0000; font-weight: bold;">1</span>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <div id="current-mining-hash" style="
                font-family: 'Courier New', monospace;
                font-size: 9px;
                color: #666;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            ">21e8000abc123def...</div>
            <div style="color: #444B6E;">
                <span style="color: #666;">DIFFICULTY:</span>
                <span id="current-difficulty" style="color: #8B0000; font-weight: bold;">21e8</span>
            </div>
            <select style="
                background: #708B75;
                color: #FFFFEE;
                border: 1px solid #444B6E;
                padding: 4px 6px;
                border-radius: 3px;
                font-size: 9px;
                margin-left: 10px;
                cursor: pointer;
            " onchange="if(this.value) window.location.href=this.value">
                <option value="">📋 Boards</option>
                <option value="/gen">💬 /gen/</option>
                <option value="/film">🎬 /film/</option>
                <option value="/biz">💼 /biz/</option>
                <option value="/lit">📚 /lit/</option>
                <option value="/x">👽 /x/</option>
                <option value="/meta">⚙️ /meta/</option>
                <option value="/mu">🎵 /mu/</option>
            </select>
            <button id="mini-dash-toggle" style="
                background: #708B75;
                border: none;
                color: white;
                padding: 4px 8px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 12px;
                margin-left: 5px;
            " title="Toggle Mini Dashboard (Ctrl+D)">⛏️</button>
        </div>
    </div>
    
    <div class="container" style="margin-top: 50px;">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">📋 Boards</a>
                <a href="/{{ $board->code }}/catalog">📑 Catalog</a>
                <a href="/mining">⛏️ Mining</a>
            </nav>
        </div>

        <div class="board-header">
            <h2>{{ $board->title }}</h2>
            <p>{{ $board->description }}</p>
        </div>


        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="post-form">
            <h3>[Start a new thread]</h3>
            <form method="POST" action="/{{ $board->code }}" enctype="multipart/form-data" id="thread-form">
                @csrf
                <table>
                    <tr>
                        <td>Subject</td>
                        <td><input type="text" name="title" size="35" maxlength="200" required></td>
                    </tr>
                    <tr>
                        <td>Comment</td>
                        <td><textarea name="content" rows="5" cols="50" required></textarea></td>
                    </tr>
                    <tr>
                        <td>File</td>
                        <td><input type="file" name="image" accept="image/*"></td>
                    </tr>
                    <tr>
                        <td>Proof of Work</td>
                        <td>
                            <div id="thread-mining-status" style="font-family: monospace; font-size: 11px; color: #666;">
                                <span id="thread-mining-indicator">⛏️</span> 
                                <span id="thread-mining-text">Mining required...</span>
                                <div id="thread-mining-progress" style="margin-top: 5px;">
                                    <div style="background: #ddd; height: 8px; border-radius: 4px;">
                                        <div id="thread-mining-bar" style="background: #708B75; height: 100%; width: 0%; border-radius: 4px; transition: width 0.3s;"></div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="pow_nonce" id="thread-pow-nonce" required>
                            <input type="hidden" name="pow_hash" id="thread-pow-hash" required>
                            <input type="hidden" name="pow_challenge_id" id="thread-pow-challenge-id" required>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button type="submit" class="btn-primary" id="thread-submit-btn" disabled>Submit (Mining...)</button></td>
                    </tr>
                </table>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('thread-form');
            const submitBtn = document.getElementById('thread-submit-btn');
            const titleInput = form.querySelector('input[name="title"]');
            const contentInput = form.querySelector('textarea[name="content"]');
            const miningStatus = document.getElementById('thread-mining-status');
            const miningText = document.getElementById('thread-mining-text');
            const miningBar = document.getElementById('thread-mining-bar');
            const miningIndicator = document.getElementById('thread-mining-indicator');
            
            let miningInProgress = false;
            let currentChallenge = null;
            
            // Generate challenge ID
            function generateChallengeId() {
                const array = new Uint8Array(16);
                crypto.getRandomValues(array);
                return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
            }
            
            // Start mining when form inputs are valid
            function startMining() {
                if (miningInProgress) return;
                
                const title = titleInput.value.trim();
                const content = contentInput.value.trim();
                
                if (!title || !content) {
                    miningText.textContent = 'Complete form to start mining...';
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submit (Mining...)';
                    return;
                }
                
                miningInProgress = true;
                currentChallenge = generateChallengeId();
                document.getElementById('thread-pow-challenge-id').value = currentChallenge;
                
                const challengeData = `thread:{{ $board->code }}:${title}:${currentChallenge}`;
                const targetPattern = '21e8';
                
                mineProof(challengeData, targetPattern);
            }
            
            // Mining function
            async function mineProof(data, pattern) {
                let nonce = 0;
                const startTime = Date.now();
                let hashCount = 0;
                
                miningText.textContent = 'Mining proof of work...';
                miningIndicator.textContent = '⛏️';
                
                async function mineStep() {
                    const batchSize = 500;
                    
                    for (let i = 0; i < batchSize && miningInProgress; i++) {
                        const testData = data + ':' + nonce;
                        const encoder = new TextEncoder();
                        
                        try {
                            const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(testData));
                            const hashArray = new Uint8Array(hashBuffer);
                            const hashHex = Array.from(hashArray, b => b.toString(16).padStart(2, '0')).join('');
                            
                            hashCount++;
                            
                            if (hashHex.startsWith(pattern.toLowerCase())) {
                                // Found valid proof!
                                document.getElementById('thread-pow-nonce').value = nonce;
                                document.getElementById('thread-pow-hash').value = hashHex;
                                
                                miningText.textContent = `✅ Proof found! Hash: ${hashHex.substring(0, 16)}...`;
                                miningIndicator.textContent = '✅';
                                miningBar.style.width = '100%';
                                miningBar.style.background = '#4CAF50';
                                
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Submit';
                                miningInProgress = false;
                                return;
                            }
                        } catch (error) {
                            console.error('Mining error:', error);
                        }
                        
                        nonce++;
                    }
                    
                    // Update progress
                    const elapsed = (Date.now() - startTime) / 1000;
                    const hashrate = Math.floor(hashCount / elapsed);
                    miningText.textContent = `Mining... ${hashrate} H/s (${hashCount} hashes)`;
                    
                    const progress = Math.min((hashCount / 10000) * 100, 95);
                    miningBar.style.width = progress + '%';
                    
                    if (miningInProgress) {
                        setTimeout(mineStep, 1);
                    }
                }
                
                await mineStep();
            }
            
            // Start mining when inputs change
            titleInput.addEventListener('input', () => setTimeout(startMining, 500));
            contentInput.addEventListener('input', () => setTimeout(startMining, 500));
            
            // Prevent form submission without valid PoW
            form.addEventListener('submit', function(e) {
                if (!document.getElementById('thread-pow-hash').value) {
                    e.preventDefault();
                    alert('Mining is required before submitting!');
                }
            });
        });
        </script>

        <div class="threads-list">
            @forelse($threads as $thread)
            <div class="thread-preview" data-thread-id="{{ $thread->id }}" data-thread-title="{{ $thread->title }}">
                <div class="thread-header">
                    <span class="subject">
                        <a href="/{{ $board->code }}/{{ $thread->id }}">
                            {{ $thread->title ?: 'No Subject' }}
                        </a>
                    </span>
                    <span class="poster-info">
                        Anonymous {{ $thread->created_at->format('m/d/y H:i') }} No.{{ $thread->id }}
                    </span>
                </div>

                <div class="thread-content">
                    @if($thread->image_path)
                    <div class="thread-image">
                        <img src="/storage/{{ $thread->image_path }}" class="thumbnail">
                    </div>
                    @endif
                    
                    <div class="thread-text">
                        <p>{!! nl2br(e(Str::limit($thread->content, 300))) !!}</p>
                    </div>
                </div>

                <div style="font-size: 8pt; color: #888; margin-top: 5px;">
                    Replies: {{ $thread->reply_count }} | 
                    Images: {{ $thread->image_count }} | 
                    Last: {{ $thread->bumped_at ? $thread->bumped_at->diffForHumans() : $thread->created_at->diffForHumans() }}
                </div>
            </div>
            @empty
            <div class="thread-preview">
                <p style="text-align: center; padding: 40px;">No threads yet. Start the first one!</p>
            </div>
            @endforelse
        </div>

        <div style="text-align: center; padding: 20px;">
            {{ $threads->links() }}
        </div>
    </div>

    <script>
        // Auto-refresh threads every 10 seconds for live updates
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                // Fetch updated thread list
                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Parse response and update thread list
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newThreadsList = doc.querySelector('.threads-list');
                    const currentThreadsList = document.querySelector('.threads-list');
                    
                    if (newThreadsList && currentThreadsList) {
                        // Smoothly fade out, update, fade in
                        currentThreadsList.style.opacity = '0.5';
                        setTimeout(() => {
                            currentThreadsList.innerHTML = newThreadsList.innerHTML;
                            currentThreadsList.style.opacity = '1';
                        }, 200);
                    }
                })
                .catch(err => console.log('Refresh failed:', err));
            }, 10000); // 10 seconds
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', () => {
            startAutoRefresh();
        });
        
        // Stop when user navigates away
        window.addEventListener('beforeunload', stopAutoRefresh);
    </script>
</body>
</html>
