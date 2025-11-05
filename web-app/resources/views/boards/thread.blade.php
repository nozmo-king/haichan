@extends('layout', ['boardCode' => $board->code])

@section('title', '/'.$board->code.'/ - '.$board->name)

@section('content')
<div style="text-align: center; margin: 10px 0;">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #FF69B4; text-shadow: -1px -1px 0 #C1418A, 1px -1px 0 #C1418A, -1px 1px 0 #C1418A, 1px 1px 0 #C1418A;">/{{ $board->code }}/ - {{ $board->name }}</h1>
    <p style="font-size: 12px; color: var(--ib-text-muted);">{{ $board->description }}</p>
</div>

<div class="nav-links">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a> |
    <a href="#bottom">Bottom</a>
</div>

<!-- Original Post -->
<div class="post {{ ($thread->user_id && $thread->bitcoinUser && $thread->bitcoinUser->is_admin) ? 'admin-post' : '' }}" style="margin: 20px 0;" 
     data-mineable="true"
     data-mine-id="{{ $thread->id }}" 
     data-mine-type="thread" 
     data-mine-difficulty="21e8"
     data-mine-content="thread_{{ $thread->id }}_{{ $thread->title }}"
     data-thread-id="{{ $thread->id }}" 
     data-board-code="{{ $board->code }}">
    <div class="post-header">
        <span class="post-name">
            @if($thread->user_id && $thread->bitcoinUser)
                <a href="{{ route('user.profile', $thread->user_id) }}" style="color: inherit; text-decoration: none; font-weight: bold;">
                    {{ $thread->getTripcode() }}
                </a>
            @else
                Anonymous
            @endif
        </span>
        @if($board->code === 'pol' && $thread->ip_address)
            {!! \App\Helpers\GeoIP::formatIPWithFlag($thread->ip_address, $board->code) !!}
        @endif
        {{ $thread->created_at->format('m/d/y(D) H:i:s') }}
        <span class="post-no">No.{{ $thread->id }}</span>
        @if($thread->accumulated_points > 0)
            <span id="thread-points-{{ $thread->id }}" style="color: var(--ib-accent); font-weight: bold;">[⚡{{ number_format($thread->accumulated_points, 1) }}]</span>
        @else
            <span id="thread-points-{{ $thread->id }}" style="color: var(--ib-accent); font-weight: bold; display: none;">[⚡0.0]</span>
        @endif
        <button id="quick-reply-btn" class="tui-btn tui-btn-sm" style="margin-left: 10px;">💬 Reply</button>
        @if(session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin)
            <form method="POST" action="{{ $thread->sticky ? route('admin.threads.unpin', $thread->id) : route('admin.threads.pin', $thread->id) }}" style="display: inline-block; margin-left: 8px;">
                @csrf
                <button type="submit" class="tui-btn {{ $thread->sticky ? 'tui-btn-warning' : 'tui-btn-secondary' }} tui-btn-sm">
                    {{ $thread->sticky ? '📌 Unpin' : '📍 Pin' }}
                </button>
            </form>
        @endif
        @if(session('bitcoin_auth_id') && ($thread->user_id === session('bitcoin_auth_id') || (session('bitcoin_auth_user') && (session('bitcoin_auth_user')->is_admin || session('bitcoin_auth_user')->is_moderator))))
            <form method="POST" action="{{ route('threads.delete.user', $thread->id) }}" style="display: inline-block; margin-left: 8px;" class="delete-thread-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="tui-btn tui-btn-danger tui-btn-sm">🗑️ Delete</button>
            </form>
        @endif
    </div>
    
    @if($thread->image_path)
    <div style="float: left; margin: 10px 15px 10px 0;">
        <a href="{{ route('thread.image', $thread->id) }}" target="_blank">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}" data-mine-type="image"
                 style="max-width: 250px; max-height: 250px; border: 1px solid var(--ib-border);">
        </a>
    </div>
    @endif
    
    <div class="post-content">
        <strong>{{ $thread->title ?: 'No Subject' }}</strong><br>
        {!! nl2br(e($thread->content)) !!}
    </div>
    
    <div style="clear: both;"></div>
</div>

<!-- Replies -->
@foreach($posts as $post)
<div class="post {{ ($post->user_id && $post->bitcoinUser && $post->bitcoinUser->is_admin) ? 'admin-post' : '' }}" 
     data-mineable="true"
     data-mine-id="{{ $post->id }}" 
     data-mine-type="post" 
     data-mine-difficulty="21e8"
     data-mine-content="post_{{ $post->id }}_{{ $thread->id }}"
     data-post-id="{{ $post->id }}" 
     data-thread-id="{{ $thread->id }}" 
     data-board-code="{{ $board->code }}">
    <div class="post-header">
        <span class="post-name">
            @if($post->user_id && $post->bitcoinUser)
                <a href="{{ route('user.profile', $post->user_id) }}" style="color: inherit; text-decoration: none; font-weight: bold;">{{ $post->bitcoinUser->getDisplayName() }}</a>
            @else
                Anonymous
            @endif
        </span>
        @if($board->code === 'pol' && $post->ip_address)
            {!! \App\Helpers\GeoIP::formatIPWithFlag($post->ip_address, $board->code) !!}
        @endif
        {{ $post->created_at->format('m/d/y(D) H:i:s') }}
        <span class="post-no clickable-hash" data-post-id="{{ $post->id }}">No.{{ $post->id }}</span>
        @if($post->accumulated_points > 0)
            <span id="post-points-{{ $post->id }}" style="color: var(--ib-accent); font-weight: bold;">[⚡{{ number_format($post->accumulated_points, 1) }}]</span>
        @else
            <span id="post-points-{{ $post->id }}" style="color: var(--ib-accent); font-weight: bold; display: none;">[⚡0.0]</span>
        @endif
        <button class="tui-btn-link quote-btn" data-post-id="{{ $post->id }}" style="font-size: 10px; margin-left: 8px;">» Quote</button>
    </div>
    
    @if($post->image_path)
    <div style="float: left; margin: 10px 15px 10px 0;">
        <a href="{{ route('post.image', $post->id) }}" target="_blank">
            <img src="{{ route('post.image', $post->id) }}" 
                 data-hash="{{ $post->image_hash ?? '' }}" data-post-id="{{ $post->id }}" data-mine-type="image"
                 style="max-width: 200px; max-height: 200px; border: 1px solid var(--ib-border);">
        </a>
    </div>
    @endif
    
    <div class="post-content">
        {!! nl2br(e($post->content)) !!}
    </div>
    
    <div style="clear: both;"></div>
</div>
@endforeach

<!-- Unified Reply Form -->
<div id="reply-form" class="tui-reply-form" style="display: block; margin: 20px 0; background: linear-gradient(135deg, #F5F5DC, #FFFACD); border: 2px solid #708B75; border-radius: 12px; box-shadow: 0 4px 20px rgba(112, 139, 117, 0.2); overflow: hidden;">
    <div class="tui-reply-header" style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="tui-reply-title" style="font-size: 18px; font-weight: 600;">💬 Quick Reply</div>
        <button type="button" class="tui-btn-close reply-close-btn" style="background: none; border: none; color: #F5F5DC; font-size: 20px; cursor: pointer; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s;">×</button>
    </div>
    
    <div class="tui-reply-container">
        <form method="POST" action="/{{ $board->code }}/{{ $thread->id }}/reply" enctype="multipart/form-data" class="unified-post-form">
            @csrf
            
            <!-- Error Display -->
            @if($errors->any())
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    <strong>Validation Errors:</strong>
                    <ul style="margin: 5px 0 0 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="tui-field">
                <label class="tui-label" for="post-name">Name (optional)</label>
                <input type="text" name="name" id="post-name" class="tui-input" placeholder="Anonymous" autocomplete="username">
                <div class="tui-hint">Leave blank for anonymous posting</div>
            </div>
            
            <div class="tui-field">
                <label class="tui-label" for="post-content">Comment</label>
                <textarea name="reply_content" id="post-content" class="tui-textarea" required rows="6" cols="48" 
                          placeholder="Enter your reply... Use >>hash to quote posts"></textarea>
                <div class="tui-hint">Required. Max 5000 characters.</div>
            </div>
            
            <div class="tui-field">
                <label class="tui-label" for="post-file">File Upload (optional)</label>
                <input type="file" name="image" id="post-file" class="tui-file" accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif">
                <div class="tui-hint">Max 25MB. Supports: JPEG, PNG, GIF, WebP, WebM, MP4, SVG, etc.</div>
                
                <!-- Preview -->
                <div id="post-image-preview" class="tui-preview" style="display: none;">
                    <img id="post-preview-img" alt="Post Preview">
                    <div id="post-file-info" class="tui-preview-info"></div>
                </div>
            </div>
            
            <!-- Image Hash Alternative -->
            <x-image-picker 
                name="image_hash" 
                label="OR Choose from Image Library"
                placeholder="Browse library or enter image hash manually..."
                pattern="[a-fA-F0-9]{64}"
                style="font-family: 'Courier New', monospace;"
            />
            
            <!-- Anonymous posting option -->
            <div class="tui-field">
                <label class="tui-checkbox-label">
                    <input type="checkbox" name="post_anonymous" value="1" id="post-anonymous">
                    <span class="tui-checkmark"></span>
                    Post anonymously (even if logged in)
                </label>
            </div>
            
            <!-- PoW fields for mining system -->
            <input type="hidden" name="pow_nonce">
            <input type="hidden" name="pow_hash">
            <input type="hidden" name="pow_challenge_id">
            
            <div class="tui-actions">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="tui-btn tui-btn-primary" id="reply-submit-btn">
                            Post Reply
                        </button>
                        <button type="button" class="tui-btn tui-btn-outline" id="cancel-reply-btn">
                            Cancel
                        </button>
                    </div>
                    <div id="bumpMiningStatus" class="mining-status">
                    <span id="bumpHashrate">0</span> H/s | 
                    <span id="bumpHashes">0</span> hashes | 
                    <span id="bumpPoints">0</span> points
                </div>
                <div id="reply-mining-status" style="font-size: 12px; color: #6B7A6B;">
                        💡 Start typing to begin mining...
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="nav-links">
    <a name="bottom"></a>
    <a href="#">Top</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="/{{ $board->code }}/catalog">Catalog</a>
</div>

<script nonce="{{ app('csp_nonce') }}" data-debug-nonce="{{ app('csp_nonce') }}">
function toggleQuickReply() {
    const replyForm = document.getElementById('reply-form');
    const quickBtn = document.getElementById('quick-reply-btn');

    if (!replyForm || !quickBtn) return;

    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        replyForm.style.display = 'block';
        replyForm.scrollIntoView({ behavior: 'smooth' });
        quickBtn.textContent = '💬 Hide Reply';
        
        // Ensure mining is initialized when form opens
        if (window.replyFormMiner && window.replyFormMiner.setup) {
            setTimeout(() => {
                window.replyFormMiner.setup();
            }, 300);
        }
    } else {
        replyForm.style.display = 'none';
        quickBtn.textContent = '💬 Reply';
    }
}

function previewPostImage(input) {
    const preview = document.getElementById('post-image-preview');
    const img = document.getElementById('post-preview-img');
    const info = document.getElementById('post-file-info');
    const hashInput = document.querySelector('input[name="image_hash"]'); // Updated to work with image picker
    
    if (input.files?.[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = (e) => {
            img.src = e.target.result;
            preview.style.display = 'block';
            info.textContent = `${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`;
        };
        
        reader.readAsDataURL(file);
        if (hashInput) hashInput.value = '';
    } else {
        preview.style.display = 'none';
    }
}


function quotePost(postId) {
    // Open reply form if closed
    const replyForm = document.getElementById('reply-form');
    const quickBtn = document.getElementById('quick-reply-btn');
    
    if (replyForm.style.display === 'none' || !replyForm.style.display) {
        toggleQuickReply();
    }
    
    // Insert quote into textarea
    const contentArea = document.getElementById('post-content');
    if (contentArea) {
        const quote = `>>${postId}\n`;
        const cursorPos = contentArea.selectionStart || contentArea.value.length;
        const textBefore = contentArea.value.substring(0, cursorPos);
        const textAfter = contentArea.value.substring(contentArea.selectionEnd || cursorPos);
        
        contentArea.value = textBefore + quote + textAfter;
        contentArea.selectionStart = contentArea.selectionEnd = cursorPos + quote.length;
        contentArea.focus();
        
        // Show visual feedback
        showQuoteFeedback();
    }
}

function showQuoteFeedback() {
    const feedback = document.createElement('div');
    feedback.textContent = 'Post quoted!';
    feedback.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--ib-accent);
        color: var(--ib-bg);
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
        z-index: 10000;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(feedback);
    setTimeout(() => {
        feedback.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => feedback.remove(), 300);
    }, 2000);
}

// Add CSS for animations
const style = document.createElement('style');
style.setAttribute('nonce', '{{ app("csp_nonce") }}');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .clickable-hash {
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .clickable-hash:hover {
        color: var(--ib-accent) !important;
        text-decoration: underline;
    }
    
    /* Reply button styling */
    #reply-submit-btn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
        background: #ccc !important;
        color: #666 !important;
    }
    
    #reply-submit-btn:enabled {
        opacity: 1 !important;
        cursor: pointer !important;
    }
    
    /* Close button hover effects */
    .reply-close-btn:hover {
        background-color: rgba(255,255,255,0.2) !important;
    }
    
    /* IP flags styling */
    .ip-flag {
        margin: 0 5px;
        font-size: 16px;
        cursor: help;
        transition: transform 0.2s ease;
    }
    
    .ip-flag:hover {
        transform: scale(1.2);
    }
`;
document.head.appendChild(style);

// Reply form mining handled by ReplyFormMiner in simple-pow.js

// Point counter updates - listen for mining proof submissions
document.addEventListener('proofSubmitted', function(e) {
    console.log('🎯 Proof submitted event received on document:', e.detail);
    handlePointUpdate(e.detail);
});

// Also listen on window for mining events
window.addEventListener('mining:complete', function(e) {
    console.log('🎯 Mining complete event received on window:', e.detail);
    // Refresh point displays after a delay to allow backend to update
    setTimeout(() => {
        refreshPointDisplays();
    }, 1500);
});

// Listen for both event targets to ensure we catch all events
window.addEventListener('proofSubmitted', function(e) {
    console.log('🎯 Proof submitted event received on window:', e.detail);
    handlePointUpdate(e.detail);
});

// Handle point updates from various events
function handlePointUpdate(detail) {
    
    // Find the thread point display using the specific ID
    const threadId = {{ $thread->id ?? 0 }};
    const threadPointSpan = document.getElementById(`thread-points-${threadId}`);
    
    console.log('🔍 Looking for element with ID: thread-points-' + threadId);
    console.log('🔍 Found thread point display:', !!threadPointSpan);
    if (threadPointSpan) {
        console.log('📊 Current thread points element:', threadPointSpan.textContent);
        console.log('📊 Element innerHTML:', threadPointSpan.innerHTML);
        console.log('📊 Element display style:', threadPointSpan.style.display);
    } else {
        console.log('❌ Could not find thread points element');
        // Try to find it with a different approach
        const allThreadPoints = document.querySelectorAll('[id^="thread-points-"]');
        console.log('🔍 Found elements with thread-points- prefix:', allThreadPoints.length);
        allThreadPoints.forEach((el, i) => {
            console.log(`  ${i}: ${el.id} - ${el.textContent}`);
        });
    }
    
    // Update thread points if we have new total points
    if (threadPointSpan && detail.total_points) {
        const currentPoints = parseFloat(threadPointSpan.textContent.match(/[\d.]+/)?.[0] || 0);
        const newPoints = parseFloat(detail.total_points);
        console.log('📊 Updating thread points:', currentPoints, '->', newPoints);
        
        // Update the display
        threadPointSpan.innerHTML = `[⚡${newPoints.toFixed(1)}]`;
        threadPointSpan.style.display = 'inline'; // Make sure it's visible
        
        // Mark that we made a manual update
        manualUpdateActive = true;
        
        // Add brief highlight animation
        threadPointSpan.style.transition = 'all 0.3s ease';
        threadPointSpan.style.backgroundColor = 'rgba(0, 169, 165, 0.2)';
        threadPointSpan.style.transform = 'scale(1.1)';
        setTimeout(() => {
            threadPointSpan.style.backgroundColor = '';
            threadPointSpan.style.transform = 'scale(1)';
            // Reset manual update flag after animation
            if (detail.hash === 'test123') {
                manualUpdateActive = false;
            }
        }, 1000);
        
        console.log('✅ Thread points updated successfully');
    }
    
    // Show a notification that points were earned
    if (detail.points) {
        console.log('📢 Showing point notification for:', detail.points, 'points');
        showPointUpdateNotification(detail.points, detail.pattern || 'mining');
    }
    
    // Don't auto-refresh after manual update to avoid overwriting our changes
    // Only refresh if this is a real mining event, not a test
    if (detail.hash && detail.hash !== 'test123') {
        setTimeout(() => {
            refreshPointDisplays();
        }, 1000);
    } else {
    }
}

// Listen for mining updates from mouseover mining
document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 Thread point updater initialized');
    
    // Test if we can find point displays
    const threadPointSpan = document.querySelector('[data-thread-id="{{ $thread->id }}"] span[style*="⚡"]');
    console.log('🔍 Initial thread point span found:', !!threadPointSpan);
    if (threadPointSpan) {
        console.log('📊 Current thread points:', threadPointSpan.textContent);
    }
});

// Function to refresh point displays by fetching latest data
function refreshPointDisplays() {
    if (manualUpdateActive) {
        return;
    }
    
    const threadId = {{ $thread->id ?? 0 }};
    const boardCode = '{{ $board->code }}';
    
    
    // Fetch updated thread data
    fetch(`/api/boards/${boardCode}/thread-order`)
        .then(response => response.json())
        .then(data => {
            console.log('📊 API response:', data);
            const threadData = data.threads.find(t => t.id === threadId);
            console.log('📊 Found thread data:', threadData);
            
            if (threadData) {
                const threadPointSpan = document.getElementById(`thread-points-${threadId}`);
                console.log('📊 Thread point span found:', !!threadPointSpan);
                
                if (threadPointSpan) {
                    const newPoints = parseFloat(threadData.accumulated_points).toFixed(1);
                    console.log('📊 Updating from API with points:', newPoints);
                    
                    threadPointSpan.innerHTML = `[⚡${newPoints}]`;
                    threadPointSpan.style.display = 'inline'; // Make sure it's visible
                    
                    // Add update animation
                    threadPointSpan.style.transition = 'all 0.3s ease';
                    threadPointSpan.style.color = '#00A9A5';
                    threadPointSpan.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        threadPointSpan.style.color = '';
                        threadPointSpan.style.transform = 'scale(1)';
                    }, 1000);
                    
                    console.log('✅ Thread points refreshed from API');
                }
            }
        })
        .catch(error => {
            console.log('❌ Failed to refresh point displays:', error);
        });
}

// Show notification when points are updated
function showPointUpdateNotification(points, pattern) {
    const notification = document.createElement('div');
    notification.innerHTML = `<span style="font-size: 14px;">⚡</span> +${points} points (${pattern})`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #00A9A5, #90C2E7);
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: bold;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 169, 165, 0.3);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Debug: Check if script is loading
console.log('🚀 Thread view script loading...');

// Check template variables
console.log('🔍 Thread ID:', '{{ $thread->id ?? "NULL" }}');
console.log('🔍 Board code:', '{{ $board->code ?? "NULL" }}');

// Simple test function at the top
window.simpleTest = function() {
    console.log('✅ Simple test works!');
};

console.log('🔧 Simple test function defined');

// Track if we've made manual updates to prevent API refresh from overwriting
let manualUpdateActive = false;

// Test function to manually trigger point update (for debugging)
window.testPointUpdate = function() {
    console.log('🧪 Testing point update...');
    const threadId = {{ $thread->id ?? 0 }};
    const threadPointSpan = document.getElementById(`thread-points-${threadId}`);
    console.log('🧪 Thread point span found:', !!threadPointSpan);
    if (threadPointSpan) {
        console.log('🧪 Current content:', threadPointSpan.textContent);
    }
    
    const fakeEventDetail = {
        points: 100,
        total_points: 250.5,
        pattern: '21e8',
        hash: 'test123'
    };
    handlePointUpdate(fakeEventDetail);
    console.log('🧪 Test complete - check for point counter updates and notification');
};

// Direct refresh test
window.testRefresh = function() {
    refreshPointDisplays();
};

// Debug function to compare server vs API values
window.comparePointValues = function() {
    const threadId = {{ $thread->id ?? 0 }};
    const serverPoints = {{ $thread->accumulated_points ?? 0 }};
    
    console.log('🔍 Comparing point values:');
    console.log('  Server-side (Blade):', serverPoints);
    
    fetch(`/api/boards/{{ $board->code }}/thread-order`)
        .then(r => r.json())
        .then(data => {
            const apiThread = data.threads.find(t => t.id === threadId);
            if (apiThread) {
                console.log('  API response:', apiThread.accumulated_points);
                console.log('  Difference:', apiThread.accumulated_points - serverPoints);
                
                if (apiThread.accumulated_points !== serverPoints) {
                    console.log('🚨 MISMATCH DETECTED! This explains the 100->update behavior');
                } else {
                    console.log('✅ Values match - no timing issue');
                }
            } else {
                console.log('❌ Thread not found in API response');
            }
        });
};

// Debug function to track what causes point updates
window.debugPointUpdates = function() {
    const threadId = {{ $thread->id ?? 0 }};
    const threadPointSpan = document.getElementById(`thread-points-${threadId}`);
    
    if (threadPointSpan) {
        console.log('🔍 Current point display state:');
        console.log('  Element:', threadPointSpan);
        console.log('  Current text:', threadPointSpan.textContent);
        console.log('  Current innerHTML:', threadPointSpan.innerHTML);
        console.log('  Display style:', threadPointSpan.style.display);
        
        // Set up a MutationObserver to watch for changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        oldValue: mutation.oldValue,
                        newValue: threadPointSpan.textContent,
                        timestamp: new Date().toISOString()
                    });
                }
            });
        });
        
        observer.observe(threadPointSpan, {
            childList: true,
            subtree: true,
            characterData: true,
            characterDataOldValue: true
        });
        
        
        // Stop watching after 30 seconds
        setTimeout(() => {
            observer.disconnect();
        }, 30000);
    }
};

// Simple point update that persists
window.updateThreadPoints = function(newPoints) {
    const threadId = {{ $thread->id ?? 0 }};
    const threadPointSpan = document.getElementById(`thread-points-${threadId}`);
    
    if (threadPointSpan) {
        console.log('💫 Permanently updating points to:', newPoints);
        threadPointSpan.innerHTML = `[⚡${newPoints}]`;
        threadPointSpan.style.display = 'inline';
        threadPointSpan.style.backgroundColor = 'lime';
        
        // Set permanent flag to prevent reset
        manualUpdateActive = true;
        
        setTimeout(() => {
            threadPointSpan.style.backgroundColor = '';
        }, 2000);
        
        console.log('✅ Points updated permanently');
    }
};

// Monitor point changes
window.monitorPointChanges = function() {
    const threadId = {{ $thread->id ?? 0 }};
    const threadPointSpan = document.getElementById(`thread-points-${threadId}`);
    
    if (threadPointSpan) {
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                }
            });
        });
        
        observer.observe(threadPointSpan, {
            childList: true,
            subtree: true,
            characterData: true
        });
        
        // Also monitor the innerHTML property
        let lastValue = threadPointSpan.innerHTML;
        setInterval(() => {
            if (threadPointSpan.innerHTML !== lastValue) {
                lastValue = threadPointSpan.innerHTML;
            }
        }, 1000);
        
    } else {
        console.log('❌ No thread point span found to monitor');
    }
};

console.log('🧪 Test functions defined');

// Auto-focus content area when reply form is opened
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Setting up event listeners...');
    
    // Quick reply button
    const quickReplyBtn = document.getElementById('quick-reply-btn');
    const cancelReplyBtn = document.getElementById('cancel-reply-btn');
    const closeReplyBtn = document.querySelector('.reply-close-btn');
    
    if (quickReplyBtn) {
        quickReplyBtn.addEventListener('click', toggleQuickReply);
    }
    if (cancelReplyBtn) {
        cancelReplyBtn.addEventListener('click', toggleQuickReply);
    }
    if (closeReplyBtn) {
        closeReplyBtn.addEventListener('click', toggleQuickReply);
    }
    
    // Quote buttons
    document.querySelectorAll('.quote-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            if (postId) {
                quotePost(postId);
            }
        });
    });
    
    // Clickable post numbers
    document.querySelectorAll('.clickable-hash').forEach(span => {
        span.addEventListener('click', function() {
            const postId = this.dataset.postId;
            if (postId) {
                quotePost(postId);
            }
        });
    });
    
    // File input preview
    const fileInput = document.getElementById('post-file');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            previewPostImage(this);
        });
    }
    
    // Delete thread form confirmation
    const deleteForm = document.querySelector('.delete-thread-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            if (!confirm('Delete this thread?')) {
                e.preventDefault();
            }
        });
    }
    
    console.log('✅ Event listeners set up');
    
    // Auto-focus content area when reply form is opened
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'style') {
                const replyForm = document.getElementById('reply-form');
                if (replyForm && replyForm.style.display === 'block') {
                    const contentArea = document.getElementById('post-content');
                    if (contentArea) {
                        setTimeout(() => contentArea.focus(), 100);
                    }
                }
            }
        });
    });
    
    const replyForm = document.getElementById('reply-form');
    if (replyForm) {
        observer.observe(replyForm, { attributes: true, attributeFilter: ['style'] });
    }
});
</script>

@endsection

@section('scripts')
<script nonce="{{ app('csp_nonce') }}" src="{{ asset('build/assets/thread-bumper.js') }}"></script>
<script nonce="{{ app('csp_nonce') }}" src="/js/pow-emergency-fallback.js" defer></script>
@endsection
