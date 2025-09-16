@extends('layout')

@section('title', 'Image Library - Haichan')

@section('content')
<!-- Japanese Web Aesthetic Container with Homepage Style -->
<div style="margin: 60px auto; max-width: 900px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header with proper color scheme -->
    <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 20px 30px; border-bottom: 2px solid #708B75; position: relative; text-align: center;">
        <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px;">
            β版
        </div>

        <h1 style="font-size: 24px; color: #3D315B; margin: 0 0 8px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            <span class="strobing-emoji" style="font-size: 22px; color: #B87333;">🖼️</span>
            <span class="fade-text" data-en="Image Library" data-jp="画像ライブラリ">Image Library</span>
            <span class="strobing-emoji" style="font-size: 22px; color: #CD5C5C;">⛏️</span>
        </h1>

        <div style="width: 80px; height: 2px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 10px auto;"></div>

        <p style="color: #708B75; font-size: 12px; line-height: 1.5; margin: 8px 0 0 0; font-weight: 400;">Proof-of-work sorted image library</p>
    </div>

    <!-- Content area -->
    <div style="padding: 30px; background: #FFFFEE;">
        <!-- Library Controls -->
        <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <select id="library-sort" style="padding: 6px 10px; background: #F5F5DC; border: 1px solid #708B75; border-radius: 3px; color: #3D315B; font-size: 11px;">
                    <option value="shifting">🔄 Ever-Shifting</option>
                    <option value="pow">⛏️ By PoW</option>
                    <option value="usage">📊 By Usage</option>
                    <option value="recent">🆕 Recent</option>
                </select>

                <button id="auto-dither-toggle" style="padding: 6px 12px; background: #708B75; color: #FFFFEE; border: none; border-radius: 3px; font-size: 10px; cursor: pointer;">
                    🎨 Auto-Dither: <span id="dither-status">OFF</span>
                </button>
            </div>

            <div style="color: #708B75; font-size: 10px; font-family: monospace;">
                Images: <span id="total-images">{{ count($images) }}</span> |
                Total PoW: <span id="total-pow">{{ $images->sum('total_pow_earned') }}</span> |
                Usage: <span id="total-usage">{{ $images->sum('usage_count') }}</span>
            </div>
        </div>

        <!-- Ever-Shifting Image Grid -->
        <div id="image-library-grid" style="
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            min-height: 400px;
            padding: 20px;
            background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 50%, #FFFFEE 100%);
            border: 2px solid #708B75;
            border-radius: 8px;
            overflow: hidden;
        ">
            @foreach($images as $image)
            <div class="library-image"
                 data-image-id="{{ $image->id }}"
                 data-pow="{{ $image->total_pow_earned }}"
                 data-usage="{{ $image->usage_count }}"
                 data-filename="{{ $image->original_name }}"
                 data-hash="{{ $image->hash }}"
                 style="
                    position: relative;
                    background: #FFFFEE;
                    border: 1px solid #708B75;
                    border-radius: 6px;
                    padding: 8px;
                    cursor: pointer;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    opacity: 0.8;
                    transform: scale(0.95);
                 "
                 onmouseover="this.style.transform='scale(1.05) rotate({{ rand(-2, 2) }}deg)'; this.style.opacity='1'; this.style.zIndex='100'; this.style.boxShadow='0 8px 25px rgba(68, 75, 110, 0.4)';"
                 onmouseout="this.style.transform='scale(0.95) rotate(0deg)'; this.style.opacity='0.8'; this.style.zIndex='1'; this.style.boxShadow='none';">

                <!-- PoW Badge -->
                <div style="
                    position: absolute;
                    top: 3px;
                    right: 3px;
                    background: {{ $image->total_pow_earned > 100 ? '#CD5C5C' : ($image->total_pow_earned > 50 ? '#B87333' : '#708B75') }};
                    color: #FFFFEE;
                    padding: 2px 5px;
                    border-radius: 10px;
                    font-size: 7px;
                    font-weight: bold;
                    z-index: 2;
                ">⛏️{{ $image->total_pow_earned }}</div>

                <!-- Image -->
                <div style="
                    width: 100%;
                    height: 80px;
                    background-image: url('{{ $image->getImageUrl() }}');
                    background-size: cover;
                    background-position: center;
                    border-radius: 3px;
                    margin-bottom: 5px;
                " onclick="openImageModal({{ $image->id }})"></div>

                <!-- Info -->
                <div style="font-size: 8px; color: #708B75; text-align: center; line-height: 1.2;">
                    <div style="font-weight: bold; margin-bottom: 2px;">{{ Str::limit($image->original_name, 15) }}</div>
                    <div>Used: <span style="color: #B87333;">{{ $image->usage_count }}×</span></div>
                    <div>Size: {{ number_format($image->file_size / 1024, 1) }}KB</div>
                </div>

                <!-- Mining Button -->
                <button class="mine-image-btn" data-image-id="{{ $image->id }}" style="
                    width: 100%;
                    margin-top: 5px;
                    padding: 3px 6px;
                    background: linear-gradient(135deg, #708B75, #9AB87A);
                    color: #FFFFEE;
                    border: none;
                    border-radius: 3px;
                    font-size: 7px;
                    cursor: pointer;
                    opacity: 0.7;
                    transition: opacity 0.2s;
                " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    MINE THIS
                </button>
            </div>
            @endforeach
        </div>

        <!-- Upload New Image Section -->
        <div style="margin-top: 30px; padding: 20px; background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px;">
            <h3 style="color: #3D315B; margin: 0 0 15px 0; font-size: 14px;">📤 Upload New Image</h3>

            <form id="upload-form" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                @csrf
                <input type="file" id="image-file" name="image" accept="image/*" style="
                    padding: 6px 10px;
                    background: #FFFFEE;
                    border: 1px solid #708B75;
                    border-radius: 3px;
                    font-size: 11px;
                    flex: 1;
                    min-width: 200px;
                ">

                <label style="display: flex; align-items: center; gap: 5px; color: #708B75; font-size: 11px;">
                    <input type="checkbox" id="auto-dither-upload" style="accent-color: #708B75;">
                    Auto-dither
                </label>

                <button type="button" id="upload-btn" style="
                    padding: 8px 16px;
                    background: linear-gradient(135deg, #708B75, #9AB87A);
                    color: #FFFFEE;
                    border: none;
                    border-radius: 3px;
                    font-size: 11px;
                    cursor: pointer;
                    font-weight: 500;
                ">Upload & Mine</button>
            </form>

            <div id="upload-status" style="margin-top: 10px; font-size: 10px; color: #708B75; display: none;"></div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
" onclick="closeImageModal()">
    <div style="
        max-width: 90%;
        max-height: 90%;
        background: #F5F5DC;
        border: 3px solid #708B75;
        border-radius: 8px;
        padding: 20px;
        position: relative;
    " onclick="event.stopPropagation()">
        <button onclick="closeImageModal()" style="
            position: absolute;
            top: 10px;
            right: 10px;
            background: #708B75;
            color: #FFFFEE;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
        ">×</button>

        <div id="modal-content"></div>
    </div>
</div>

<script>
// Ever-shifting image library system
class ImageLibrarySystem {
    constructor() {
        this.autoDitherEnabled = false;
        this.currentSort = 'shifting';
        this.images = @json($images);
        this.initializeSystem();
    }

    initializeSystem() {
        this.setupEventListeners();
        this.startShiftingAnimation();
        this.setupMiningButtons();
    }

    setupEventListeners() {
        // Sort selector
        document.getElementById('library-sort').addEventListener('change', (e) => {
            this.currentSort = e.target.value;
            this.reorganizeImages();
        });

        // Auto-dither toggle
        document.getElementById('auto-dither-toggle').addEventListener('click', () => {
            this.autoDitherEnabled = !this.autoDitherEnabled;
            document.getElementById('dither-status').textContent = this.autoDitherEnabled ? 'ON' : 'OFF';
        });

        // Upload form
        document.getElementById('upload-btn').addEventListener('click', () => {
            this.uploadImage();
        });
    }

    startShiftingAnimation() {
        // Disabled automatic shifting - images stay in place
        // Real-time PoW sorting happens only on user action or new uploads
    }

    performShift() {
        const grid = document.getElementById('image-library-grid');
        const images = Array.from(grid.children);

        // Randomly select 2-4 images to swap positions
        const swapCount = Math.floor(Math.random() * 3) + 2;

        for (let i = 0; i < swapCount; i++) {
            const idx1 = Math.floor(Math.random() * images.length);
            const idx2 = Math.floor(Math.random() * images.length);

            if (idx1 !== idx2) {
                // Create shifting animation
                images[idx1].style.transition = 'all 1s cubic-bezier(0.4, 0, 0.2, 1)';
                images[idx2].style.transition = 'all 1s cubic-bezier(0.4, 0, 0.2, 1)';

                // Swap elements
                const temp = images[idx1].cloneNode(true);
                images[idx1].replaceWith(images[idx2]);
                images[idx2].replaceWith(temp);

                // Re-setup event listeners for new elements
                this.setupMiningButtons();
            }
        }
    }

    reorganizeImages() {
        const grid = document.getElementById('image-library-grid');
        const imageElements = Array.from(grid.children);

        let sortedElements;

        switch (this.currentSort) {
            case 'pow':
                sortedElements = imageElements.sort((a, b) =>
                    parseInt(b.dataset.pow) - parseInt(a.dataset.pow)
                );
                break;
            case 'usage':
                sortedElements = imageElements.sort((a, b) =>
                    parseInt(b.dataset.usage) - parseInt(a.dataset.usage)
                );
                break;
            case 'recent':
                sortedElements = imageElements.reverse();
                break;
            default: // shifting
                sortedElements = imageElements.sort(() => Math.random() - 0.5);
        }

        // Animate reorganization
        sortedElements.forEach((element, index) => {
            element.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
            element.style.transform = 'scale(0.8) rotate(5deg)';

            setTimeout(() => {
                grid.appendChild(element);
                element.style.transform = 'scale(0.95) rotate(0deg)';
            }, index * 50);
        });
    }

    setupMiningButtons() {
        document.querySelectorAll('.mine-image-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const imageId = btn.dataset.imageId;
                this.mineImage(imageId);
            });
        });
    }

    async mineImage(imageId) {
        const btn = document.querySelector(`.mine-image-btn[data-image-id="${imageId}"]`);
        const imageElement = btn.parentElement;
        const originalText = btn.textContent;

        // Add mining visual effects
        imageElement.classList.add('mining-active');
        btn.textContent = 'MINING...';
        btn.disabled = true;
        btn.style.background = 'linear-gradient(45deg, #FFD700, #FFA500)';

        // Simulate mining process with more realistic timing
        const miningTime = Math.random() * 4000 + 1500; // 1.5-5.5 seconds
        const hashRate = Math.floor(Math.random() * 1500) + 500; // 500-2000 H/s

        // Show mining progress
        let progress = 0;
        const miningInterval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress < 100) {
                btn.textContent = `MINING... ${Math.floor(progress)}%`;
            }
        }, 200);

        setTimeout(async () => {
            clearInterval(miningInterval);
            btn.textContent = 'PROCESSING...';

            // Try to get proof-of-work if simple mining is available
            let proofData = {};
            if (window.simpleMiner && typeof window.simpleMiner.getLatestProof === 'function') {
                const proof = window.simpleMiner.getLatestProof();
                if (proof && proof.hash && proof.nonce) {
                    proofData = {
                        proof_hash: proof.hash,
                        nonce: proof.nonce
                    };
                }
            }

            try {
                const response = await fetch('/api/image-library/mine', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        image_id: imageId,
                        hash_rate: hashRate,
                        ...proofData
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Remove mining effects
                    imageElement.classList.remove('mining-active');

                    // Show exciting message based on what happened!
                    let buttonColor = '#4CAF50';
                    let showTime = 3000;
                    let specialClass = '';

                    if (result.jackpot) {
                        btn.textContent = '🎰 MEGA JACKPOT!';
                        buttonColor = '#FFD700';
                        showTime = 5000;
                        imageElement.classList.add('jackpot-win');
                        // Add jackpot particles effect
                        this.showJackpotEffect(imageElement);
                    } else if (result.hash_pattern.startsWith('000')) {
                        btn.textContent = '💎 LEGENDARY!';
                        buttonColor = '#8A2BE2';
                        showTime = 4000;
                        specialClass = 'hash-legendary';
                    } else if (result.hash_pattern.startsWith('666')) {
                        btn.textContent = '😈 CURSED!';
                        buttonColor = '#DC143C';
                        showTime = 4000;
                        specialClass = 'hash-cursed';
                    } else if (result.hash_pattern.startsWith('dead')) {
                        btn.textContent = '💀 DEATH!';
                        buttonColor = '#000000';
                        btn.style.color = '#FFFFFF';
                        showTime = 4000;
                        specialClass = 'hash-death';
                    } else if (result.hash_pattern.startsWith('21e8')) {
                        btn.textContent = '🚀 BONUS!';
                        buttonColor = '#FF6347';
                        showTime = 3500;
                        specialClass = 'hash-bonus';
                    } else {
                        btn.textContent = `+${result.points} PoW!`;
                    }

                    // Apply special styling
                    if (specialClass) {
                        imageElement.classList.add(specialClass);
                        setTimeout(() => imageElement.classList.remove(specialClass), showTime);
                    }

                    btn.style.background = buttonColor;
                    btn.style.fontSize = '8px';
                    btn.style.fontWeight = 'bold';

                    // Show floating points animation
                    this.showFloatingPoints(imageElement, result.points, result.message);

                    // Show verified PoW indicator if this was verified
                    if (result.verified_pow) {
                        const verifyIcon = document.createElement('div');
                        verifyIcon.textContent = '✅ VERIFIED';
                        verifyIcon.style.position = 'absolute';
                        verifyIcon.style.top = '5px';
                        verifyIcon.style.left = '5px';
                        verifyIcon.style.fontSize = '8px';
                        verifyIcon.style.background = '#4CAF50';
                        verifyIcon.style.color = 'white';
                        verifyIcon.style.padding = '2px 4px';
                        verifyIcon.style.borderRadius = '2px';
                        verifyIcon.style.zIndex = '999';
                        imageElement.appendChild(verifyIcon);
                        setTimeout(() => verifyIcon.remove(), showTime);
                    }

                    // Update PoW badge with animation
                    const badge = imageElement.querySelector('div[style*="position: absolute"]');
                    if (badge) {
                        badge.style.animation = 'pulse 0.5s ease-in-out';
                        badge.textContent = `⛏️${result.new_total}`;
                        badge.style.background = buttonColor;
                    }

                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.background = 'linear-gradient(135deg, #708B75, #9AB87A)';
                        btn.style.fontSize = '7px';
                        btn.style.fontWeight = 'normal';
                        btn.style.color = '';
                        btn.disabled = false;
                        imageElement.classList.remove('jackpot-win');
                        if (badge) {
                            badge.style.animation = '';
                            badge.style.background = result.new_total > 100 ? '#CD5C5C' : (result.new_total > 50 ? '#B87333' : '#708B75');
                        }
                    }, showTime);
                }
            } catch (error) {
                console.error('Mining failed:', error);
                imageElement.classList.remove('mining-active');
                btn.textContent = 'FAILED!';
                btn.style.background = '#DC143C';
                btn.style.color = '#FFFFFF';

                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = 'linear-gradient(135deg, #708B75, #9AB87A)';
                    btn.style.color = '';
                    btn.disabled = false;
                }, 2000);
            }
        }, miningTime);
    }

    showJackpotEffect(imageElement) {
        // Create golden particle explosion
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.textContent = ['💰', '💎', '🎰', '⭐', '✨'][Math.floor(Math.random() * 5)];
            particle.style.position = 'absolute';
            particle.style.left = '50%';
            particle.style.top = '50%';
            particle.style.fontSize = '12px';
            particle.style.zIndex = '1000';
            particle.style.pointerEvents = 'none';
            particle.style.transform = 'translate(-50%, -50%)';

            imageElement.appendChild(particle);

            // Animate particles
            const angle = (i / 15) * 2 * Math.PI;
            const distance = 80 + Math.random() * 40;
            const x = Math.cos(angle) * distance;
            const y = Math.sin(angle) * distance;

            particle.animate([
                { transform: 'translate(-50%, -50%) scale(0)', opacity: 1 },
                { transform: `translate(${x}px, ${y}px) scale(1)`, opacity: 1 },
                { transform: `translate(${x * 1.5}px, ${y * 1.5}px) scale(0.5)`, opacity: 0 }
            ], {
                duration: 2000,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
            }).onfinish = () => particle.remove();
        }
    }

    showFloatingPoints(imageElement, points, message) {
        const floatingText = document.createElement('div');
        floatingText.textContent = message || `+${points} PoW!`;
        floatingText.style.position = 'absolute';
        floatingText.style.left = '50%';
        floatingText.style.top = '20%';
        floatingText.style.transform = 'translateX(-50%)';
        floatingText.style.fontSize = '10px';
        floatingText.style.fontWeight = 'bold';
        floatingText.style.color = '#FFD700';
        floatingText.style.zIndex = '999';
        floatingText.style.pointerEvents = 'none';
        floatingText.style.textShadow = '1px 1px 2px rgba(0,0,0,0.8)';

        imageElement.appendChild(floatingText);

        // Animate floating up
        floatingText.animate([
            { transform: 'translateX(-50%) translateY(0px)', opacity: 1, fontSize: '10px' },
            { transform: 'translateX(-50%) translateY(-30px)', opacity: 1, fontSize: '12px' },
            { transform: 'translateX(-50%) translateY(-60px)', opacity: 0, fontSize: '8px' }
        ], {
            duration: 2500,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        }).onfinish = () => floatingText.remove();
    }

    addNewImageToLibrary(imageData) {
        const grid = document.getElementById('image-library-grid');

        // Create new image element HTML
        const imageHTML = `
            <div class="library-image" style="position: relative; cursor: pointer; border-radius: 5px; overflow: hidden; transition: all 0.3s ease;"
                 data-image-id="${imageData.id}" data-pow="${imageData.total_pow_earned}" data-usage="${imageData.usage_count}"
                 data-filename="${imageData.filename}" data-hash="${imageData.hash}"
                 onclick="openImageModal('${imageData.id}')">

                <img src="/api/image-library/${imageData.id}/thumb" style="width: 100%; height: 150px; object-fit: cover;">

                <!-- PoW Badge -->
                <div style="position: absolute; top: 5px; right: 5px; background: #708B75; color: #FFFFEE; padding: 2px 6px;
                           font-size: 8px; border-radius: 10px; font-weight: bold; min-width: 20px; text-align: center;">
                    ⛏️${imageData.total_pow_earned}
                </div>

                <!-- Mining Button -->
                <button class="mine-image-btn" data-image-id="${imageData.id}"
                        style="position: absolute; bottom: 5px; left: 5px; background: linear-gradient(135deg, #708B75, #9AB87A);
                               color: #FFFFEE; border: none; padding: 4px 8px; font-size: 7px; border-radius: 3px;
                               cursor: pointer; font-weight: bold; transition: all 0.2s ease;"
                        onclick="event.stopPropagation();">MINE ⛏️</button>

                <!-- File info -->
                <div style="position: absolute; bottom: 5px; right: 5px; background: rgba(0,0,0,0.7); color: #FFFFEE;
                           padding: 2px 4px; font-size: 6px; border-radius: 2px;">
                    ${imageData.usage_count} uses
                </div>
            </div>
        `;

        // Insert at the beginning (most recent first)
        grid.insertAdjacentHTML('afterbegin', imageHTML);

        // Re-setup mining buttons for the new element
        this.setupMiningButtons();

        // Add entrance animation
        const newImage = grid.firstElementChild;
        newImage.style.transform = 'scale(0.8)';
        newImage.style.opacity = '0';

        setTimeout(() => {
            newImage.style.transform = 'scale(1)';
            newImage.style.opacity = '1';
            newImage.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
        }, 100);
    }

    async uploadImage() {
        const fileInput = document.getElementById('image-file');
        const statusDiv = document.getElementById('upload-status');

        if (!fileInput.files[0]) {
            alert('Please select an image first!');
            return;
        }

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);
        formData.append('auto_dither', document.getElementById('auto-dither-upload').checked);

        statusDiv.style.display = 'block';
        statusDiv.textContent = 'Uploading and mining...';

        try {
            const response = await fetch('/api/image-library/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                statusDiv.textContent = `✅ Uploaded! Hash: ${result.hash.substring(0, 16)}... | PoW: ${result.pow_points}`;
                // Add new image to library dynamically instead of reloading page
                this.addNewImageToLibrary(result.image);

                // Clear form
                fileInput.value = '';
                setTimeout(() => {
                    statusDiv.textContent = '';
                    statusDiv.style.display = 'none';
                }, 3000);
            }
        } catch (error) {
            statusDiv.textContent = '❌ Upload failed. Please try again.';
        }
    }
}

// Modal functions
function openImageModal(imageId) {
    const modal = document.getElementById('image-modal');
    const content = document.getElementById('modal-content');

    // Find image data
    const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
    const filename = imageElement.dataset.filename;
    const pow = imageElement.dataset.pow;
    const usage = imageElement.dataset.usage;
    const hash = imageElement.dataset.hash;

    content.innerHTML = `
        <div style="text-align: center;">
            <h3 style="color: #3D315B; margin-bottom: 15px;">${filename}</h3>
            <img src="/api/image-library/${imageId}/full" style="max-width: 500px; max-height: 400px; border-radius: 5px;">
            <div style="margin-top: 15px; font-size: 12px; color: #708B75;">
                <div>PoW Earned: <strong>${pow}</strong></div>
                <div>Usage Count: <strong>${usage}</strong></div>
                <div>Hash: <code style="font-size: 10px;">${hash.substring(0, 32)}...</code></div>
            </div>
            <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                <button onclick="copyImageHash('${hash}')" style="padding: 6px 12px; background: #708B75; color: white; border: none; border-radius: 3px; cursor: pointer;">Copy Hash</button>
                <button onclick="downloadImage(${imageId})" style="padding: 6px 12px; background: #9AB87A; color: white; border: none; border-radius: 3px; cursor: pointer;">Download</button>
            </div>
        </div>
    `;

    modal.style.display = 'flex';
}

function closeImageModal() {
    document.getElementById('image-modal').style.display = 'none';
}

function copyImageHash(hash) {
    navigator.clipboard.writeText(hash).then(() => {
        alert('Hash copied to clipboard!');
    });
}

function downloadImage(imageId) {
    window.open(`/api/image-library/${imageId}/download`, '_blank');
}

// Initialize when DOM loads
document.addEventListener('DOMContentLoaded', () => {
    window.imageLibrary = new ImageLibrarySystem();

    // Add fade text animation
    const fadeElements = document.querySelectorAll('.fade-text');
    fadeElements.forEach(element => {
        const englishText = element.dataset.en;
        const japaneseText = element.dataset.jp;

        if (englishText && japaneseText) {
            setInterval(() => {
                element.style.opacity = '0';
                element.style.transition = 'opacity 0.5s ease-in-out';

                setTimeout(() => {
                    element.textContent = element.textContent.trim() === englishText ? japaneseText : englishText;
                    element.style.opacity = '1';
                }, 500);
            }, 3000);
        }
    });
});
</script>

<!-- CSS for strobing emojis and mining animations -->
<style>
@keyframes strobe {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.1); }
    100% { opacity: 1; transform: scale(1); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

@keyframes mining-glow {
    0% { box-shadow: 0 0 5px rgba(255, 215, 0, 0.3); }
    50% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.8), inset 0 0 10px rgba(255, 215, 0, 0.3); }
    100% { box-shadow: 0 0 5px rgba(255, 215, 0, 0.3); }
}

@keyframes jackpot-spin {
    0% { transform: rotate(0deg) scale(1); }
    25% { transform: rotate(90deg) scale(1.1); }
    50% { transform: rotate(180deg) scale(1.2); }
    75% { transform: rotate(270deg) scale(1.1); }
    100% { transform: rotate(360deg) scale(1); }
}

.strobing-emoji {
    animation: strobe 2s infinite;
}

.fade-text {
    transition: opacity 0.5s ease-in-out;
}

.library-image:hover .mine-image-btn {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(68, 75, 110, 0.3);
}

.mining-active {
    animation: mining-glow 1s infinite;
}

.jackpot-win {
    animation: jackpot-spin 1s ease-in-out;
}

/* Special hash pattern styling */
.hash-legendary { border: 3px solid #8A2BE2 !important; box-shadow: 0 0 15px rgba(138, 43, 226, 0.5); }
.hash-cursed { border: 3px solid #DC143C !important; box-shadow: 0 0 15px rgba(220, 20, 60, 0.5); }
.hash-death { border: 3px solid #000 !important; box-shadow: 0 0 15px rgba(0, 0, 0, 0.8); }
.hash-bonus { border: 3px solid #FF6347 !important; box-shadow: 0 0 15px rgba(255, 99, 71, 0.5); }
</style>
@endsection