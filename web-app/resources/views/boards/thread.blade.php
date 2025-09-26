<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($thread->title ?: 'Thread') . ' - /' . $board->code . '/' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/elegant-themes.css">
    <script>
        // Define toggleQuickReply early to prevent "not defined" errors
        window.toggleQuickReply = function() {
            const replyForm = document.getElementById('reply-form');
            const quickBtn = document.getElementById('quick-reply-btn');

            if (!replyForm || !quickBtn) return; // Guard against missing elements

            if (replyForm.style.display === 'none' || !replyForm.style.display) {
                replyForm.style.display = 'block';
                replyForm.scrollIntoView({ behavior: 'smooth' });
                quickBtn.textContent = 'Hide Reply';
                quickBtn.style.background = 'var(--warning-color, #CD5C5C)';
            } else {
                replyForm.style.display = 'none';
                quickBtn.textContent = 'Quick Reply';
                quickBtn.style.background = 'var(--accent-color, #708B75)';
            }
        };
    </script>
    <style>
        :root {
            --bg: #FFFFEE;
            --card: #F5F5DC;
            --primary: #708B75;
            --primary-2: #9AB87A;
            --text: #3D315B;
            --text-2: #444B6E;
            --muted: #666;
            --danger: #CD5C5C;
            --border: #9AB87A;
        }
        body {
            margin: 0;
            padding: 0;
            background: #FFFDF5;
            font-family: Georgia, serif;
            color: var(--text);
        }
        .container {
            margin: 40px auto;
            max-width: 900px;
            background: var(--card);
            border: 2px solid var(--primary);
            box-shadow: 0 4px 16px rgba(68, 75, 110, 0.15);
        }
        .header {
            background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%);
            padding: 20px 28px;
            border-bottom: 2px solid var(--primary);
        }
        .breadcrumb {
            font-size: 12px;
            color: var(--text-2);
            margin-bottom: 8px;
        }
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            margin-right: 8px;
        }
        .title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .thread-title {
            font-size: 20px;
            margin: 0;
            font-weight: 600;
            color: var(--text);
        }
        .badge {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #FFFFEE;
            padding: 6px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid #FFFFEE;
        }
        .meta {
            font-size: 11px;
            color: var(--primary);
        }
        .content {
            padding: 24px;
            background: var(--bg);
        }
        .op {
            position: relative;
            background: var(--card);
            border: 2px solid var(--primary);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .op-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .op-image {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid var(--primary);
            border-radius: 5px;
            cursor: pointer;
        }
        .reply-section h3 {
            text-align: center;
            font-weight: 500;
            font-size: 16px;
            color: var(--text-2);
            margin: 0 0 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--primary);
        }
        .quick-reply-btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }
        .reply-card {
            background: var(--card);
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 20px;
        }
        .reply-card label {
            color: var(--text-2);
            font-size: 12px;
            margin-bottom: 6px;
            display: block;
        }
        .reply-card textarea,
        .reply-card input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--border);
            border-radius: 4px;
            background: var(--bg);
            color: var(--text);
            font-size: 13px;
            box-sizing: border-box;
        }
        .reply-submit {
            background: var(--primary);
            color: #FFFFEE;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .sort-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }
        .sort-row select {
            padding: 6px 8px;
            background: var(--card);
            border: 1px solid var(--primary);
            border-radius: 4px;
            color: var(--text);
            font-size: 12px;
        }
        .mining-status {
            display: none;
            margin-top: 12px;
            padding: 12px;
            background: #FFFACD;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: monospace;
            font-size: 11px;
            color: var(--muted);
        }
        .locked {
            text-align: center;
            padding: 24px;
            background: var(--card);
            border: 2px dashed var(--border);
            border-radius: 8px;
            color: var(--primary);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="breadcrumb">
            <a href="{{ route('boards.index') }}">Boards</a> ›
            <a href="/{{ $board->code }}">/{{ $board->code }}/</a> ›
            <a href="/{{ $board->code }}/catalog">Catalog</a> ›
            <span>Thread #{{ $thread->id }}</span>
        </div>

        <div class="title-row">
            <h1 class="thread-title">{{ $thread->title ?: 'Thread #' . $thread->id }}</h1>
            <div class="badge" id="thread-mining-badge">
                ⛏️ <span id="thread-pow-number">{{ number_format($thread->accumulated_points ?: 0, 2) }}</span>
            </div>
        </div>

        <div class="sort-row">
            <label for="reply-sort">Sort replies:</label>
            <select id="reply-sort" onchange="changeSort()">
                <option value="chronological" {{ request('sort') === 'chronological' || !request('sort') ? 'selected' : '' }}>Chronological</option>
                <option value="pow" {{ request('sort') === 'pow' ? 'selected' : '' }}>By PoW</option>
            </select>
        </div>
    </div>

    <div class="content">
        @if($errors->any())
        <div style="background: #FFE6E6; border: 1px solid #FF9999; padding: 12px; margin-bottom: 20px; border-radius: 5px;">
            @foreach($errors->all() as $error)
                <p style="color: #CC0000; margin: 4px 0; font-size: 12px;">• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <!-- Original Post -->
        <div id="post{{ $thread->id }}" class="op"
             data-mine-type="thread" data-mine-target="thread-{{ $thread->id }}"
             data-thread-id="{{ $thread->id }}" data-thread-title="{{ $thread->title ?: 'Thread #' . $thread->id }}">

            <div class="op-header">
                <div>
                    <div class="meta">
                        @if($thread->user_id && $thread->bitcoinUser)
                            {{ $thread->bitcoinUser->getDisplayName() }}
                        @else
                            Anonymous
                        @endif
                        • {{ $thread->created_at->format('M d, Y H:i') }}
                        @if($thread->user_id)
                            @include('components.admin-badge', ['user' => $thread->bitcoinUser])
                        @endif
                        <span style="margin-left: 8px;"><a href="#post{{ $thread->id }}" style="color: var(--primary); text-decoration: none;">No.{{ $thread->id }}</a></span>
                    </div>
                    @if($thread->pow_hash)
                    <div style="margin-top: 8px; padding: 8px; background: rgba(154, 184, 122, 0.1); border: 1px solid var(--border); border-radius: 4px;">
                        <div style="font-size: 10px; color: var(--primary); font-weight: 600; margin-bottom: 4px;">
                            PoW • SHA-256
                        </div>
                        <div style="font-family: 'Courier New', monospace; font-size: 11px; color: var(--text-2); word-break: break-all; line-height: 1.2;">
                            {{ $thread->pow_hash }}
                        </div>
                        @if($thread->pow_difficulty)
                        <div style="font-size: 9px; color: var(--border); margin-top: 4px;">
                            Difficulty: {{ number_format($thread->pow_difficulty, 2) }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @if($thread->user_id === session('bitcoin_auth_id') || (session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin))
                <form method="POST" action="{{ route('threads.delete.user', $thread->id) }}" onsubmit="return confirm('Delete this entire thread and all replies?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 11px;">Delete Thread</button>
                </form>
                @endif
            </div>

            <div style="display: flex; gap: 20px;">
                @if($thread->image_path)
                <div style="flex-shrink: 0;">
                    <div style="margin-bottom: 6px; font-size: 10px; color: var(--primary);">
                        File: {{ $thread->image_filename ?: 'image' }}
                        @php
                            $imagePath = storage_path('app/public/' . $thread->image_path);
                            $filesize = file_exists($imagePath) ? filesize($imagePath) : 0;
                        @endphp
                        ({{ number_format($filesize / 1024, 1) }} KB)
                    </div>
                    <img src="{{ route('thread.image', $thread->id) }}"
                         class="op-image"
                         alt="{{ $thread->image_filename }}"
                         onclick="this.style.maxWidth = this.style.maxWidth === 'none' ? '200px' : 'none'">
                </div>
                @endif

                <div style="flex-grow: 1;">
                    <div style="color: var(--text); font-size: 14px; line-height: 1.6;">
                        {!! App\Helpers\MarkdownHelper::parseContent($thread->content) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Replies -->
        @if(count($posts) > 0)
        <div class="reply-section" style="margin-bottom: 24px;">
            <h3>Replies ({{ count($posts) }})</h3>
            <div>
                @foreach($posts as $post)
                    @include('forum.post-recursive', ['post' => $post, 'level' => 0, 'thread' => $thread, 'board' => $board])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Reply -->
        @if(!($thread->locked ?? false))
        <div style="text-align: center; margin-bottom: 16px;">
            <button onclick="toggleQuickReply()" id="quick-reply-btn" class="quick-reply-btn">Quick Reply</button>
        </div>

        <div class="reply-card" id="reply-form" style="display: none;">
            <form method="POST" action="/{{ strtolower($board->code) }}/{{ $thread->id }}/reply"
                  enctype="multipart/form-data" id="reply-form-actual"
                  data-original-action="/{{ strtolower($board->code) }}/{{ $thread->id }}/reply">
                @csrf
                <input type="hidden" name="pow_nonce" id="reply-pow-nonce" value="0">
                <input type="hidden" name="pow_hash" id="reply-pow-hash" value="">
                <input type="hidden" name="pow_challenge_id" id="reply-pow-challenge-id" value="quicksubmit">

                <label for="reply-content">Comment</label>
                <textarea name="content" id="reply-content" required rows="6" maxlength="5000"></textarea>

                <label for="reply-file">Media (optional)</label>
                <input type="file" id="reply-file" name="image" accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif">
                
                <!-- OR use existing image hash -->
                <div style="margin-top: 10px; padding: 8px; background: #F0F8FF; border: 1px dashed #708B75; border-radius: 4px;">
                    <label for="reply-image-hash" style="font-size: 11px; color: #444B6E; font-weight: bold;">OR use existing image hash:</label>
                    <input type="text" name="image_hash" id="reply-image-hash" placeholder="Paste hash from library..."
                           style="width: 100%; padding: 4px; margin: 4px 0; font-family: monospace; font-size: 10px;">
                </div>

                @if(session('bitcoin_auth_id'))
                <div style="margin: 10px 0;">
                    <label style="display: flex; align-items: center; gap: 8px; color: var(--text-2); font-size: 12px;">
                        <input type="checkbox" name="post_anonymous" value="1" style="margin: 0;">
                        Post as Anonymous
                    </label>
                </div>
                @endif

                <div class="mining-status" id="reply-mining-status">
                    <div>Pattern: <strong>21e</strong></div>
                    <div>Hashes: <span id="reply-hash-count">0</span></div>
                    <div>Rate: <span id="reply-hash-rate">0</span> H/s</div>
                    <div style="margin-top: 8px;">
                        <button type="button" id="stop-reply-mining" class="quick-reply-btn" style="background: var(--danger);">Stop Mining</button>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 12px; display: flex; gap: 10px; justify-content: center;">
                    <button type="button" id="mine-reply-btn" class="reply-submit">Mine & Submit Reply</button>
                    <button type="submit" id="quick-submit-btn" class="reply-submit" style="background: var(--primary-2);">Quick Submit (No Mining)</button>
                </div>
            </form>
        </div>
        @else
        <div class="locked">
            This thread is locked and no longer accepting replies.
        </div>
        @endif
    </div>
</div>

<script>
// Minimal, clean JS — keep functionality, reduce visual noise

class ReplyHashSystem {
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
            if (hashElement) hashElement.textContent = hash;
        } catch (error) {
            console.error('Reply hash calculation failed:', error);
        }
    }


        document.querySelectorAll('.post[data-post-id]').forEach(post => {
            const postId = post.dataset.postId;
            const contentEl = post.querySelector('.post-text');
            if (postId && contentEl) {
                const content = contentEl.textContent.trim();
                if (content) this.calculateReplyHash(postId, content);
            }
        });
    }
}

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
    const maxTime = 30000;

    // Set the challenge ID in the form
    document.getElementById('reply-pow-challenge-id').value = challengeId;

    const hashCountEl = document.getElementById('reply-hash-count');
    const hashRateEl = document.getElementById('reply-hash-rate');
    const statusEl = document.getElementById('reply-mining-status');
    statusEl.style.display = 'block';

    async function mineStep() {
        if (!replyMiningInProgress) return;

        const elapsed = Date.now() - startTime;
        if (elapsed > maxTime) {
            document.getElementById('reply-pow-nonce').value = '0';
            document.getElementById('reply-pow-hash').value = '0'.repeat(64);
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
                document.getElementById('reply-pow-nonce').value = nonce;
                document.getElementById('reply-pow-hash').value = hashHex;
                replyMiningInProgress = false;
                statusEl.style.display = 'none';

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

function submitReplyForm(formData, url) {
    console.log('📤 SUBMITTING REPLY FORM TO:', url);
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('📥 REPLY RESPONSE STATUS:', response.status);
        if (response.ok) {
            console.log('✅ REPLY SUBMITTED SUCCESSFULLY');
            window.location.reload();
        } else {
            response.text().then(text => {
                console.error('❌ REPLY FAILED:', response.status, text);
                alert('Reply failed: ' + response.status + '\n' + text.substring(0, 200));
            });
        }
    })
    .catch(error => {
        console.error('❌ NETWORK ERROR:', error);
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        const btn = document.getElementById('mine-reply-btn');
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Mine & Submit Reply';
        }
    });
}

const replyHashSystem = new ReplyHashSystem();

window.toggleQuickReply = function() {
    const replyForm = document.getElementById('reply-form');
    const quickBtn = document.getElementById('quick-reply-btn');

    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        replyForm.style.display = 'block';
        replyForm.scrollIntoView({ behavior: 'smooth' });
        quickBtn.textContent = 'Hide Reply';
        quickBtn.style.background = 'var(--warning-color, #CD5C5C)';
    } else {
        replyForm.style.display = 'none';
        quickBtn.textContent = 'Quick Reply';
        quickBtn.style.background = 'var(--accent-color, #708B75)';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    replyHashSystem.processAllReplies();

    document.getElementById('mine-reply-btn').addEventListener('click', async () => {
        console.log('🔄 Mine reply button clicked');
        const content = document.getElementById('reply-content').value.trim();
        if (!content) {
            alert('Please enter a reply first!');
            return;
        }

        console.log('✅ Content validated, starting mining...');
        replyMiningInProgress = true;
        const btn = document.getElementById('mine-reply-btn');
        btn.disabled = true;
        btn.textContent = 'Mining...';

        try {
            await mineReplyProof({{ $thread->id }}, content, '21e');
            console.log('✅ Mining completed');
        } catch (error) {
            console.error('❌ Mining error:', error);
            alert('Mining failed: ' + error.message);
            btn.disabled = false;
            btn.textContent = 'Mine & Submit Reply';
            replyMiningInProgress = false;
        }
    });

    document.getElementById('stop-reply-mining').addEventListener('click', () => {
        replyMiningInProgress = false;
        document.getElementById('reply-mining-status').style.display = 'none';
        const btn = document.getElementById('mine-reply-btn');
        btn.disabled = false;
        btn.textContent = 'Mine & Submit Reply';
    });

    document.getElementById('quick-submit-btn').addEventListener('click', (e) => {
        console.log('🚀 Quick submit button clicked');
        const content = document.getElementById('reply-content').value.trim();
        console.log('Content:', content);
        if (!content) {
            alert('Please enter a reply first!');
            e.preventDefault();
            return false;
        }
        console.log('✅ Quick submit validation passed, form should submit normally');
    });

    function initThreadMining() {
        // Set the current mining target to this thread
        if (window.directMiner) {
            window.directMiner.currentTarget = 'thread:{{ $thread->id }}';
            console.log('🎯 SET THREAD MINING TARGET:', window.directMiner.currentTarget);
        }
        
        // Also set for emergency miner if available
        if (window.emergencyMiner) {
            window.emergencyMiner.currentTarget = 'thread:{{ $thread->id }}';
        }
    }
    initThreadMining();

    window.changeSort = function() {
        const sortValue = document.getElementById('reply-sort').value;
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('sort', sortValue);
        window.location = currentUrl.toString();
    };

    const replyForm = document.getElementById('reply-form-actual');
    if (replyForm) {
        const correctAction = '/{{ strtolower($board->code) }}/{{ $thread->id }}/reply';
        replyForm.action = correctAction;
        console.log('📝 REPLY FORM ACTION SET TO:', correctAction);
        
        // Add form submission debug listener
        replyForm.addEventListener('submit', function(e) {
            console.log('📤 FORM SUBMISSION EVENT TRIGGERED');
            console.log('📋 Form data:', new FormData(replyForm));
            const content = document.getElementById('reply-content').value;
            if (!content.trim()) {
                alert('Please enter a reply first!');
                e.preventDefault();
                return false;
            }
            console.log('✅ Form validation passed');
        });
    }
});

function getDynamicPoWPattern() {
    const replyCount = {{ count($posts ?? []) }};
    if (replyCount <= 10) return '21e';
    if (replyCount <= 50) return '21e8';
    if (replyCount <= 100) return '21e80';
    return '21e800';
}

const originalMineReplyProof = mineReplyProof;
async function mineReplyProof(threadId, content, staticPattern) {
    const dynamicPattern = getDynamicPoWPattern();
    return originalMineReplyProof(threadId, content, dynamicPattern);
}

function quotePost(postId) {
    const replyForm = document.getElementById('reply-form');
    const textarea = document.getElementById('reply-content');

    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        toggleQuickReply();
    }

    const quote = `>>${postId}\n`;
    const currentValue = textarea.value;

    if (!currentValue.includes(quote)) {
        textarea.value = currentValue + quote;
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    }
}

function expandImage(img) {
    if (img.style.maxWidth === 'none') {
        img.style.maxWidth = '200px';
        img.style.maxHeight = '200px';
    } else {
        img.style.maxWidth = 'none';
        img.style.maxHeight = 'none';
        img.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Initialize theme selector functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeSelector = document.getElementById('theme-selector');
    if (themeSelector) {
        themeSelector.addEventListener('change', function() {
            document.documentElement.setAttribute('data-theme', this.value);
            localStorage.setItem('selected-theme', this.value);
        });
        
        // Load saved theme
        const savedTheme = localStorage.getItem('selected-theme') || 'classic';
        themeSelector.value = savedTheme;
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
});

function toggleBoardDropdown() {
    const dropdown = document.getElementById('board-dropdown');
    if (dropdown.style.display === 'none' || !dropdown.style.display) {
        dropdown.style.display = 'block';
    } else {
        dropdown.style.display = 'none';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('board-dropdown');
    const button = document.getElementById('board-dropdown-btn');
    if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
</script>

<!-- Elegant Bottom Toolbar -->
<div id="elegant-toolbar" style="
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: linear-gradient(135deg, 
        var(--toolbar-bg-start, rgba(68, 75, 110, 0.95)) 0%, 
        var(--toolbar-bg-end, rgba(112, 139, 117, 0.95)) 100%);
    backdrop-filter: blur(20px);
    border-top: 2px solid var(--toolbar-border, rgba(255, 255, 238, 0.2));
    color: var(--toolbar-text, #FFFFEE);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 11px;
    padding: 12px 20px;
    z-index: 9997;
    box-shadow: 0 -4px 20px var(--toolbar-shadow, rgba(0, 0, 0, 0.3));
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
">
    <!-- Left Section: Navigation -->
    <div style="display: flex; align-items: center; gap: 16px;">
        <div style="
            background: var(--nav-item-bg, rgba(255, 255, 238, 0.1));
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--nav-item-border, rgba(255, 255, 238, 0.2));
            display: flex;
            align-items: center;
            gap: 12px;
        ">
            <a href="/catalog" class="nav-link" style="
                color: var(--nav-link-color, #E8FFE8);
                text-decoration: none;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 600;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 4px;
            " onmouseover="this.style.background='var(--nav-link-hover, rgba(255,255,238,0.2))'" onmouseout="this.style.background='transparent'">
                🎯 <span>MC</span>
            </a>
            
            <a href="/library" class="nav-link" style="
                color: var(--nav-link-color, #E8FFE8);
                text-decoration: none;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 600;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 4px;
            " onmouseover="this.style.background='var(--nav-link-hover, rgba(255,255,238,0.2))'" onmouseout="this.style.background='transparent'">
                🖼️ <span>LIB</span>
            </a>
            
            <a href="/{{ $board->code }}" class="nav-link" style="
                color: var(--nav-link-color, #E8FFE8);
                text-decoration: none;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 600;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 4px;
            " onmouseover="this.style.background='var(--nav-link-hover, rgba(255,255,238,0.2))'" onmouseout="this.style.background='transparent'">
                📋 <span>/{{ $board->code }}/</span>
            </a>
            
            @if(session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin)
            <a href="/admin" class="nav-link" style="
                color: var(--nav-link-color, #E8FFE8);
                text-decoration: none;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 600;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 4px;
                background: rgba(255, 68, 68, 0.2);
            " onmouseover="this.style.background='rgba(255, 68, 68, 0.3)'" onmouseout="this.style.background='rgba(255, 68, 68, 0.2)'">
                ⚔️ <span>ADMIN</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Center Section: Logo & Mining Status -->
    <div style="display: flex; align-items: center; gap: 16px;">
        <div style="text-align: center;">
            <a href="/" style="
                text-decoration: none;
                color: var(--logo-color, #FFFFEE);
                font-family: 'Nova Cut', serif;
                font-size: 16px;
                font-weight: 300;
                letter-spacing: 1px;
                text-shadow: 0 2px 4px var(--logo-shadow, rgba(0, 0, 0, 0.5));
                transition: all 0.3s ease;
            " onmouseover="this.style.textShadow='0 0 20px var(--logo-glow, rgba(255,255,238,0.6))'" onmouseout="this.style.textShadow='0 2px 4px var(--logo-shadow, rgba(0,0,0,0.5))'">
                Haichan
            </a>
            <div style="font-size: 8px; color: var(--status-text, #FFFFEE); opacity: 0.8; margin-top: 2px;">
                Thread #{{ $thread->id }}
            </div>
        </div>
    </div>

    <!-- Right Section: Theme & Dashboard -->
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- Theme Selector -->
        <select id="theme-selector" style="
            background: var(--theme-selector-bg, rgba(255, 255, 238, 0.1));
            border: 1px solid var(--theme-selector-border, rgba(255, 255, 238, 0.2));
            color: var(--theme-selector-text, #E8FFE8);
            font-size: 9px;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        ">
            <option value="classic">Classic</option>
            <option value="day">Day</option>
            <option value="cyberpunk">Cyberpunk</option>
            <option value="vaporwave">Vaporwave</option>
            <option value="matrix">Matrix</option>
            <option value="terminal">Terminal</option>
            <option value="synthwave">Synthwave</option>
            <option value="ocean">Ocean</option>
            <option value="volcanic">Volcanic</option>
            <option value="arctic">Arctic</option>
        </select>

        <!-- Mini Dashboard -->
        <div style="
            background: var(--dash-btn-bg, rgba(154, 184, 122, 0.8));
            border: 1px solid var(--dash-btn-border, rgba(255, 255, 238, 0.3));
            color: var(--dash-btn-text, #FFFFEE);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 2px 8px var(--dash-btn-shadow, rgba(154, 184, 122, 0.3));
            transition: all 0.2s ease;
        ">
            <div>⛏️ Mining</div>
            <div style="font-size: 8px; opacity: 0.9;">Thread Focus</div>
        </div>
    </div>
</div>

</body>
</html>