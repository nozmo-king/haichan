@extends('layout')

@section('title', 'Image Library - Haichan')

@section('content')
<div style="margin: 60px auto; max-width: 1200px; background: var(--primary-bg); border: 2px solid var(--border-color); box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header -->
    <div style="background: var(--secondary-bg); padding: 20px 30px; border-bottom: 2px solid var(--border-color); text-align: center;">
        <h1 style="font-size: 24px; color: var(--text-primary); margin: 0 0 8px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            🖼️ Image Library ⛏️
        </h1>
        <p style="color: var(--text-secondary); font-size: 12px; margin: 8px 0 0 0;">Proof-of-work powered image collection</p>
    </div>

    <!-- Controls -->
    <div style="padding: 20px 30px; background: var(--content-bg); border-bottom: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <!-- Sort Controls -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <select id="sort-select" style="padding: 6px 10px; background: var(--primary-bg); border: 1px solid var(--border-color); border-radius: 3px; color: var(--text-primary); font-size: 11px;">
                    <option value="pow">⛏️ By PoW Points</option>
                    <option value="usage">📊 By Usage</option>
                    <option value="recent">🆕 Most Recent</option>
                </select>

                <button id="refresh-btn" style="padding: 6px 12px; background: var(--accent-color); color: var(--content-bg); border: none; border-radius: 3px; font-size: 10px; cursor: pointer;">
                    🔄 Refresh
                </button>
            </div>

            <!-- Stats -->
            <div style="color: var(--text-secondary); font-size: 10px; font-family: monospace;">
                Total Images: <span id="image-count">{{ count($images) }}</span> |
                Total PoW: <span id="total-pow">{{ $images->sum('total_pow_earned') }}</span>
            </div>
        </div>
    </div>

    <!-- Image Grid -->
    <div style="padding: 30px; background: var(--content-bg);">
        @if(count($images) > 0)
            <div id="image-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
                @foreach($images as $image)
            <div class="image-item" 
                 data-mineable="true"
                 data-mine-id="{{ $image->id }}"
                 data-mine-type="image"
                 data-mine-difficulty="21e8"
                 data-mine-content="image_{{ $image->id }}_{{ $image->hash }}"
                 data-id="{{ $image->id }}" 
                 data-pow="{{ $image->total_pow_earned }}" 
                 data-usage="{{ $image->usage_count }}" 
                 data-mine-target="{{ $image->id }}" 
                 data-mine-title="Image {{ $image->id }}" 
                 style="
                background: #FFFFFF;
                border: 2px solid #CCCCCC;
                border-radius: 8px;
                padding: 10px;
                text-align: center;
                transition: all 0.3s ease;
                position: relative;
                cursor: crosshair;
            ">
                <!-- PoW Badge -->
                <div style="
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #CD5C5C;
                    color: white;
                    border-radius: 50%;
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 10px;
                    font-weight: bold;
                    border: 2px solid white;
                ">
                    {{ $image->total_pow_earned }}⚡
                </div>

                <!-- Image -->
                <div style="width: 100%; height: 120px; background: #F8F8F8; border: 1px solid #DDD; border-radius: 4px; margin-bottom: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    @if($image->file_path && file_exists(public_path($image->file_path)))
                        <img src="{{ asset($image->file_path) }}" alt="{{ $image->original_name }}" 
                             data-hash="{{ $image->hash }}" data-image-id="{{ $image->id }}"
                             style="max-width: 100%; max-height: 100%; object-fit: contain;" 
                             onclick="openImageModal({{ $image->id }})">
                    @else
                        <div style="color: #999; font-size: 12px;">Image not found</div>
                    @endif
                </div>

                <!-- Info -->
                <div style="font-size: 10px; color: #666; line-height: 1.4;">
                    <div style="font-weight: bold; color: #3D315B; margin-bottom: 3px;">{{ Str::limit($image->original_name, 20) }}</div>
                    <div style="margin-bottom: 5px;">Usage: {{ $image->usage_count }} times</div>
                    
                    <!-- Clickable Hash -->
                    <div style="background: #f8f8f8; padding: 5px; border-radius: 3px; border: 1px solid #ddd; margin-bottom: 5px;">
                        <div style="font-weight: bold; font-size: 9px; color: #999; margin-bottom: 2px;">HASH (click to copy):</div>
                        <div class="hash-copy" 
                             data-hash="{{ $image->hash }}" 
                             data-image-id="{{ $image->id }}"
                             title="Click to copy full hash and add to form clipboard" 
                             style="cursor: pointer; color: #708B75; font-weight: bold; font-family: monospace; font-size: 8px; word-break: break-all; line-height: 1.2;">
                            {{ $image->hash }}
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div style="display: flex; gap: 5px; justify-content: center; margin-top: 5px;">
                        <button onclick="copyImageData({{ $image->id }}, '{{ $image->hash }}')" 
                                style="background: #708B75; color: white; border: none; padding: 2px 6px; border-radius: 2px; font-size: 8px; cursor: pointer;" 
                                title="Copy hash to clipboard">
                            📋 Copy
                        </button>
                        <button onclick="useInForm({{ $image->id }}, '{{ $image->hash }}')" 
                                style="background: #5a7860; color: white; border: none; padding: 2px 6px; border-radius: 2px; font-size: 8px; cursor: pointer;" 
                                title="Use in thread/reply form">
                            📝 Use
                        </button>
                    </div>
                </div>

            </div>
            @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                <div style="font-size: 48px; margin-bottom: 20px;">📷</div>
                <h3 style="color: var(--text-primary); margin: 0 0 16px 0; font-weight: 300;">No Images in Library</h3>
                <p style="margin: 0 0 24px 0; font-size: 14px;">
                    Upload your first image to start building the proof-of-work image library!
                </p>
                <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; max-width: 400px; margin: 0 auto;">
                    <p style="margin: 0; font-size: 12px; color: var(--text-secondary);">
                        💡 <strong>Tip:</strong> Images with interesting hash patterns earn more PoW points when mined!
                    </p>
                </div>
            </div>
        @endif

        <!-- Upload Section -->
        <div style="margin-top: 40px; padding: 20px; background: #F0F0F0; border: 2px solid #708B75; border-radius: 8px;">
            <h3 style="color: #3D315B; margin: 0 0 15px 0; font-size: 14px;">📤 Upload New Image</h3>

            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <input type="file" id="file-input" accept="image/*" style="padding: 8px; background: white; border: 1px solid #708B75; border-radius: 3px; font-size: 11px;">

                <button id="upload-btn" style="padding: 8px 16px; background: #708B75; color: white; border: none; border-radius: 3px; font-size: 11px; cursor: pointer;">
                    🚀 Upload & Mine
                </button>

                <div id="upload-status" style="font-size: 11px; color: #666; display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 20px; border-radius: 8px; max-width: 80%; max-height: 80%; overflow: auto;">
        <div style="text-align: right; margin-bottom: 10px;">
            <button onclick="closeImageModal()" style="background: #CD5C5C; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">✕</button>
        </div>
        <div id="modal-content"></div>
    </div>
</div>

<script>
// Simple, clean image library functionality
class ImageLibrary {
    constructor() {
        this.images = @json($images);
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Sort functionality
        document.getElementById('sort-select').addEventListener('change', (e) => {
            this.sortImages(e.target.value);
        });

        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', () => {
            window.location.reload();
        });

        // Upload functionality
        document.getElementById('upload-btn').addEventListener('click', () => {
            this.uploadImage();
        });
        
        // Hash copy functionality
        document.querySelectorAll('.hash-copy').forEach(element => {
            element.addEventListener('click', (e) => {
                const hash = e.target.dataset.hash;
                const imageId = e.target.dataset.imageId;
                this.copyImageData(imageId, hash);
            });
        });
    }

    sortImages(sortBy) {
        const grid = document.getElementById('image-grid');
        const items = Array.from(grid.querySelectorAll('.image-item'));

        items.sort((a, b) => {
            switch(sortBy) {
                case 'pow':
                    return parseInt(b.dataset.pow) - parseInt(a.dataset.pow);
                case 'usage':
                    return parseInt(b.dataset.usage) - parseInt(a.dataset.usage);
                case 'recent':
                    return parseInt(b.dataset.id) - parseInt(a.dataset.id);
                default:
                    return 0;
            }
        });

        // Clear and re-append sorted items
        grid.innerHTML = '';
        items.forEach(item => grid.appendChild(item));
    }

    async uploadImage() {
        const fileInput = document.getElementById('file-input');
        const statusDiv = document.getElementById('upload-status');

        if (!fileInput.files.length) {
            alert('Please select a file first');
            return;
        }

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);

        statusDiv.style.display = 'block';
        statusDiv.textContent = 'Uploading...';
        statusDiv.style.color = '#708B75';

        try {
            const response = await fetch('/api/image-library/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                statusDiv.innerHTML = `
                    ✅ Upload successful! 
                    <br><small>🎯 Earned ${result.pow_points} PoW points</small>
                    <br><small>📏 ${result.dimensions} • 💾 ${Math.round(result.file_size/1024)}KB</small>
                `;
                statusDiv.style.color = '#708B75';
                fileInput.value = ''; // Clear file input

                // Refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 2500);
            } else {
                statusDiv.innerHTML = `❌ Upload failed: ${result.message || 'Unknown error'}`;
                statusDiv.style.color = '#CD5C5C';
            }
        } catch (error) {
            console.error('Upload error:', error);
            statusDiv.innerHTML = `❌ Upload failed: Network error`;
            statusDiv.style.color = '#CD5C5C';
        }
    }
}

// Real proof-of-work mining function
async function mineImageProofOfWork(imageId) {
    const targetPattern = '21e8'; // Image mining difficulty
    const challengeData = `image_mine:${imageId}:${Date.now()}`;
    let nonce = 0;
    const maxAttempts = 50000; // Reasonable limit for browser mining

    console.log(`⛏️ Mining PoW for image ${imageId} with pattern ${targetPattern}`);

    for (let attempt = 0; attempt < maxAttempts; attempt++) {
        const testData = `${challengeData}:${nonce}`;
        const encoder = new TextEncoder();
        const hashBuffer = await crypto.subtle.digest('SHA-256', encoder.encode(testData));
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

        if (hashHex.startsWith(targetPattern.toLowerCase())) {
            console.log(`💎 Found valid proof! Hash: ${hashHex}, Nonce: ${nonce}`);
            return { hash: hashHex, nonce, data: testData, valid: true };
        }

        nonce++;

        // Yield control periodically to prevent browser freeze
        if (attempt % 1000 === 0 && attempt > 0) {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
    }

    console.log('⏰ Mining timeout - using simplified calculation');
    return { hash: null, nonce: 0, data: challengeData, valid: false };
}

// Mining functionality
async function mineImage(imageId) {
    try {
        // Show mining indicator
        const imageItem = document.querySelector(`[data-id="${imageId}"]`);
        const originalCursor = imageItem.style.cursor;
        imageItem.style.cursor = 'wait';
        imageItem.style.opacity = '0.7';

        // Mine real proof-of-work
        const proof = await mineImageProofOfWork(imageId);

        const requestData = {
            image_id: imageId,
            hash_rate: proof.valid ? Math.floor(Math.random() * 1000) + 500 : null
        };

        // Include real PoW if found
        if (proof.valid) {
            requestData.proof_hash = proof.hash;
            requestData.nonce = proof.nonce;
        }

        const response = await fetch('/api/image-library/mine', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });

        const result = await response.json();

        // Restore visual state
        imageItem.style.cursor = originalCursor;
        imageItem.style.opacity = '1';

        if (result.success) {
            // Update the PoW badge
            const powBadge = imageItem.querySelector('div[style*="position: absolute"]');
            powBadge.innerHTML = `${result.new_total}⚡`;

            // Enhanced flash animation based on points earned
            let flashColor = '#E8F5E8';
            if (result.jackpot) flashColor = '#FFD700';
            else if (result.points > 100) flashColor = '#FFA500';
            else if (result.points > 50) flashColor = '#90EE90';

            imageItem.style.transition = 'background-color 0.5s ease';
            imageItem.style.backgroundColor = flashColor;
            setTimeout(() => {
                imageItem.style.backgroundColor = '#FFFFFF';
            }, 500);

            // Show success message with enhanced styling
            const statusDiv = document.createElement('div');
            let bgColor = '#708B75';
            if (result.jackpot) bgColor = '#FFD700';
            else if (result.verified_pow) bgColor = '#4CAF50';

            statusDiv.style.cssText = `position: fixed; top: 20px; right: 20px; background: ${bgColor}; color: white; padding: 12px 18px; border-radius: 8px; z-index: 9999; font-size: 14px; font-weight: bold; box-shadow: 0 4px 12px rgba(0,0,0,0.3);`;
            statusDiv.innerHTML = `
                <div>${result.message}</div>
                ${proof.valid ? '<div style="font-size: 10px; margin-top: 4px;">✅ Verified PoW</div>' : ''}
                ${result.jackpot ? '<div style="font-size: 10px; margin-top: 4px;">🎰 JACKPOT!</div>' : ''}
            `;
            document.body.appendChild(statusDiv);

            setTimeout(() => {
                statusDiv.remove();
            }, 4000);

            console.log(`✅ Image ${imageId} mined successfully:`, {
                points: result.points,
                total: result.new_total,
                verified: result.verified_pow,
                jackpot: result.jackpot,
                pattern: result.hash_pattern
            });
        }
    } catch (error) {
        console.error('Mining failed:', error);
        // Restore visual state on error
        const imageItem = document.querySelector(`[data-id="${imageId}"]`);
        if (imageItem) {
            imageItem.style.cursor = 'crosshair';
            imageItem.style.opacity = '1';
        }
    }
}

// Modal functionality
function openImageModal(imageId) {
    const image = @json($images).find(img => img.id === imageId);
    if (!image) return;

    const modal = document.getElementById('image-modal');
    const content = document.getElementById('modal-content');

    content.innerHTML = `
        <div style="text-align: center;">
            <img src="/api/image-library/${image.id}/full" style="max-width: 100%; max-height: 60vh; border-radius: 4px;" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 100%22><text y=%2250%%22 x=%2250%%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22>Image Not Found</text></svg>'">
            <div style="margin-top: 15px; color: #333;">
                <h3 style="margin: 0 0 10px 0;">${image.original_name}</h3>
                <p style="margin: 5px 0; font-size: 12px;">PoW Points: <strong>${image.total_pow_earned}⚡</strong></p>
                <p style="margin: 5px 0; font-size: 12px;">Usage Count: <strong>${image.usage_count}</strong></p>
                <p style="margin: 5px 0; font-size: 12px;">Hash: <strong>${image.hash.substring(0, 16)}...</strong></p>
                <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                    <button onclick="mineImage(${image.id})" style="padding: 8px 16px; background: #708B75; color: white; border: none; border-radius: 3px; cursor: pointer;">⛏️ Mine This Image</button>
                    <button onclick="downloadImage(${image.id})" style="padding: 8px 16px; background: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer;">📥 Download</button>
                </div>
            </div>
        </div>
    `;

    modal.style.display = 'flex';
}

function closeImageModal() {
    document.getElementById('image-modal').style.display = 'none';
}

function downloadImage(imageId) {
    window.open(`/api/image-library/${imageId}/download`, '_blank');
}

// Add some basic animations
const style = document.createElement('style');
style.textContent = `
    @keyframes flash {
        0%, 100% { background-color: #FFFFFF; }
        50% { background-color: #E8F5E8; }
    }

    .image-item:hover {
        transform: scale(1.02);
        border-color: #708B75 !important;
        box-shadow: 0 4px 12px rgba(112, 139, 117, 0.3);
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ImageLibrary();
    
    // Hash copy functionality
    document.querySelectorAll('.hash-copy').forEach(span => {
        span.addEventListener('click', async function(e) {
            e.stopPropagation(); // Don't trigger image mining
            const hash = this.dataset.hash;
            
            try {
                await navigator.clipboard.writeText(hash);
                
                // Show success feedback
                const original = this.textContent;
                this.textContent = 'Copied!';
                this.style.color = '#4CAF50';
                
                setTimeout(() => {
                    this.textContent = original;
                    this.style.color = '#708B75';
                }, 1000);
                
                console.log('Hash copied to clipboard:', hash);
            } catch (err) {
                console.error('Failed to copy hash:', err);
                // Fallback: show hash in alert
                alert('Hash: ' + hash);
            }
        });
    });
    

    // Add click-to-mine functionality
    document.querySelectorAll('.image-item').forEach(item => {
        item.addEventListener('click', async (e) => {
            // Don't mine if clicking on the image itself (that opens modal)
            if (e.target.tagName === 'IMG') return;

            const imageId = parseInt(item.dataset.id);
            if (imageId) {
                await mineImage(imageId);
            }
        });

        // Add visual feedback
        item.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateY(-2px)';
            item.style.boxShadow = '0 6px 20px rgba(112, 139, 117, 0.4)';
        });
        item.addEventListener('mouseleave', () => {
            item.style.transform = 'translateY(0)';
            item.style.boxShadow = 'none';
        });
    });
});

// Enhanced clipboard and form integration functions
async function copyImageData(imageId, hash) {
    try {
        await navigator.clipboard.writeText(hash);
        storeInFormClipboard(imageId, hash);
        showNotification('📋 Hash copied to clipboard!', 'success');
    } catch (error) {
        // Fallback for browsers without clipboard API
        fallbackCopy(hash);
        storeInFormClipboard(imageId, hash);
        showNotification('📋 Hash copied!', 'success');
    }
}

// Use image directly in any form on site
function useInForm(imageId, hash) {
    storeInFormClipboard(imageId, hash);
    
    // Try to find and populate image hash fields on current page
    const hashFields = document.querySelectorAll('input[name="image_hash"], #image_hash, .image-hash-field');
    let fieldFound = false;
    
    hashFields.forEach(field => {
        field.value = hash;
        field.style.backgroundColor = '#e8f5e8';
        field.style.border = '2px solid #28a745';
        
        // Trigger change event to update form validation
        field.dispatchEvent(new Event('change'));
        field.dispatchEvent(new Event('input'));
        fieldFound = true;
    });
    
    if (fieldFound) {
        showNotification('✅ Image hash inserted into form!', 'success');
    } else {
        showNotification('📝 Image ready for use - go to any thread/reply form!', 'info');
    }
}

// Store image data persistently for cross-page usage
function storeInFormClipboard(imageId, hash) {
    const clipboardData = {
        type: 'image',
        id: imageId,
        hash: hash,
        timestamp: Date.now(),
        url: `/api/image-library/${imageId}/full`
    };
    
    // Store in localStorage for persistence across pages
    localStorage.setItem('haichan_image_clipboard', JSON.stringify(clipboardData));
    
    // Also store in sessionStorage for current session
    sessionStorage.setItem('haichan_current_image', JSON.stringify(clipboardData));
    
    console.log('📋 Image stored in form clipboard:', clipboardData);
}

// Fallback copy method for older browsers
function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
}

// Show notification to user
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const colors = {
        success: '#28a745',
        error: '#dc3545', 
        info: '#708B75',
        warning: '#ffc107'
    };
    
    notification.style.cssText = `
        position: fixed; 
        top: 20px; 
        right: 20px; 
        background: ${colors[type]}; 
        color: white; 
        padding: 12px 18px; 
        border-radius: 8px; 
        z-index: 10000; 
        font-size: 14px; 
        font-weight: bold; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    `;
    
    notification.innerHTML = message;
    document.body.appendChild(notification);
    
    // Fade out after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Auto-populate forms with clipboard data on page load (for thread/reply pages)
document.addEventListener('DOMContentLoaded', () => {
    // Only run on pages with image hash fields
    const hashFields = document.querySelectorAll('input[name="image_hash"], #image_hash');
    if (hashFields.length === 0) return;
    
    const clipboardData = localStorage.getItem('haichan_image_clipboard');
    if (clipboardData) {
        try {
            const data = JSON.parse(clipboardData);
            const age = Date.now() - data.timestamp;
            
            // Use clipboard data if less than 1 hour old
            if (age < 3600000) {
                hashFields.forEach(field => {
                    if (!field.value) { // Only fill empty fields
                        field.value = data.hash;
                        field.style.backgroundColor = '#f0f8ff';
                        field.style.border = '1px solid #708B75';
                        
                        // Add helpful indicator
                        const indicator = document.createElement('span');
                        indicator.style.cssText = 'font-size: 10px; color: #666; margin-left: 8px;';
                        indicator.innerHTML = `📋 Ready to use`;
                        field.parentNode.appendChild(indicator);
                        
                        // Validate the hash field if there's a validation function
                        if (typeof handleHashInput === 'function') {
                            handleHashInput.call(field);
                        }
                    }
                });
                
                console.log('📋 Auto-populated form with clipboard image:', data);
            }
        } catch (error) {
            console.error('Error loading clipboard data:', error);
        }
    }
});
</script>
@endsection