@extends('layout')

@section('title', 'Create Thread - ' . $board->code)

@section('content')
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
        <form action="{{ $board->code === 'd' ? '/d/create' : route('board.create.store', $board->code) }}" method="POST" enctype="multipart/form-data" id="thread-form" style="display: flex; flex-direction: column; gap: 20px;">
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
                          style="width: 100%; padding: 12px 16px; border: 2px solid #708B75; border-radius: 8px; font-size: 14px; font-family: inherit; background: #FFFACD; resize: vertical; line-height: 1.5; transition: all 0.3s ease;">{{ old('content') }}</textarea>
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
                    <input type="checkbox" name="anonymous" value="1"
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
            
            <!-- Submit Section -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 20px;">
                <a href="{{ route('forum.board', $board->code) }}" 
                   style="color: #708B75; text-decoration: none; font-weight: 600; padding: 10px 20px; border: 2px solid #708B75; border-radius: 8px; transition: all 0.3s ease;">
                    ← Back to /{{ $board->code }}/
                </a>
                
                <div style="flex: 1; text-align: right;">
                    <div id="mining-status" style="font-size: 14px; margin-bottom: 10px; color: #6B7A6B; min-height: 20px;">
                        Fill in title and content to start mining
                    </div>
                    <button type="submit" id="submit-btn"
                            disabled
                            style="background: #ccc; color: #666; border: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: not-allowed; transition: all 0.3s ease; opacity: 0.6;">
                        ⏳ Fill Content First
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
        miningStatus.innerHTML = '<span style="color: #6B7A6B;">Fill in title and content to start mining</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Fill Content First';
        submitBtn.style.background = '#ccc';
        return;
    }
    
    isMining = true;
    
    try {
        console.log('Starting direct mining...');
        miningStatus.innerHTML = '<span style="color: #ffc107;">⛏️ Mining proof-of-work...</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⛏️ Mining...';
        
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
                submitBtn.innerHTML = '⚡ Create Thread';
                submitBtn.style.background = 'linear-gradient(135deg, #708B75, #5a7860)';
                submitBtn.style.cursor = 'pointer';
                submitBtn.style.opacity = '1';
                
                miningStatus.innerHTML = '<span style="color: #28a745;">✅ Mining complete! Ready to post.</span>';
            }
            
            nonce++;
            
            // Update UI periodically
            if (nonce % 1000 === 0) {
                miningStatus.innerHTML = `<span style="color: #ffc107;">⛏️ Mining... ${nonce} hashes</span>`;
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
    } catch (error) {
        console.error('Mining error:', error);
        miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Mining error - please retry</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '❌ Mining Error';
        submitBtn.style.background = '#dc3545';
        
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
    clearTimeout(miningTimeout);
    currentProof = null; // Reset proof when content changes
    
    const title = titleInput.value.trim();
    const content = contentInput.value.trim();
    
    if (title.length >= 3 && content.length >= 5) {
        miningTimeout = setTimeout(mineDirectly, 1000);
    } else {
        miningStatus.innerHTML = '<span style="color: #6B7A6B;">Fill in title and content to start mining</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Fill Content First';
        submitBtn.style.background = '#ccc';
        submitBtn.style.cursor = 'not-allowed';
        submitBtn.style.opacity = '0.6';
    }
}

titleInput.addEventListener('input', checkAndMine);
contentInput.addEventListener('input', checkAndMine);

// Form validation on submit
form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Always prevent default

    if (!currentProof || !currentProof.hash) {
        alert('Please wait for mining to complete');
        mineDirectly();
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '📤 Creating Thread...';
    form.submit(); // Manually submit the form
});
</script>

<style>
/* Enhanced styling */
.form-group input:focus,
.form-group textarea:focus {
    border-color: #5a7860 !important;
    box-shadow: 0 0 0 3px rgba(112, 139, 117, 0.1) !important;
    outline: none;
}

#submit-btn:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(112, 139, 117, 0.3);
}
</style>
@endsection