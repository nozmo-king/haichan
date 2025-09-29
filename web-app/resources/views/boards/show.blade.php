@extends('layout')

@section('title', '{{ $board->title }}')

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

<!-- Thread Creation Form -->
<div class="postarea">
    <form method="POST" action="/{{ $board->code }}" enctype="multipart/form-data" id="thread-form">
        @csrf
        
        <table style="width: 100%;">
            <tr>
                <td style="font-weight: bold; width: 80px; text-align: right; padding-right: 8px;">Name:</td>
                <td><input type="text" name="name" placeholder="Anonymous" size="25"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding-right: 8px;">Subject:</td>
                <td><input type="text" name="title" maxlength="200" required size="35"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding-right: 8px; vertical-align: top;">Comment:</td>
                <td><textarea name="content" required rows="4" cols="48"></textarea></td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: right; padding-right: 8px;">File:</td>
                <td>
                    <input type="file" name="image" accept="image/*" required size="35">
                    <div style="font-size: 10px; color: var(--ib-text-muted); margin-top: 2px;"><strong>REQUIRED:</strong> Max 25MB • JPG, PNG, GIF, WebP, WebM, MP4, etc.</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding: 8px;">
                    <!-- PoW Mining Status -->
                    <div id="pow-status" style="margin-bottom: 8px; font-size: 10px; color: var(--ib-muted);">
                        <span id="thread-mining-indicator">Ready to mine</span>
                    </div>
                    
                    <input type="hidden" name="pow_nonce" id="thread-pow-nonce" required>
                    <input type="hidden" name="pow_hash" id="thread-pow-hash" required>
                    <input type="hidden" name="pow_challenge_id" id="thread-pow-challenge-id" required>
                    
                    <input type="submit" value="Submit" id="thread-submit-btn">
                    <button type="button" onclick="testSubmit()" style="margin-left: 10px;">TEST SUBMIT</button>
                </td>
            </tr>
        </table>
    </form>
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('thread-form');
    const submitBtn = document.getElementById('thread-submit-btn');
    const titleInput = form.querySelector('input[name="title"]');
    const contentInput = form.querySelector('textarea[name="content"]');
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
            miningIndicator.textContent = 'Complete form fields to start mining...';
            submitBtn.disabled = true;
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.value = 'Submit (Mining...)';
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

        miningIndicator.textContent = 'Mining proof of work...';

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

                        miningIndicator.textContent = `✅ Proof found! Hash: ${hashHex.substring(0,16)}...`;
                        submitBtn.disabled = false;
                        submitBtn.style.cursor = 'pointer';
                        submitBtn.value = 'Submit Thread ✨';
                        miningInProgress = false;
                        
                        // Show proof notification
                        showProofNotification(hashHex);
                        return;
                    }
                } catch (error) {
                    console.error('Mining error:', error);
                }

                nonce++;
            }

            const elapsed = (Date.now() - startTime) / 1000;
            const hashrate = Math.floor(hashCount / elapsed);
            miningIndicator.textContent = `Mining... ${hashrate} H/s (${hashCount.toLocaleString()} hashes)`;

            if (miningInProgress) {
                setTimeout(mineStep, 1);
            }
        }

        await mineStep();
    }

    function showProofNotification(hash) {
        // Create notification if it doesn't exist
        let notification = document.getElementById('proof-notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'proof-notification';
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #8B4513;
                color: #F5DEB3;
                border: 2px solid #654321;
                padding: 12px 16px;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            `;
            document.body.appendChild(notification);
        }
        
        notification.innerHTML = `⚡ PROOF FOUND!<br>Hash: ${hash.substring(0,16)}...`;
        notification.style.display = 'block';
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    titleInput.addEventListener('input', () => setTimeout(startMining, 500));
    contentInput.addEventListener('input', () => setTimeout(startMining, 500));

    form.addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
        console.log('PoW Hash:', document.getElementById('thread-pow-hash').value);
        console.log('PoW Nonce:', document.getElementById('thread-pow-nonce').value);
        console.log('Challenge ID:', document.getElementById('thread-pow-challenge-id').value);
        
        if (!document.getElementById('thread-pow-hash').value) {
            e.preventDefault();
            alert('Mining is required before submitting!');
            console.log('Form submission blocked - no PoW hash');
            return false;
        }
        
        console.log('Form submission allowed - PoW validated');
    });

    // Test function to bypass mining
    window.testSubmit = function() {
        console.log('TEST SUBMIT clicked');
        document.getElementById('thread-pow-hash').value = '21e8' + 'a'.repeat(60);
        document.getElementById('thread-pow-nonce').value = '12345';
        document.getElementById('thread-pow-challenge-id').value = 'b'.repeat(32);
        console.log('Test values set, submitting form...');
        form.submit();
    };

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