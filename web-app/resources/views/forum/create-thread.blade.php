@extends('layout')

@section('title', 'Create Thread - ' . $board->code)

@section('content')
<script src="/js/doodle-pow.js"></script>
<div style="max-width: 800px; margin: 40px auto; background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 32px rgba(112, 139, 117, 0.2);">
    <div style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 20px; text-align: center;">
        <h2 style="margin: 0; font-family: 'Nova Cut', serif; font-size: 24px; letter-spacing: 1px;">
            🌱 Create New Thread in /{{ $board->code }}/
        </h2>
        <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 14px;">
            Share your thoughts with proof-of-work ⚡
        </p>
    </div>
    
    <div style="padding: 30px;">
        <form action="{{ $board->code === 'ddl' ? '/ddl/create' : route('board.create.store', $board->code) }}" method="POST" enctype="multipart/form-data" id="thread-form" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            
            <!-- Title Field -->
            <div class="form-group">
                <label for="title" style="display: block; margin-bottom: 8px; color: #3D315B; font-weight: 600; font-size: 16px;">
                    🏷️ Thread Title
                </label>
                <input type="text" name="title" id="title" 
                       required maxlength="255" value="{{ old('title') }}" 
                       placeholder="What's on your mind?"
                       style="width: 100%; padding: 12px 16px; border: 2px solid #708B75; border-radius: 8px; font-size: 16px; font-family: inherit; background: #FFFACD; transition: all 0.3s ease;">
                @error('title')
                    <div style="color: #dc3545; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Content Field -->
            <div class="form-group">
                <label for="content" style="display: block; margin-bottom: 8px; color: #3D315B; font-weight: 600; font-size: 16px;">
                    📝 Your Message
                </label>
                <textarea name="content" id="content" 
                          required maxlength="5000" rows="6" 
                          placeholder="Share your thoughts, ideas, or start a discussion..."
                          style="width: 100%; padding: 12px 16px; border: 2px solid #708B75; border-radius: 8px; font-size: 14px; font-family: 'Courier New', monospace; background: #FFFACD; resize: vertical; line-height: 1.5; transition: all 0.3s ease;">{{ old('content') }}</textarea>
                <div style="font-size: 12px; color: #6B7A6B; margin-top: 4px; text-align: right;">
                    <span id="char-count">0</span> / 5000 characters
                </div>
                @error('content')
                    <div style="color: #dc3545; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Image Upload -->
            <div class="form-group">
                <label for="image" style="display: block; margin-bottom: 8px; color: #3D315B; font-weight: 600; font-size: 16px;">
                    🖼️ Attach Image (optional)
                </label>
                <input type="file" name="image" id="image" 
                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml,image/avif,video/webm,video/mp4"
                       style="width: 100%; padding: 10px; border: 2px solid #708B75; border-radius: 8px; background: #FFFACD; font-size: 14px;">
                <div style="font-size: 12px; color: #6B7A6B; margin-top: 4px;">
                    Max 25MB. Supports: JPEG, PNG, GIF, WebP, AVIF, SVG, WebM, MP4
                </div>
                @error('image')
                    <div style="color: #dc3545; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Image Hash Alternative -->
            <div class="form-group">
                <label for="image_hash" style="display: block; margin-bottom: 8px; color: #3D315B; font-weight: 600; font-size: 16px;">
                    #️⃣ OR Use Image Hash from Library
                </label>
                <input type="text" name="image_hash" id="image_hash" 
                       placeholder="Paste a 64-character hash from the image library..."
                       pattern="[a-fA-F0-9]{64}"
                       style="width: 100%; padding: 12px 16px; border: 2px solid #708B75; border-radius: 8px; font-size: 14px; font-family: 'Courier New', monospace; background: #FFFACD; transition: all 0.3s ease;">
                <div style="font-size: 12px; color: #6B7A6B; margin-top: 4px;">
                    Instead of uploading, use an existing image hash from the <a href="/library" target="_blank" style="color: #708B75; text-decoration: none; font-weight: 600;">Image Library</a>
                </div>
                @error('image_hash')
                    <div style="color: #dc3545; font-size: 14px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Anonymous Posting -->
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; color: #3D315B; font-weight: 600; font-size: 14px; cursor: pointer;">
                    <input type="checkbox" name="post_anonymous" value="1"
                           style="width: 18px; height: 18px; cursor: pointer;">
                    <span>👻 Post Anonymously (hide username)</span>
                </label>
                @if(session('bitcoin_auth_id'))
                <div style="font-size: 12px; color: #6B7A6B; margin-top: 4px; margin-left: 26px;">
                    You're logged in as {{ session('bitcoin_auth_user')->username }}, but can still post anonymously
                </div>
                @endif
            </div>
            
            <!-- Hidden PoW fields -->
            <input type="hidden" name="pow_nonce" id="pow_nonce" required>
            <input type="hidden" name="pow_hash" id="pow_hash" required>
            <input type="hidden" name="pow_challenge_id" id="pow_challenge_id" required>
            
            <!-- Doodle PoW Section -->
            <div class="form-group" style="background: #f0f0f0; padding: 20px; border-radius: 8px; border: 2px dashed #708B75;">
                <label style="display: block; margin-bottom: 12px; color: #3D315B; font-weight: 600; font-size: 16px;">
                    🎨 Doodle Mining (Alternative to Auto-Mining)
                </label>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;">
                        <button type="button" onclick="toggleDoodleMode()" id="doodle-toggle" 
                                style="background: #708B75; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                            Enable Doodle Mining
                        </button>
                        <button type="button" onclick="clearDoodle()" 
                                style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; display: none;" 
                                id="clear-doodle-btn">
                            Clear Canvas
                        </button>
                    </div>
                    
                    <!-- Shape buttons -->
                    <div id="shape-buttons" style="display: none; margin-bottom: 10px;">
                        <label style="font-size: 12px; color: #6B7A6B; margin-bottom: 5px; display: block;">Quick Shapes:</label>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <button type="button" onclick="drawShape('spiral')" class="shape-btn">🌀 Spiral</button>
                            <button type="button" onclick="drawShape('star')" class="shape-btn">⭐ Star</button>
                            <button type="button" onclick="drawShape('smiley')" class="shape-btn">😊 Smiley</button>
                            <button type="button" onclick="drawShape('3dsquare')" class="shape-btn">🎲 3D Box</button>
                            <button type="button" onclick="drawShape('cat')" class="shape-btn">🐱 Cat</button>
                            <button type="button" onclick="drawShape('frog')" class="shape-btn">🐸 Frog</button>
                        </div>
                    </div>
                    
                    <!-- Color palette -->
                    <div id="color-palette" style="display: none; margin-bottom: 10px;">
                        <label style="font-size: 12px; color: #6B7A6B; margin-bottom: 5px; display: block;">Colors:</label>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <div class="color-swatch" onclick="setDoodleColor('#708B75')" style="background: #708B75;" title="Haichan Green"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#000000')" style="background: #000000;" title="Black"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#FF0000')" style="background: #FF0000;" title="Red"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#00FF00')" style="background: #00FF00;" title="Green"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#0000FF')" style="background: #0000FF;" title="Blue"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#FFFF00')" style="background: #FFFF00;" title="Yellow"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#FF00FF')" style="background: #FF00FF;" title="Magenta"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#00FFFF')" style="background: #00FFFF;" title="Cyan"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#FFA500')" style="background: #FFA500;" title="Orange"></div>
                            <div class="color-swatch" onclick="setDoodleColor('#800080')" style="background: #800080;" title="Purple"></div>
                        </div>
                    </div>
                    <canvas id="doodle-canvas" style="display: none;"></canvas>
                    <div id="doodle-status" style="margin-top: 10px; font-size: 14px; color: #6B7A6B;"></div>
                    <div id="entropy-display" style="font-size: 12px; color: #6B7A6B; margin-top: 5px;"></div>
                </div>
                <div style="font-size: 12px; color: #6B7A6B; line-height: 1.4;">
                    • Draw complex patterns to generate entropy<br>
                    • More intricate doodles = faster mining<br>
                    • Doodles are saved with your post
                </div>
            </div>
            
            <!-- Submit Section -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: var(--space-4);">
                <a href="{{ route('forum.board', $board->code) }}" 
                   style="color: var(--ib-link); text-decoration: none; font-weight: var(--font-weight-semibold); padding: var(--space-2) var(--space-4); border: 2px solid var(--ib-border); border-radius: var(--radius-md); transition: var(--default-transition-duration) ease; font-size: var(--text-xs);">
                    ← Back to /{{ $board->code }}/
                </a>
                
                <div style="flex: 1; text-align: right;">
                    <div id="mining-status" style="font-size: var(--text-xs); margin-bottom: var(--space-2); color: var(--ib-text-muted); min-height: 18px; font-weight: var(--font-weight-medium); background: var(--ib-bg); padding: var(--space-2); border-radius: var(--radius-sm); border-left: 3px solid var(--ib-border);">
                        Fill in title and content to start mining
                    </div>
                    <button type="submit" id="submit-btn"
                            disabled
                            style="background: var(--color-gray-400); color: var(--color-gray-600); border: none; padding: var(--space-3) var(--space-6); border-radius: var(--radius-md); font-size: var(--text-sm); font-weight: var(--font-weight-bold); cursor: not-allowed; transition: var(--default-transition-duration) ease; opacity: 0.6;">
                        <span id="submit-emoji">⏳</span> Fill Content First
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Character counter
document.getElementById('content').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
});

// Simple direct mining
const form = document.getElementById('thread-form');
const titleInput = document.getElementById('title');
const contentInput = document.getElementById('content');
const submitBtn = document.getElementById('submit-btn');
const miningStatus = document.getElementById('mining-status');

let isMining = false;
let currentProof = null;

// Direct mining function
async function mineDirectly() {
    if (isMining) return;
    
    const title = titleInput.value.trim();
    const content = contentInput.value.trim();
    
    if (title.length < 3 || content.length < 5) {
        miningStatus.innerHTML = '<span style="color: var(--ib-text-muted);">Fill in title and content to start mining</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span id="submit-emoji">⏳</span> Fill Content First';
        submitBtn.style.background = 'var(--color-gray-400)';
        return;
    }
    
    isMining = true;
    
    try {
        console.log('Starting direct mining...');
        miningStatus.innerHTML = '<span style="color: var(--color-amber-500);">⛏️ Mining proof-of-work...</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span id="submit-emoji">⛏️</span> Mining...';
        
        // Start mining animation - Enhanced strobe sequence
        setTimeout(() => {
            if (window.emojiAnimator) {
                window.emojiAnimator.startAnimation('submit-emoji', ['⛏️', '💎', '⚡', '🔥', '💫', '⭐', '💰', '⛏️'], 120);
            }
        }, 100);
        
        // Get challenge
        const challengeResp = await fetch('/api/mining/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                board_code: '{{ $board->code }}',
                target_type: 'thread',
                action: 'create',
                difficulty: '21e8'
            })
        });
        
        const challenge = await challengeResp.json();
        if (!challenge.success) {
            throw new Error(challenge.error || 'Challenge request failed');
        }
        
        console.log('Challenge received:', challenge.token);
        
        // Mine proof
        const encoder = new TextEncoder();
        const challengeData = JSON.stringify(challenge.canonical_payload);
        let nonce = 0;
        let found = false;
        
        while (!found) {
            const data = challengeData + ':' + nonce;
            const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(data));
            const hashArray = new Uint8Array(hashBuffer);
            const hash = Array.from(hashArray).map(b => b.toString(16).padStart(2, '0')).join('');
            
            if (hash.startsWith('21e8')) {
                found = true;
                currentProof = {
                    nonce: nonce.toString(),
                    hash: hash,
                    challenge_id: challenge.token
                };
                
                console.log('✅ Proof found:', currentProof);
                
                // Fill hidden fields
                document.getElementById('pow_nonce').value = currentProof.nonce;
                document.getElementById('pow_hash').value = currentProof.hash;
                document.getElementById('pow_challenge_id').value = currentProof.challenge_id;
                
                // Enable submit
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span id="submit-emoji">⚡</span> Create Thread';
                submitBtn.style.background = 'linear-gradient(135deg, var(--ib-border), #5a7860)';
                submitBtn.style.cursor = 'pointer';
                submitBtn.style.opacity = '1';
                
                // Success animation - Celebration strobe
                setTimeout(() => {
                    if (window.emojiAnimator) {
                        window.emojiAnimator.startAnimation('submit-emoji', ['⚡', '🎉', '🏆', '⭐', '✨', '💎', '🌟', '🎊'], 150);
                    }
                }, 100);
                
                miningStatus.innerHTML = '<span style="color: var(--color-green-600);">✅ Mining complete! Ready to post.</span>';
            }
            
            nonce++;
            
            // Update UI periodically
            if (nonce % 1000 === 0) {
                miningStatus.innerHTML = `<span style="color: var(--color-amber-500);">⛏️ Mining... ${nonce} hashes</span>`;
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
    } catch (error) {
        console.error('Mining error:', error);
        miningStatus.innerHTML = '<span style="color: var(--color-red-500);">❌ Mining error - please retry</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span id="submit-emoji">❌</span> Mining Error';
        
        // Error animation - Warning strobe
        setTimeout(() => {
            if (window.emojiAnimator) {
                window.emojiAnimator.startAnimation('submit-emoji', ['❌', '💥', '⚠️', '🔥', '💢', '⛔', '❌'], 200);
            }
        }, 100);
        submitBtn.style.background = 'var(--color-red-500)';
        
        // Retry after delay
        setTimeout(() => {
            isMining = false;
            mineDirectly();
        }, 2000);
    }
    
    isMining = false;
}

// Start mining when fields are filled
let miningTimeout;
function checkAndMine() {
    if (doodleMode) return; // Skip auto-mining in doodle mode
    
    clearTimeout(miningTimeout);
    currentProof = null; // Reset proof when content changes
    
    const title = titleInput.value.trim();
    const content = contentInput.value.trim();
    
    if (title.length >= 3 && content.length >= 5) {
        miningTimeout = setTimeout(mineDirectly, 1000);
    } else {
        miningStatus.innerHTML = '<span style="color: var(--ib-text-muted);">Fill in title and content to start mining</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span id="submit-emoji">⏳</span> Fill Content First';
        submitBtn.style.background = 'var(--color-gray-400)';
        submitBtn.style.cursor = 'not-allowed';
        submitBtn.style.opacity = '0.6';
    }
}

titleInput.addEventListener('input', checkAndMine);
contentInput.addEventListener('input', checkAndMine);

// Doodle mining integration
let doodlePoW = null;
let doodleMode = false;

function toggleDoodleMode() {
    doodleMode = !doodleMode;
    const canvas = document.getElementById('doodle-canvas');
    const toggleBtn = document.getElementById('doodle-toggle');
    const clearBtn = document.getElementById('clear-doodle-btn');
    const shapeButtons = document.getElementById('shape-buttons');
    const colorPalette = document.getElementById('color-palette');
    
    if (doodleMode) {
        canvas.style.display = 'block';
        clearBtn.style.display = 'inline-block';
        shapeButtons.style.display = 'block';
        colorPalette.style.display = 'block';
        toggleBtn.textContent = 'Disable Doodle Mining';
        toggleBtn.style.background = 'var(--color-red-500)';
        
        // Initialize doodle PoW
        if (!doodlePoW) {
            console.log('Initializing DoodlePoW...');
            doodlePoW = new DoodlePoW('doodle-canvas', {
                difficulty: '21e8',
                onProofFound: (proof) => {
                    currentProof = proof;
                    document.getElementById('pow_nonce').value = proof.nonce;
                    document.getElementById('pow_hash').value = proof.hash;
                    document.getElementById('pow_challenge_id').value = proof.challenge_id;
                    
                    // Add doodle data to form
                    const doodleInput = document.createElement('input');
                    doodleInput.type = 'hidden';
                    doodleInput.name = 'doodle_data';
                    doodleInput.value = JSON.stringify(proof.doodle_data);
                    form.appendChild(doodleInput);
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span id="submit-emoji">🎨</span> Create with Doodle';
                    
                    // Doodle success animation - Artistic celebration
                    setTimeout(() => {
                        if (window.emojiAnimator) {
                            window.emojiAnimator.startAnimation('submit-emoji', ['🎨', '✨', '🌟', '🎭', '🖌️', '💫', '🏆', '🎨'], 180);
                        }
                    }, 100);
                    submitBtn.style.background = 'linear-gradient(135deg, var(--ib-border), #5a7860)';
                    submitBtn.style.cursor = 'pointer';
                    submitBtn.style.opacity = '1';
                    
                    miningStatus.innerHTML = '<span style="color: var(--color-green-600);">✅ Doodle mining complete!</span>';
                },
                onEntropyUpdate: (entropy) => {
                    document.getElementById('entropy-display').textContent = `Entropy: ${entropy} points`;
                    if (entropy > 50) {
                        document.getElementById('doodle-status').innerHTML = '<span style="color: var(--color-amber-500);">⛏️ Mining with your doodle...</span>';
                    }
                }
            });
        }
        
        // Stop regular mining
        isMining = false;
        clearTimeout(miningTimeout);
    } else {
        canvas.style.display = 'none';
        clearBtn.style.display = 'none';
        shapeButtons.style.display = 'none';
        colorPalette.style.display = 'none';
        toggleBtn.textContent = 'Enable Doodle Mining';
        toggleBtn.style.background = 'var(--ib-border)';
        
        if (doodlePoW) {
            doodlePoW.stopMining();
        }
        
        // Resume regular mining if fields are filled
        checkAndMine();
    }
}

function drawShape(shape) {
    if (doodlePoW) {
        console.log('Drawing shape:', shape);
        switch(shape) {
            case 'spiral': doodlePoW.drawSpiral(); break;
            case 'star': doodlePoW.drawStar(); break;
            case 'smiley': doodlePoW.drawSmiley(); break;
            case '3dsquare': doodlePoW.draw3DSquare(); break;
            case 'cat': doodlePoW.drawCat(); break;
            case 'frog': doodlePoW.drawFrog(); break;
        }
    }
}

function setDoodleColor(color) {
    if (doodlePoW) {
        doodlePoW.setColor(color);
        // Update all color swatches to show selection
        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.style.border = swatch.style.background === color ? '3px solid #000' : '1px solid #ccc';
        });
    }
}

function clearDoodle() {
    if (doodlePoW) {
        doodlePoW.clearCanvas();
        document.getElementById('doodle-status').textContent = '';
        document.getElementById('entropy-display').textContent = '';
        currentProof = null;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span id="submit-emoji">⏳</span> Draw to Mine';
        submitBtn.style.background = 'var(--color-gray-400)';
    }
}

// Form validation on submit
form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Always prevent default

    if (!currentProof || !currentProof.hash) {
        alert('Please wait for mining to complete');
        if (!doodleMode) {
            mineDirectly();
        }
        return;
    }
    
    // Refresh CSRF token before submitting
    try {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        const currentToken = metaTag.content;
        
        // Update form's CSRF token
        const csrfInput = form.querySelector('input[name="_token"]');
        if (csrfInput) {
            csrfInput.value = currentToken;
        }
    } catch (error) {
        console.error('CSRF refresh error:', error);
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span id="submit-emoji">📤</span> Creating Thread...';
    
    // Final submission animation - Launch sequence
    if (window.emojiAnimator) {
        window.emojiAnimator.startAnimation('submit-emoji', ['📤', '🚀', '✈️', '🎯', '⭐', '💫', '📤'], 130);
    }
    form.submit(); // Manually submit the form
});
</script>

<style>
/* Canvas cursor styles */
#doodle-canvas {
    cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="12" cy="12" r="2" fill="%23708B75"/><circle cx="12" cy="12" r="4" fill="none" stroke="%23708B75" stroke-width="1"/></svg>') 12 12, crosshair !important;
}

/* Shape button styles */
.shape-btn {
    background: var(--ib-bg);
    border: 1px solid var(--ib-border);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    cursor: pointer;
    transition: var(--default-transition-duration) ease;
}

.shape-btn:hover {
    background: var(--ib-panel);
    border-color: var(--ib-accent);
    transform: scale(1.05);
}

/* Color swatch styles */
.color-swatch {
    width: 24px;
    height: 24px;
    border: 1px solid var(--ib-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--default-transition-duration) ease;
}

.color-swatch:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-md);
}

/* Enhanced styling */
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--ib-accent) !important;
    box-shadow: 0 0 0 3px rgba(154, 184, 122, 0.1) !important;
    outline: none;
}

#submit-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-lg);
}
</style>
@endsection