@extends('layout')

@section('title', $board->title . ' - Haichan')

@section('content')
<!-- Japanese Web Aesthetic Container with Homepage Style -->
<div style="margin: 60px auto 40px auto; max-width: 900px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header with proper color scheme -->
    <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 25px 40px; border-bottom: 2px solid #708B75; position: relative; text-align: center;">
        <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 4px 12px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
            β版
        </div>

        <h1 style="font-size: 28px; color: #3D315B; margin: 0 0 12px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            <span class="strobing-emoji" style="font-size: 26px; color: #B87333;">⛏</span>
            {{ $board->title }}
            <span class="strobing-emoji" style="font-size: 26px; color: #CD5C5C;">⚡</span>
        </h1>

        <div style="width: 80px; height: 2px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 15px auto;"></div>

        <p style="color: #708B75; font-size: 13px; line-height: 1.6; margin: 15px 0 0 0; font-weight: 400;">{{ $board->description }}</p>

        <!-- Navigation breadcrumb with proper spacing -->
        <div style="margin-top: 20px; font-size: 11px; color: #444B6E;">
            <a href="{{ route('boards.index') }}" style="color: #708B75; text-decoration: none; margin-right: 10px;">[Boards]</a>
            <a href="/{{ $board->code }}/catalog" style="color: #708B75; text-decoration: none; margin-right: 10px;">[Catalog]</a>
            <span style="color: #9AB87A;">[Current: {{ $board->code }}]</span>
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

        <!-- Thread Creation Form -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px; margin-bottom: 40px;">
            <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #708B75;">
                <h2 style="color: #444B6E; font-size: 18px; margin: 0; font-weight: 400; letter-spacing: 1px;">
                    <span class="strobing-emoji">✍️</span> Start New Thread <span class="strobing-emoji">✨</span>
                </h2>
            </div>

            <form method="POST" action="/{{ $board->code }}" enctype="multipart/form-data" id="thread-form">
                @csrf

                <!-- Subject Field -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 8px; font-size: 12px;">Subject</label>
                    <input type="text" name="title" maxlength="200" required
                           style="width: 100%; padding: 12px 15px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 13px; box-sizing: border-box;">
                </div>

                <!-- Comment Field -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 8px; font-size: 12px;">Comment</label>
                    <textarea name="content" required rows="8"
                              style="width: 100%; padding: 15px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 13px; line-height: 1.5; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <!-- File Upload -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 8px; font-size: 12px;">Image (required) 📷</label>
                    <input type="file" name="image" accept="image/*" required
                           style="width: 100%; padding: 10px; border: 2px solid #708B75; border-radius: 5px; background: #FFFFEE; color: #3D315B; font-size: 12px; box-sizing: border-box;">
                    <div style="font-size: 10px; color: #708B75; margin-top: 5px;">Max 2MB • JPG, PNG, GIF</div>
                </div>

                <!-- Proof of Work Mining -->
                <div style="margin-bottom: 25px; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                    <label style="display: block; color: #444B6E; font-weight: 600; margin-bottom: 12px; font-size: 12px;">⛏️ Proof of Work Mining</label>
                    <div id="thread-mining-status" style="font-family: monospace; font-size: 11px; color: #666;">
                        <div style="margin-bottom: 10px;">
                            <span id="thread-mining-indicator">⛏️</span>
                            <span id="thread-mining-text">Complete form fields to start mining...</span>
                        </div>
                        <div id="thread-mining-progress" style="background: #DDD; height: 10px; border-radius: 5px; overflow: hidden;">
                            <div id="thread-mining-bar" style="background: linear-gradient(90deg, #708B75, #9AB87A); height: 100%; width: 0%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                    <input type="hidden" name="pow_nonce" id="thread-pow-nonce" required>
                    <input type="hidden" name="pow_hash" id="thread-pow-hash" required>
                    <input type="hidden" name="pow_challenge_id" id="thread-pow-challenge-id" required>
                </div>

                <!-- Submit Button -->
                <div style="text-align: center;">
                    <button type="submit" id="thread-submit-btn" disabled
                            style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; border: none; padding: 15px 40px; border-radius: 5px; font-size: 14px; font-weight: 600; cursor: not-allowed; letter-spacing: 0.5px; transition: all 0.3s;">
                        Submit Thread (Mining...)
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Threads -->
        <div style="margin-top: 40px;">
            <div style="text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #708B75;">
                <h2 style="color: #444B6E; font-size: 18px; margin: 0; font-weight: 400; letter-spacing: 1px;">
                    <span class="strobing-emoji">💬</span> Recent Threads <span class="strobing-emoji">🔥</span>
                </h2>
            </div>

            <div class="threads-list">
                @forelse($threads as $thread)
                <div style="background: #F5F5DC; border: 1px solid #9AB87A; border-radius: 8px; padding: 25px; margin-bottom: 20px; transition: all 0.3s; hover:box-shadow: 0 2px 8px rgba(68, 75, 110, 0.2); position: relative;"
                     data-thread-id="{{ $thread->id }}" data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}">

                    @if($thread->accumulated_points > 0)
                    <!-- Energy Expenditure Badge -->
                    <div style="position: absolute; top: -8px; right: 15px; background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; padding: 4px 12px; font-size: 10px; border-radius: 12px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: 2px solid #FFFFEE;">
                        ⚡ {{ number_format($thread->accumulated_points, 1) }}
                    </div>
                    @endif

                    <!-- Thread Header -->
                    <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #9AB87A;">
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600;">
                            <a href="/{{ $board->code }}/{{ $thread->id }}" style="color: #3D315B; text-decoration: none;">
                                {{ $thread->title ?: 'No Subject' }}
                            </a>
                        </h3>
                        <div style="color: #708B75; font-size: 11px;">
                            @if($board->code === 'pol' && $thread->country_flag)
                                <span style="font-size: 16px; margin-right: 5px; vertical-align: middle;">{{ $thread->country_flag }}</span>
                            @endif
                            @if($thread->is_anonymous_post ?? false)
                                <span style="color: #8B008B; font-weight: bold;">Anonymous</span>
                            @else
                                Anonymous
                            @endif
                            • {{ $thread->created_at->format('M d, Y H:i') }} • No.{{ $thread->id }}
                            @if($thread->user_id)
                                @include('components.admin-badge', ['user' => $thread->bitcoinUser])
                            @endif
                        </div>
                        @if($thread->pow_hash)
                        <div style="margin-top: 8px; padding: 6px; background: rgba(154, 184, 122, 0.05); border: 1px solid rgba(154, 184, 122, 0.3); border-radius: 3px;">
                            <div style="font-size: 9px; color: #708B75; font-weight: bold; margin-bottom: 3px;">⛏️ PROOF HASH</div>
                            <div style="font-family: 'Courier New', monospace; font-size: 10px; color: #666; word-break: break-all;">
                                {{ Str::limit($thread->pow_hash, 32, '...') }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Thread Content -->
                    <div style="display: flex; gap: 20px;">
                        @if($thread->image_path)
                        <div style="flex-shrink: 0;">
                            <img src="/storage/{{ $thread->image_path }}"
                                 style="max-width: 150px; max-height: 150px; border: 1px solid #708B75; border-radius: 4px;">
                        </div>
                        @endif

                        <div style="flex-grow: 1;">
                            <div style="color: #3D315B; font-size: 13px; line-height: 1.6; margin-bottom: 15px;">
                                {!! nl2br(e(Str::limit($thread->content, 300))) !!}
                            </div>

                            <!-- Thread Stats -->
                            <div style="color: #9AB87A; font-size: 10px; font-family: monospace;">
                                💬 {{ $thread->reply_count }} replies •
                                🖼️ {{ $thread->image_count }} images •
                                🕒 {{ $thread->bumped_at ? $thread->bumped_at->diffForHumans() : $thread->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 60px; background: #F5F5DC; border: 2px dashed #9AB87A; border-radius: 8px;">
                    <div style="font-size: 24px; margin-bottom: 15px;">🌱</div>
                    <p style="color: #708B75; font-size: 14px; margin: 0;">No threads yet. Be the first to start the conversation!</p>
                </div>
                @endforelse
            </div>

            <!-- Energy-based sorting - no pagination needed as we show top 20 by expenditure -->
            @if(count($threads) >= 20)
            <div style="text-align: center; padding: 30px 0; border-top: 1px solid #708B75; margin-top: 30px; color: #708B75; font-size: 14px;">
                <em>Showing top 20 threads by energy expenditure</em>
            </div>
            @endif
        </div>
    </div>
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

    function generateChallengeId() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    function startMining() {
        if (miningInProgress) return;

        const title = titleInput.value.trim();
        const content = contentInput.value.trim();

        if (!title || !content) {
            miningText.textContent = 'Complete form fields to start mining...';
            submitBtn.disabled = true;
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.style.opacity = '0.6';
            submitBtn.textContent = 'Submit Thread (Mining...)';
            return;
        }

        miningInProgress = true;
        currentChallenge = generateChallengeId();
        document.getElementById('thread-pow-challenge-id').value = currentChallenge;

        const challengeData = `thread:{{ $board->code }}:${title}:${currentChallenge}`;
        const targetPattern = '21e8';

        mineProof(challengeData, targetPattern);
    }

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
                        document.getElementById('thread-pow-nonce').value = nonce;
                        document.getElementById('thread-pow-hash').value = hashHex;

                        miningText.textContent = `✅ Proof found! Hash: ${hashHex}`;
                        miningIndicator.textContent = '✅';
                        miningBar.style.width = '100%';
                        miningBar.style.background = 'linear-gradient(90deg, #4CAF50, #8BC34A)';

                        submitBtn.disabled = false;
                        submitBtn.style.cursor = 'pointer';
                        submitBtn.style.opacity = '1';
                        submitBtn.textContent = 'Submit Thread ✨';
                        miningInProgress = false;
                        return;
                    }
                } catch (error) {
                    console.error('Mining error:', error);
                }

                nonce++;
            }

            const elapsed = (Date.now() - startTime) / 1000;
            const hashrate = Math.floor(hashCount / elapsed);
            miningText.textContent = `Mining... ${hashrate} H/s (${hashCount.toLocaleString()} hashes)`;

            const progress = Math.min((hashCount / 10000) * 100, 95);
            miningBar.style.width = progress + '%';

            if (miningInProgress) {
                setTimeout(mineStep, 1);
            }
        }

        await mineStep();
    }

    titleInput.addEventListener('input', () => setTimeout(startMining, 500));
    contentInput.addEventListener('input', () => setTimeout(startMining, 500));

    form.addEventListener('submit', function(e) {
        if (!document.getElementById('thread-pow-hash').value) {
            e.preventDefault();
            alert('Mining is required before submitting!');
        }
    });

    // Auto-refresh threads
    setInterval(() => {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newThreadsList = doc.querySelector('.threads-list');
            const currentThreadsList = document.querySelector('.threads-list');

            if (newThreadsList && currentThreadsList) {
                currentThreadsList.style.opacity = '0.7';
                setTimeout(() => {
                    currentThreadsList.innerHTML = newThreadsList.innerHTML;
                    currentThreadsList.style.opacity = '1';
                }, 300);
            }
        })
        .catch(err => console.log('Refresh failed:', err));
    }, 15000);
});
</script>
@endsection