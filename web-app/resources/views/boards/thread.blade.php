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

        <h1 style="font-size: 24px; color: #3D315B; margin: 0 0 12px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            <span class="strobing-emoji" style="font-size: 22px; color: #B87333;">💬</span>
            {{ $thread->title ?: 'Thread #' . $thread->id }}
            <span class="strobing-emoji" style="font-size: 22px; color: #CD5C5C;">⚡</span>
        </h1>

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
        <div id="post{{ $thread->id }}" style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px; margin-bottom: 30px;"
             data-mine-type="thread" data-mine-target="thread-{{ $thread->id }}" data-mine-weight="60"
             data-thread-id="{{ $thread->id }}" data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}">

            <!-- Post Header -->
            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #9AB87A;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                    <h2 style="color: #3D315B; font-size: 18px; margin: 0; font-weight: 600;">
                        {{ $thread->title ?: 'No Subject' }}
                    </h2>
                    <div style="color: #708B75; font-size: 11px; text-align: right;">
                        <div>Anonymous • {{ $thread->created_at->format('M d, Y H:i') }}</div>
                        <div><a href="#post{{ $thread->id }}" style="color: #9AB87A; text-decoration: none;">No.{{ $thread->id }}</a></div>
                    </div>
                </div>
            </div>

            <!-- Post Content -->
            <div style="display: flex; gap: 25px; margin-bottom: 20px;">
                @if($thread->image_path)
                <div style="flex-shrink: 0;">
                    <div style="margin-bottom: 8px; font-size: 10px; color: #708B75;">
                        File: {{ $thread->image_filename ?: 'image' }}
                        ({{ number_format(filesize(storage_path('app/public/' . $thread->image_path)) / 1024, 1) }} KB)
                    </div>
                    <img src="{{ route('thread.image', $thread->id) }}"
                         style="max-width: 200px; max-height: 200px; border: 1px solid #708B75; border-radius: 5px; cursor: pointer;"
                         alt="{{ $thread->image_filename }}"
                         onclick="this.style.maxWidth = this.style.maxWidth === 'none' ? '200px' : 'none'">
                </div>
                @endif

                <div style="flex-grow: 1;">
                    <div style="color: #3D315B; font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                        {!! nl2br(e($thread->content)) !!}
                    </div>
                </div>
            </div>

            <!-- Post Hash -->
            <div id="hash-{{ $thread->id }}" style="font-family: monospace; font-size: 10px; color: #9AB87A; padding: 10px; background: #FFFACD; border-radius: 3px;">
                <span style="color: #708B75;">sha256:</span>
                <span class="hash-value">calculating...</span>
                <span class="hash-bump-indicator" style="display: none; color: #ff6b35; font-weight: bold; margin-left: 10px;">🔥 21e8 BUMP!</span>
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

        <!-- Reply Form -->
        @if(!$thread->locked)
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px;" id="reply-form">
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
                    <textarea name="content" id="reply-content" required rows="6"
                              style="width: 100%; padding: 15px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 13px; line-height: 1.5; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <!-- File Upload -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 8px; font-size: 12px;">Image (optional)</label>
                    <input type="file" name="image" accept="image/*"
                           style="width: 100%; padding: 10px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 12px; box-sizing: border-box;">
                </div>

                <!-- Mining Status -->
                <div id="reply-mining-status" style="display: none; margin-bottom: 25px; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                    <div style="font-family: monospace; font-size: 11px; color: #666;">
                        <div style="margin-bottom: 10px;">⛏️ Mining proof of work...</div>
                        <div>Pattern: <strong>21e8</strong></div>
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
// Hash calculation system
class PostHashSystem {
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

    async calculatePostHash(postId, content) {
        try {
            const hash = await this.sha256(content);
            const hashElement = document.querySelector(`#hash-${postId} .hash-value`);
            const bumpIndicator = document.querySelector(`#hash-${postId} .hash-bump-indicator`);

            if (hashElement) {
                hashElement.textContent = hash.substring(0, 16) + '...';
                hashElement.title = hash;
                if (hash.startsWith('21e8')) {
                    this.trigger21e8Bump(postId, hash, bumpIndicator);
                }
            }
        } catch (error) {
            console.error('Hash calculation failed:', error);
        }
    }

    trigger21e8Bump(postId, hash, bumpIndicator) {
        console.log(`🔥 21e8 BUMP! Post #${postId}: ${hash}`);
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
                console.log(`✅ Bump applied! Post #${postId} +${result.bump_points} points`);
            }
        } catch (error) {
            console.error('Bump submission failed:', error);
        }
    }

    async processAllPosts() {
        const threadContent = `{!! addslashes($thread->content) !!}`;
        await this.calculatePostHash({{ $thread->id }}, threadContent);

        @php
        function flattenPosts($posts, $flattened = []) {
            foreach ($posts as $post) {
                $flattened[] = $post;
                if ($post->allReplies && $post->allReplies->count() > 0) {
                    $flattened = flattenPosts($post->allReplies, $flattened);
                }
            }
            return $flattened;
        }
        $allPosts = flattenPosts($posts);
        @endphp

        @foreach($allPosts as $post)
        const post{{ $post->id }}Content = `{!! addslashes($post->content) !!}`;
        await this.calculatePostHash({{ $post->id }}, post{{ $post->id }}Content);
        @endforeach
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
    const challengeData = `post:${threadId}:${content}:${challengeId}`;
    let nonce = 0, startTime = Date.now(), hashCount = 0;

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
                document.getElementById('reply-form-actual').submit();
                return;
            }
            nonce++;
        }

        if (replyMiningInProgress) setTimeout(mineStep, 1);
    }

    await mineStep();
}

// Initialize
const postHashSystem = new PostHashSystem();

document.addEventListener('DOMContentLoaded', function() {
    postHashSystem.processAllPosts();

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

    document.getElementById('stop-reply-mining').addEventListener('click', () => {
        replyMiningInProgress = false;
        document.getElementById('reply-mining-status').style.display = 'none';
        document.getElementById('mine-reply-btn').disabled = false;
        document.getElementById('mine-reply-btn').textContent = 'Mine & Submit Reply';
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

    // Force form action to stay lowercase
    const replyForm = document.getElementById('reply-form-actual');
    if (replyForm) {
        const correctAction = '/{{ strtolower($board->code) }}/{{ $thread->id }}/reply';
        replyForm.action = correctAction;

        // Intercept form submission and force correct URL
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(replyForm);
            const correctUrl = '/{{ strtolower($board->code) }}/{{ $thread->id }}/reply';

            fetch(correctUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Reply failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Reply error:', error);
                alert('Reply failed. Please try again.');
            });
        });
    }
});
</script>
</body>
</html>