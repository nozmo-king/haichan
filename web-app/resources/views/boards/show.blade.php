@extends('layout', ['boardCode' => $board->code])

@section('title', '/'.$board->code.'/ - '.$board->name)

@section('content')
<div style="background: var(--primary-bg); padding: 30px; border-radius: 12px; border: 2px solid var(--border-color); margin-bottom: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); text-align: center;">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #FF69B4; margin: 0 0 10px 0; text-shadow: -1px -1px 0 #C1418A, 1px -1px 0 #C1418A, -1px 1px 0 #C1418A, 1px 1px 0 #C1418A;">
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
<div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 2px solid var(--border-color); margin-bottom: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="font-size: 18px; color: var(--text-primary); margin: 0; font-family: 'Nova Cut', serif;">
            <a href="/{{ $board->code }}/create" style="color: var(--text-primary); text-decoration: none;">🧵 Create New Thread</a>
        </h3>
        <button type="button" style="background: var(--primary-bg); border: 1px solid var(--border-color); color: var(--text-primary); width: 28px; height: 28px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: all 0.3s ease;" id="thread-form-toggle" onclick="toggleThreadForm()">−</button>
    </div>
    
    <div id="thread-form-container" style="padding: 0;">
        <form method="POST" action="/{{ $board->code }}" enctype="multipart/form-data" id="new-thread-form" style="display: flex; flex-direction: column; gap: 15px;">
            @csrf
            
            @if($board->code !== 'i')
            <!-- Subject Field -->
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="display: block; color: var(--text-primary); font-weight: 600; font-size: 13px;">
                    📝 Subject <span style="color: var(--accent-red);">*</span>
                </label>
                <input type="text" name="title" id="thread-title" required maxlength="200" placeholder="Thread subject..."
                       style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                <div style="color: #888; font-size: 11px;">Required • 3-200 characters</div>
            </div>
            
            <!-- Content Field -->
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="display: block; color: var(--text-primary); font-weight: 600; font-size: 13px;">
                    💬 Comment <span style="color: var(--accent-red);">*</span>
                </label>
                <textarea name="content" id="thread-content" required rows="5" placeholder="What's on your mind..."
                          style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
                <div style="color: #888; font-size: 11px;">Required • 5-5000 characters</div>
            </div>
            @else
            <!-- Hidden fields for /i/ board -->
            <input type="hidden" name="title" value="[Image]">
            <input type="hidden" name="content" value=".">
            @endif
            
            <!-- Image Upload -->
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="display: block; color: var(--text-primary); font-weight: 600; font-size: 13px;">
                    🖼️ Image <span style="color: var(--accent-red);">*</span>
                </label>
                <input type="file" name="image" id="thread-image" onchange="previewThreadImage(this)"
                       accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif"
                       style="width: 100%; padding: 10px; border: 1px dashed var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-size: 13px; box-sizing: border-box;">
                <div style="color: #888; font-size: 11px;">Required • Max 25MB • Images, Videos, GIFs</div>
                
                <!-- Image Preview -->
                <div id="thread-image-preview" class="image-preview" style="display: none;">
                    <img id="thread-preview-img" class="preview-image" alt="Thread Image Preview">
                    <div id="thread-file-info" class="file-info"></div>
                    <button type="button" class="remove-preview" onclick="removeThreadPreview()">×</button>
                </div>
            </div>
            
            <!-- Library Hash Alternative -->
            <x-image-picker 
                name="image_hash" 
                label="🔗 OR Use Library Image"
                placeholder="Browse library or enter image hash manually..."
                pattern="[a-fA-F0-9]{64}"
                style="font-family: 'Courier New', monospace;"
            />
            
            <!-- Author Options -->
            <div class="field-group">
                <label class="field-label" for="thread-name">👤 Name (Optional)</label>
                <input type="text" name="name" id="thread-name" class="field-input" 
                       placeholder="Anonymous">
                <div class="field-hint">Leave blank for anonymous posting</div>
            </div>
            
            <!-- Hidden PoW Fields -->
            <input type="hidden" name="pow_nonce" id="thread-pow-nonce" required>
            <input type="hidden" name="pow_hash" id="thread-pow-hash" required>
            <input type="hidden" name="pow_challenge_id" id="thread-pow-challenge-id" required>
            
            <!-- Submit Actions -->
            <div style="display: flex; gap: 10px; padding-top: 10px;">
                <button type="submit" style="padding: 10px 20px; background: var(--accent-green); color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                    📤 Post Thread
                </button>
                <button type="button" onclick="resetThreadForm()" 
                        style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; transition: all 0.3s ease;">
                    🔄 Reset
                </button>
            </div>
        </form>
    </div>
</div>

<hr>

<!-- Threads -->
<div class="threads-list" style="max-width: 900px; margin: 0 auto; padding: 0 15px;">
@forelse($threads as $thread)
<div class="post {{ ($thread->user_id && $thread->bitcoinUser && $thread->bitcoinUser->is_admin) ? 'admin-post' : '' }}" data-thread-id="{{ $thread->id }}" data-mine-type="thread" data-board-code="{{ $board->code }}" style="background: var(--content-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
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
        <span class="pow-indicator" data-value="{{ $thread->accumulated_points ?? 0 }}" 
              style="color: #FFD700; font-weight: bold; margin-left: 8px; font-size: 11px; padding: 2px 6px; background: rgba(255, 215, 0, 0.15); border-radius: 4px; display: inline-block;">
            ⚡{{ number_format($thread->accumulated_points ?? 0, 1) }}
        </span>
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
        {!! nl2br(e($thread->content)) !!}
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
</div>

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

<script nonce="{{ app('csp_nonce') }}">
let currentMiner = null;
let latestProof = null;

// Mouseover mining system is now global in layout.blade.php

async function startMining() {
    if (currentMiner) {
        currentMiner.stop = true;  // Signal existing miner to stop
    }
    
    const btn = document.querySelector('#new-thread-form button[type="submit"]');
    btn.disabled = true;
    btn.textContent = '⛏️ Mining...';
    
    try {
        // Get real challenge from server
        const challengeResponse = await fetch('/api/mining/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                target_type: 'thread',
                action: 'create',
                difficulty: '21e8',
                board_code: '{{ $board->code }}'
            })
        });
        
        if (!challengeResponse.ok) {
            throw new Error('Failed to get mining challenge');
        }
        
        const challengeData = await challengeResponse.json();
        const difficulty = challengeData.difficulty || '21e8';
        const canonicalPayload = challengeData.canonical_payload;
        const payloadString = JSON.stringify(canonicalPayload);
        
        // Create new miner
        currentMiner = {
            stop: false,
            async mine() {
                let nonce = Math.floor(Math.random() * 1000000);
                let hash = '';
                
                while (nonce < 10000000 && !this.stop) {
                    const input = payloadString + ':' + nonce;
                    
                    const encoder = new TextEncoder();
                    const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(input));
                    const hashArray = new Uint8Array(hashBuffer);
                    hash = Array.from(hashArray).map(b => b.toString(16).padStart(2, '0')).join('');
                    
                    // Real difficulty check for 21e8
                    if (hash.toLowerCase().startsWith('21e8')) {
                        latestProof = {
                            nonce: nonce,
                            hash: hash,
                            challenge_id: challengeData.token
                        };
                        
                        btn.disabled = false;
                        btn.textContent = '📤 Post Thread (Mined!)';
                        btn.style.background = '#28a745';
                        
                        document.getElementById('thread-pow-nonce').value = latestProof.nonce;
                        document.getElementById('thread-pow-hash').value = latestProof.hash;
                        document.getElementById('thread-pow-challenge-id').value = latestProof.challenge_id;
                        
                        return true;
                    }
                    
                    nonce++;
                    
                    // Update progress every 50k hashes
                    if (nonce % 50000 === 0 && !this.stop) {
                        btn.textContent = `⛏️ Mining... ${Math.floor(nonce/1000)}k hashes`;
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                }
                
                return false;
            }
        };
        
        currentMiner.mine();
        
    } catch (error) {
        btn.disabled = false;
        btn.textContent = '❌ Mining failed';
        console.error('Mining failed:', error);
    }
}

// Start mining when user starts typing (keep thread creation simple)
@if($board->code !== 'i')
document.getElementById('thread-title').addEventListener('input', function() {
    if (this.value.length >= 3 && !currentMiner) {
        startMining();
    }
});

document.getElementById('thread-content').addEventListener('input', function() {
    if (this.value.length >= 5 && !currentMiner) {
        startMining();
    }
});
@else
// For /i/ board, start mining when image is selected
document.getElementById('thread-image').addEventListener('change', function() {
    if (this.files.length > 0 && !currentMiner) {
        startMining();
    }
});
@endif

// Handle form submission
document.getElementById('new-thread-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!latestProof) {
        alert('Please wait for mining to complete');
        return;
    }
    
    // If we already tried to submit this proof, generate a new one
    if (latestProof.used) {
        alert('Mining a new proof...');
        latestProof = null;
        await startMining();
        if (!latestProof) {
            alert('Failed to generate new proof');
            return;
        }
    }
    
    // Mark this proof as used so we don't reuse it
    latestProof.used = true;
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    const originalColor = btn.style.background;
    
    try {
        btn.disabled = true;
        btn.textContent = '📤 Submitting...';
        btn.style.background = '#6c757d';
        
        // Create FormData from the form
        const formData = new FormData(this);
        
        // Submit via fetch to handle errors properly
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            // Success - redirect to the new thread or board
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                window.location.reload();
            }
        } else {
            // Handle validation errors
            const errorData = await response.text();
            console.error('Form submission failed:', errorData);
            alert('Thread creation failed. Please check your input and try again.');
            
            btn.disabled = false;
            btn.textContent = originalText;
            btn.style.background = originalColor;
        }
    } catch (error) {
        console.error('Form submission error:', error);
        alert('Network error occurred. Please try again.');
        
        btn.disabled = false;
        btn.textContent = originalText;
        btn.style.background = originalColor;
    }
});

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


function resetThreadForm() {
    document.getElementById('new-thread-form').reset();
    document.getElementById('thread-image-preview').style.display = 'none';
    
    // Clear image hash field in the image picker component
    const hashInput = document.querySelector('input[name="image_hash"]');
    if (hashInput) {
        hashInput.value = '';
        hashInput.style.borderColor = '';
    }
    
    // Clear PoW fields
    document.getElementById('thread-pow-nonce').value = '';
    document.getElementById('thread-pow-hash').value = '';
    document.getElementById('thread-pow-challenge-id').value = '';
}

// Initialize form collapse state from localStorage
document.addEventListener('DOMContentLoaded', function() {
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

// Real-time thread reordering and PoW updates
let pollInterval;

function startThreadPolling() {
    updateThreadOrder();
    pollInterval = setInterval(updateThreadOrder, 2000); // Poll every 2 seconds for more responsive updates
}

async function updateThreadOrder() {
    try {
        console.log('🔄 Fetching thread order updates...');
        const response = await fetch('/api/boards/{{ $board->code }}/thread-order');
        const data = await response.json();
        console.log('📊 Thread data received:', data);
        
        data.threads.forEach(thread => {
            const threadEl = document.querySelector(`[data-thread-id="${thread.id}"]`);
            if (threadEl) {
                const powIndicator = threadEl.querySelector('.pow-indicator');
                if (powIndicator) {
                    const oldValue = parseFloat(powIndicator.dataset.value || 0);
                    const newValue = thread.accumulated_points;
                    
                    if (newValue !== oldValue) {
                        console.log(`⚡ Thread ${thread.id} points updated: ${oldValue} -> ${newValue}`);
                        powIndicator.dataset.value = newValue;
                        powIndicator.textContent = `⚡${newValue.toFixed(1)}`;
                        
                        if (newValue > oldValue) {
                            powIndicator.classList.add('pow-increased');
                            setTimeout(() => powIndicator.classList.remove('pow-increased'), 1000);
                        }
                    }
                }
            }
        });
        
        const container = document.querySelector('.threads-list');
        if (container) {
            data.threads.forEach((thread, index) => {
                const threadEl = document.querySelector(`[data-thread-id="${thread.id}"]`);
                if (threadEl && threadEl.parentElement === container) {
                    const currentIndex = Array.from(container.children).indexOf(threadEl);
                    if (currentIndex !== index && currentIndex !== -1) {
                        threadEl.style.transition = 'transform 0.5s ease';
                        if (index === 0) {
                            container.prepend(threadEl);
                        } else {
                            const beforeEl = container.children[index];
                            if (beforeEl && beforeEl !== threadEl) {
                                container.insertBefore(threadEl, beforeEl);
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Thread polling error:', error);
    }
}

document.addEventListener('DOMContentLoaded', startThreadPolling);
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

.pow-indicator {
    transition: all 0.3s ease;
}

.pow-increased {
    animation: powPulse 0.8s ease;
}

@keyframes powPulse {
    0%, 100% { 
        transform: scale(1); 
        background: rgba(255, 215, 0, 0.15);
    }
    50% { 
        transform: scale(1.2); 
        background: rgba(255, 215, 0, 0.4);
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
    }
}
</style>

@endsection
