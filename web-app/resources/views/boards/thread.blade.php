<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($thread->title ?: 'Thread') . ' - /' . $board->code . '/' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    @vite('resources/js/simple-mining.js')
    <style>
        @keyframes strobe {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .strobing-emoji {
            animation: strobe 2s infinite;
        }

        /* Mining Animations */
        @keyframes mine-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes mine-glow {
            0%, 100% { text-shadow: 0 0 5px #ff6b35; }
            50% { text-shadow: 0 0 20px #ff6b35, 0 0 30px #ff6b35; }
        }
        @keyframes mine-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-2px); }
            75% { transform: translateX(2px); }
        }
        @keyframes hash-pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }
        @keyframes progress-fill {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .mining-active {
            animation: mine-spin 1s linear infinite;
        }
        .mining-glow {
            animation: mine-glow 1.5s ease-in-out infinite;
        }
        .mining-shake {
            animation: mine-shake 0.5s ease-in-out infinite;
        }
        .hash-calculating {
            animation: hash-pulse 0.8s ease-in-out infinite;
        }
        .progress-animated {
            background: linear-gradient(90deg, #708B75, #9AB87A, #708B75);
            background-size: 200% 100%;
            animation: progress-fill 2s linear infinite;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #FFFACD 0%, #F0F8FF 100%);
            font-family: 'Georgia', serif;
            min-height: 100vh;
        }
    </style>
</head>
<body>
<!-- Japanese Web Aesthetic Container with Homepage Style -->
<div style="margin: 60px auto 40px auto; max-width: 900px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header with proper color scheme -->
    <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 25px 40px; border-bottom: 2px solid #708B75; position: relative; text-align: center;">
        <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 4px 12px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
            β版
        </div>

        <!-- Home Button -->
        <div style="position: absolute; top: 20px; left: 20px;">
            <a href="/" style="background: #708B75; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-size: 16px; display: inline-block;">
                🏠
            </a>
        </div>

        <h1 style="font-size: 24px; color: #3D315B; margin: 0 0 12px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            <span class="strobing-emoji" style="font-size: 22px; color: #B87333;">💬</span>
            {{ $thread->title ?: 'Thread #' . $thread->id }}
            <span class="strobing-emoji" style="font-size: 22px; color: #CD5C5C;">⚡</span>
        </h1>

        <!-- Reply Sort Controls -->
        <div style="margin: 10px 0; display: flex; justify-content: center; align-items: center; gap: 10px;">
            <label style="font-size: 12px; color: #666;">Sort replies by:</label>
            <select id="reply-sort" onchange="changeSort()" style="padding: 4px 8px; background: #F5F5DC; border: 1px solid #708B75; border-radius: 3px; color: #3D315B; font-size: 11px;">
                <option value="chronological" {{ request('sort') === 'chronological' || !request('sort') ? 'selected' : '' }}>📅 Chronological</option>
                <option value="pow" {{ request('sort') === 'pow' ? 'selected' : '' }}>⛏️ By PoW</option>
            </select>
        </div>

        <div style="width: 80px; height: 2px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 15px auto;"></div>

        <p style="color: #708B75; font-size: 12px; line-height: 1.5; margin: 15px 0 0 0; font-weight: 400;">/{{ $board->code }}/ discussion thread</p>

        <!-- Navigation breadcrumb with proper spacing -->
        <div style="margin-top: 20px; font-size: 11px; color: #444B6E;">
            <a href="{{ route('boards.index') }}" style="color: #708B75; text-decoration: none; margin-right: 10px;">[Boards]</a>
            <a href="/{{ $board->code }}" style="color: #708B75; text-decoration: none; margin-right: 10px;">[/{{ $board->code }}/]</a>
            <a href="/{{ $board->code }}/catalog" style="color: #708B75; text-decoration: none; margin-right: 10px;">[Catalog]</a>
            <span style="color: #9AB87A;">[Thread #{{ $thread->id }}]</span>
        </div>
    </div>

    <!-- Content area with proper spacing -->
    <div style="padding: 40px; background: #FFFFEE;">

        @if($errors->any())
        <div style="background: #FFE6E6; border: 1px solid #FF9999; padding: 15px; margin-bottom: 30px; border-radius: 5px;">
            @foreach($errors->all() as $error)
                <p style="color: #CC0000; margin: 5px 0; font-size: 12px;">• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <!-- Original Post -->
        <div id="post{{ $thread->id }}" style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px; margin-bottom: 30px; position: relative;"
             data-mine-type="thread" data-mine-target="thread-{{ $thread->id }}" data-mine-weight="60"
             data-thread-id="{{ $thread->id }}" data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}">

            <!-- PoW Mining Badge - Always show for retroactive mining -->
            <div id="thread-mining-badge" style="position: absolute; top: -10px; right: 20px; background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; padding: 6px 16px; font-size: 12px; border-radius: 15px; font-weight: bold; box-shadow: 0 3px 6px rgba(0,0,0,0.2); border: 3px solid #FFFFEE; z-index: 10;">
                ⛏️ <span id="thread-pow-number">{{ number_format($thread->accumulated_points ?: 0, 2) }}</span>
            </div>

            <!-- Post Header -->
            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #9AB87A;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                    <h2 style="color: #3D315B; font-size: 18px; margin: 0; font-weight: 600;">
                        {{ $thread->title ?: 'No Subject' }}
                    </h2>
                    <div style="color: #708B75; font-size: 11px; text-align: right;">
                        <div>
                            @if($board->code === 'pol' && $thread->country_flag)
                                <span style="font-size: 18px; margin-right: 5px; vertical-align: middle;">{{ $thread->country_flag }}</span>
                            @endif
                            Anonymous • {{ $thread->created_at->format('M d, Y H:i') }}
                            @if($thread->user_id)
                                @include('components.admin-badge', ['user' => $thread->bitcoinUser])
                            @endif
                        </div>
                        <div>
                            <a href="#post{{ $thread->id }}" style="color: #9AB87A; text-decoration: none;">No.{{ $thread->id }}</a>
                            @if(session('bitcoin_auth_id') && ($thread->user_id === session('bitcoin_auth_id') || (session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin)))
                                <form method="POST" action="{{ route('threads.delete.user', $thread->id) }}" style="display: inline; margin-left: 15px;" onsubmit="return confirm('Delete this entire thread and all replies?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #F44336; cursor: pointer; font-size: 11px;">[Delete Thread]</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                @if($thread->pow_hash)
                <!-- Proof of Work Hash Display -->
                <div style="margin-top: 12px; padding: 8px; background: rgba(154, 184, 122, 0.1); border: 1px solid #9AB87A; border-radius: 4px;">
                    <div style="font-size: 10px; color: #708B75; font-weight: bold; margin-bottom: 4px;">
                        ⛏️ PROOF OF WORK • SHA256 HASH
                    </div>
                    <div style="font-family: 'Courier New', monospace; font-size: 11px; color: #444B6E; word-break: break-all; line-height: 1.2;">
                        {{ $thread->pow_hash }}
                    </div>
                    @if($thread->pow_difficulty)
                    <div style="font-size: 9px; color: #9AB87A; margin-top: 4px;">
                        Difficulty: {{ number_format($thread->pow_difficulty, 2) }}
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Post Content -->
            <div style="display: flex; gap: 25px; margin-bottom: 20px;">
                @if($thread->image_path)
                <div style="flex-shrink: 0;">
                    <div style="margin-bottom: 8px; font-size: 10px; color: #708B75;">
                        File: {{ $thread->image_filename ?: 'image' }}
                        @php
                            $imagePath = storage_path('app/public/' . $thread->image_path);
                            $filesize = file_exists($imagePath) ? filesize($imagePath) : 0;
                        @endphp
                        ({{ number_format($filesize / 1024, 1) }} KB)
                    </div>
                    <img src="{{ route('thread.image', $thread->id) }}"
                         style="max-width: 200px; max-height: 200px; border: 1px solid #708B75; border-radius: 5px; cursor: pointer;"
                         alt="{{ $thread->image_filename }}"
                         onclick="this.style.maxWidth = this.style.maxWidth === 'none' ? '200px' : 'none'">
                </div>
                @endif

                <div style="flex-grow: 1;">
                    <div style="color: #3D315B; font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                        {!! App\Helpers\MarkdownHelper::parseContent($thread->content) !!}
                    </div>
                </div>
            </div>

        </div>

        <!-- Replies Section -->
        @if(count($posts) > 0)
        <div style="margin-bottom: 40px;">
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #708B75;">
                <h3 style="color: #444B6E; font-size: 16px; margin: 0; font-weight: 400; letter-spacing: 1px;">
                    <span class="strobing-emoji">💭</span> Replies ({{ count($posts) }}) <span class="strobing-emoji">📝</span>
                </h3>
            </div>

            <div style="space-y: 20px;">
                @foreach($posts as $post)
                    @include('forum.post-recursive', ['post' => $post, 'level' => 0, 'thread' => $thread, 'board' => $board])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Reply Button (Imageboard Style) -->
        @if(!$thread->locked)
        <div style="text-align: center; margin-bottom: 20px;">
            <button onclick="toggleQuickReply()" id="quick-reply-btn"
                    style="background: #708B75; color: white; padding: 12px 24px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                💬 Quick Reply
            </button>
        </div>

        <!-- Reply Form (Initially Hidden) -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px; display: none;" id="reply-form">
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #708B75;">
                <h3 style="color: #444B6E; font-size: 16px; margin: 0; font-weight: 400; letter-spacing: 1px;">
                    <span class="strobing-emoji">✍️</span> Post Reply <span class="strobing-emoji">💫</span>
                </h3>
            </div>

            <form method="POST" action="/{{ strtolower($board->code) }}/{{ $thread->id }}/reply"
                  enctype="multipart/form-data" id="reply-form-actual"
                  data-original-action="/{{ strtolower($board->code) }}/{{ $thread->id }}/reply">
                @csrf
                <input type="hidden" name="pow_nonce" id="reply-pow-nonce" value="">
                <input type="hidden" name="pow_hash" id="reply-pow-hash" value="">
                <input type="hidden" name="pow_challenge_id" id="reply-pow-challenge-id" value="">

                <!-- Comment Field -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 8px; font-size: 12px;">Comment</label>
                    <textarea name="content" id="reply-content" required rows="6" maxlength="5000"
                              style="width: 100%; padding: 15px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 13px; line-height: 1.5; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <!-- File Upload -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 8px; font-size: 12px;">Media (optional) - Images, WebM, MP4</label>
                    <input type="file" name="image" accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif"
                           style="width: 100%; padding: 10px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 12px; box-sizing: border-box;">
                </div>

                <!-- Anonymous posting option for registered users -->
                @if(session('bitcoin_auth_id'))
                <div style="margin-bottom: 25px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #444B6E; font-size: 12px;">
                        <input type="checkbox" name="post_anonymous" value="1" style="margin: 0;">
                        Post as Anonymous (hide admin badge and username)
                    </label>
                </div>
                @endif

                <!-- Mining Status -->
                <div id="reply-mining-status" style="display: none; margin-bottom: 25px; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                    <div style="font-family: monospace; font-size: 11px; color: #666;">
                        <div style="margin-bottom: 10px;">⛏️ Mining proof of work...</div>
                        <div>Pattern: <strong>21e</strong></div>
                        <div>Hashes: <span id="reply-hash-count">0</span></div>
                        <div>Rate: <span id="reply-hash-rate">0</span> H/s</div>
                        <div style="margin-top: 15px;">
                            <button type="button" id="stop-reply-mining"
                                    style="background: #CD5C5C; color: white; border: none; padding: 8px 15px; border-radius: 3px; font-size: 11px; cursor: pointer;">
                                Stop Mining
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: center;">
                    <button type="button" id="mine-reply-btn"
                            style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; border: none; padding: 15px 30px; border-radius: 5px; font-size: 14px; font-weight: 600; cursor: pointer; letter-spacing: 0.5px; transition: all 0.3s;">
                        Mine & Submit Reply
                    </button>
                </div>
            </form>
        </div>
        @else
        <div style="text-align: center; padding: 40px; background: #F5F5DC; border: 2px dashed #9AB87A; border-radius: 8px;">
            <div style="font-size: 20px; margin-bottom: 10px;">🔒</div>
            <p style="color: #708B75; font-size: 14px; margin: 0;">This thread is locked and no longer accepting replies.</p>
        </div>
        @endif
    </div>
</div>

<script>
// Reply Hash Calculation System
class ReplyHashSystem {
    constructor() {
        this.bumpMultiplier = 10;
    }

    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async calculateReplyHash(postId, content) {
        try {
            const hash = await this.sha256(content);
            const hashElement = document.querySelector(`#hash-${postId} .hash-value`);
            const bumpIndicator = document.querySelector(`#hash-${postId} .hash-bump-indicator`);

            if (hashElement) {
                // Add calculating animation
                hashElement.classList.add('hash-calculating');

                setTimeout(() => {
                    hashElement.textContent = hash;
                    hashElement.classList.remove('hash-calculating');

                    if (hash.startsWith('21e8')) {
                        hashElement.classList.add('mining-glow');
                        this.trigger21e8Bump(postId, hash, bumpIndicator);

                        setTimeout(() => {
                            hashElement.classList.remove('mining-glow');
                        }, 3000);
                    }
                }, 500);
            }
        } catch (error) {
            console.error('Reply hash calculation failed:', error);
        }
    }

    trigger21e8Bump(postId, hash, bumpIndicator) {
        console.log(`🔥 21e8 BUMP! Reply #${postId}: ${hash}`);
        if (bumpIndicator) bumpIndicator.style.display = 'inline';

        const post = document.querySelector(`#post${postId}`);
        if (post) {
            post.style.border = '2px solid #ff6b35';
            post.style.boxShadow = '0 0 20px rgba(255, 107, 53, 0.5)';
        }
        this.submitBumpToServer(postId, hash);
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
                console.log(`✅ Reply bump applied! Post #${postId} +${result.bump_points} points`);
            }
        } catch (error) {
            console.error('Reply bump submission failed:', error);
        }
    }

    processAllReplies() {
        // Process all reply posts
        document.querySelectorAll('.post[data-post-id]').forEach(post => {
            const postId = post.dataset.postId;
            const contentEl = post.querySelector('.post-text');
            if (postId && contentEl) {
                const content = contentEl.textContent.trim();
                if (content) {
                    this.calculateReplyHash(postId, content);
                }
            }
        });
    }
}

// Reply mining system
let replyMiningInProgress = false;

function generateChallengeId() {
    const array = new Uint8Array(16);
    crypto.getRandomValues(array);
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
}

async function mineReplyProof(threadId, content, pattern) {
    const challengeId = generateChallengeId();
    const challengeData = `reply:{{ strtolower($board->code) }}:${threadId}:${challengeId}`;
    let nonce = 0, startTime = Date.now(), hashCount = 0;
    const maxTime = 30000; // 30 second timeout

    console.log('⛏️ Mining challenge data:', challengeData);
    console.log('🎯 Looking for pattern:', pattern);
    console.log('⏱️ Max mining time: 30 seconds');

    document.getElementById('reply-pow-challenge-id').value = challengeId;

    const hashCountEl = document.getElementById('reply-hash-count');
    const hashRateEl = document.getElementById('reply-hash-rate');
    const statusEl = document.getElementById('reply-mining-status');
    statusEl.style.display = 'block';

    // Add visual mining effects
    statusEl.classList.add('mining-shake');
    const patternEl = statusEl.querySelector('strong');
    if (patternEl) patternEl.classList.add('mining-glow');

    async function mineStep() {
        if (!replyMiningInProgress) return;

        // Check timeout
        const elapsed = Date.now() - startTime;
        if (elapsed > maxTime) {
            console.log('⏰ Mining timeout reached, submitting with dummy proof');
            // Submit with dummy proof if timeout
            document.getElementById('reply-pow-nonce').value = '0';
            document.getElementById('reply-pow-hash').value = '0000000000000000000000000000000000000000000000000000000000000000';
            replyMiningInProgress = false;
            statusEl.style.display = 'none';

            const replyForm = document.getElementById('reply-form-actual');
            const formData = new FormData(replyForm);
            const correctUrl = '/{{ strtolower($board->code) }}/{{ $thread->id }}/reply';

            submitReplyForm(formData, correctUrl);
            return;
        }

        const batchSize = 500;
        for (let i = 0; i < batchSize && replyMiningInProgress; i++) {
            const testData = challengeData + ':' + nonce;
            const encoder = new TextEncoder();
            const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(testData));
            const hashHex = Array.from(new Uint8Array(hashBuffer), b => b.toString(16).padStart(2, '0')).join('');

            hashCount++;
            const elapsedSecs = (Date.now() - startTime) / 1000;
            const rate = Math.round(hashCount / elapsedSecs);

            hashCountEl.textContent = hashCount.toLocaleString();
            hashRateEl.textContent = rate.toLocaleString();

            if (hashHex.startsWith(pattern.toLowerCase())) {
                console.log('💎 FOUND VALID PROOF!');
                console.log('🔗 Hash:', hashHex);
                console.log('🔢 Nonce:', nonce);
                console.log('📊 Attempts:', hashCount);

                document.getElementById('reply-pow-nonce').value = nonce;
                document.getElementById('reply-pow-hash').value = hashHex;
                replyMiningInProgress = false;
                statusEl.style.display = 'none';

                // Submit reply with valid proof
                const replyForm = document.getElementById('reply-form-actual');
                const formData = new FormData(replyForm);
                const correctUrl = '/{{ strtolower($board->code) }}/{{ $thread->id }}/reply';

                submitReplyForm(formData, correctUrl);

                return;
            }
            nonce++;
        }

        if (replyMiningInProgress) setTimeout(mineStep, 1);
    }

    await mineStep();
}

// Centralized reply submission function
function submitReplyForm(formData, url) {
    console.log('🚀 Submitting reply to:', url);
    console.log('📋 Form data content length:', formData.get('content') ? formData.get('content').length : 0);

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        if (response.ok) {
            console.log('✅ Reply successful, reloading page');
            window.location.reload();
        } else {
            response.text().then(text => {
                console.error('❌ Reply failed with status:', response.status);
                console.error('📄 Response:', text);
                alert('Reply failed: ' + response.status + ' - Check console for details');
            });
        }
    })
    .catch(error => {
        console.error('🔥 Network error:', error);
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        // Reset the button
        const btn = document.getElementById('mine-reply-btn');
        btn.disabled = false;
        btn.innerHTML = '⚡ Mine & Submit Reply';
        btn.classList.remove('mining-shake', 'mining-glow');
    });
}

// Initialize Reply Hash System
const replyHashSystem = new ReplyHashSystem();

document.addEventListener('DOMContentLoaded', function() {
    // Process all existing replies on page load
    replyHashSystem.processAllReplies();

    document.getElementById('mine-reply-btn').addEventListener('click', async () => {
        const content = document.getElementById('reply-content').value.trim();
        if (!content) {
            alert('Please enter a reply first!');
            return;
        }

        replyMiningInProgress = true;
        const btn = document.getElementById('mine-reply-btn');
        btn.disabled = true;
        btn.innerHTML = '⛏️ <span class="mining-active">⚡</span> Mining...';
        btn.classList.add('mining-shake', 'mining-glow');

        console.log('🚀 Starting reply mining for thread {{ $thread->id }}');
        console.log('📝 Content:', content);

        // Intermediate difficulty for replies
        await mineReplyProof({{ $thread->id }}, content, '21e');
    });

    document.getElementById('stop-reply-mining').addEventListener('click', () => {
        replyMiningInProgress = false;
        document.getElementById('reply-mining-status').style.display = 'none';
        const btn = document.getElementById('mine-reply-btn');
        btn.disabled = false;
        btn.innerHTML = '⚡ Mine & Submit Reply';
        btn.classList.remove('mining-shake', 'mining-glow');
    });

    // Initialize mining target
    function initThreadMining() {
        if (window.simpleMiner) {
            window.simpleMiner.switchTarget('thread', {{ $thread->id }}, '{{ addslashes($thread->title ?: "Thread #" . $thread->id) }}');
        } else {
            setTimeout(initThreadMining, 100);
        }
    }
    initThreadMining();

    // Sort functionality
    function changeSort() {
        const sortValue = document.getElementById('reply-sort').value;
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('sort', sortValue);
        window.location = currentUrl.toString();
    }

    // Force form action to stay lowercase
    const replyForm = document.getElementById('reply-form-actual');
    if (replyForm) {
        const correctAction = '/{{ strtolower($board->code) }}/{{ $thread->id }}/reply';
        replyForm.action = correctAction;

        // Form submission is now handled directly in the mining function
        // This prevents accidental form submission without mining
    }
});

// Quick Reply Functions (Imageboard Style)
function toggleQuickReply() {
    const replyForm = document.getElementById('reply-form');
    const quickBtn = document.getElementById('quick-reply-btn');

    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        replyForm.style.display = 'block';
        replyForm.scrollIntoView({ behavior: 'smooth' });
        quickBtn.textContent = '❌ Hide Reply';
        quickBtn.style.background = '#dc3545';
    } else {
        replyForm.style.display = 'none';
        quickBtn.textContent = '💬 Quick Reply';
        quickBtn.style.background = '#708B75';
    }
}

// Dynamic PoW Difficulty based on thread size
function getDynamicPoWPattern() {
    const replyCount = {{ count($posts ?? []) }};

    if (replyCount <= 10) {
        return '21e';     // Easy for small threads
    } else if (replyCount <= 50) {
        return '21e8';    // Medium for growing threads
    } else if (replyCount <= 100) {
        return '21e80';   // Hard for large threads
    } else {
        return '21e800';  // Very hard for huge threads
    }
}

// Update the mining function to use dynamic difficulty
const originalMineReplyProof = mineReplyProof;
async function mineReplyProof(threadId, content, staticPattern) {
    const dynamicPattern = getDynamicPoWPattern();
    console.log(`⚡ Dynamic PoW difficulty: ${dynamicPattern} (${{{ count($posts ?? []) }}} replies)`);
    return originalMineReplyProof(threadId, content, dynamicPattern);
}

// Imageboard-style post quoting
function quotePost(postId) {
    const replyForm = document.getElementById('reply-form');
    const textarea = document.getElementById('reply-content');

    // Show reply form if hidden
    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        toggleQuickReply();
    }

    // Add quote to textarea
    const quote = `>>${postId}\n`;
    const currentValue = textarea.value;

    if (!currentValue.includes(quote)) {
        textarea.value = currentValue + quote;
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    }
}

// Image expansion (imageboard style)
function expandImage(img) {
    if (img.style.maxWidth === 'none') {
        // Collapse to thumbnail
        img.style.maxWidth = '125px';
        img.style.maxHeight = '125px';
        img.style.position = 'static';
        img.style.zIndex = 'auto';
        img.style.boxShadow = 'none';
    } else {
        // Expand to full size
        img.style.maxWidth = 'none';
        img.style.maxHeight = 'none';
        img.style.position = 'relative';
        img.style.zIndex = '999';
        img.style.boxShadow = '0 4px 20px rgba(0,0,0,0.5)';

        // Scroll into view if needed
        img.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
</body>
</html>