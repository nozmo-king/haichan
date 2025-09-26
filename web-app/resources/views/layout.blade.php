<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Forum')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/elegant-themes.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <script src="/js/transparent-pow.js"></script>
    @vite('resources/js/simple-mining.js')
    <script>
        // Force complete cache refresh
        console.log('🔄 LAYOUT LOADED - New mining system should initialize');

        // HYPERINTERACTIVE MINING SYSTEM - EVERYTHING IS MINEABLE
        document.addEventListener('DOMContentLoaded', function() {
            let currentHoveredElement = null;

            // Track all hoverable/mineable elements
            function setupGlobalMining() {
                // Make ALL images mineable
                const images = document.querySelectorAll('img');
                images.forEach(img => {
                    img.style.cursor = 'crosshair';
                    img.addEventListener('mouseenter', () => {
                        currentHoveredElement = img;
                        img.style.boxShadow = '0 0 10px rgba(255, 215, 0, 0.8)';
                        img.style.border = '2px solid #FFD700';
                        setupAutoMining(img);
                    });
                    img.addEventListener('mouseleave', () => {
                        if (currentHoveredElement === img) {
                            currentHoveredElement = null;
                        }
                        img.style.boxShadow = '';
                        img.style.border = '';
                        clearInterval(miningInterval);
                        document.getElementById('toolbar-target').textContent = 'None';
                    });
                });

                // Make threads mineable (but not boards/pages)
                const threads = document.querySelectorAll('.catalog-thread, .library-image, .post[data-thread-id]');
                threads.forEach(thread => {
                    thread.style.cursor = 'crosshair';
                    thread.addEventListener('mouseenter', () => {
                        currentHoveredElement = thread;
                        thread.style.boxShadow = '0 0 8px rgba(154, 184, 122, 0.6)';
                        thread.style.borderColor = '#9AB87A';
                        setupAutoMining(thread);
                    });
                    thread.addEventListener('mouseleave', () => {
                        if (currentHoveredElement === thread) {
                            currentHoveredElement = null;
                        }
                        thread.style.boxShadow = '';
                        thread.style.borderColor = '';
                        clearInterval(miningInterval);
                        document.getElementById('toolbar-target').textContent = 'None';
                    });
                });
            }

            // Continuous auto-mining on hover
            let miningInterval;
            function setupAutoMining(element) {
                clearInterval(miningInterval);

                // Update toolbar target display
                updateTargetDisplay(element);

                // Start mining immediately and continue every 200ms
                mineElement(element);
                miningInterval = setInterval(() => {
                    if (currentHoveredElement === element) {
                        mineElement(element);
                    } else {
                        clearInterval(miningInterval);
                        // Reset target display when mining stops
                        document.getElementById('toolbar-target').textContent = 'None';
                    }
                }, 200);
            }

            function updateTargetDisplay(element) {
                const targetDisplay = document.getElementById('toolbar-target');

                if (element.tagName === 'IMG') {
                    // Mining an image
                    const src = element.src;
                    const filename = src.split('/').pop();
                    const libraryImage = element.closest('[data-image-id]');

                    if (libraryImage) {
                        targetDisplay.textContent = `Image #${libraryImage.dataset.imageId}`;
                    } else {
                        targetDisplay.textContent = `Image: ${filename.substring(0, 15)}...`;
                    }
                } else if (element.dataset.threadId) {
                    targetDisplay.textContent = `Thread #${element.dataset.threadId}`;
                } else if (element.dataset.imageId) {
                    targetDisplay.textContent = `Image #${element.dataset.imageId}`;
                } else {
                    targetDisplay.textContent = 'Unknown';
                }
            }

            async function mineElement(element) {
                // Determine what type of element we're mining
                let miningTarget = null;
                let miningType = 'generic';

                if (element.tagName === 'IMG') {
                    // Mining an image - try to find its ID in image library
                    const src = element.src;
                    const filename = src.split('/').pop();

                    // If it's a library image, get the ID
                    const libraryImage = element.closest('[data-image-id]');
                    if (libraryImage) {
                        miningTarget = libraryImage.dataset.imageId;
                        miningType = 'image';
                    } else {
                        // Try to mine it as a generic image
                        miningTarget = filename;
                        miningType = 'file';
                    }
                } else if (element.dataset.threadId) {
                    miningTarget = element.dataset.threadId;
                    miningType = 'thread';
                } else if (element.dataset.imageId) {
                    miningTarget = element.dataset.imageId;
                    miningType = 'image';
                }

                if (miningTarget) {
                    // Visual feedback
                    element.style.animation = 'mining-pulse 0.5s ease-in-out';

                    // Show mining indicator
                    const indicator = document.createElement('div');
                    indicator.textContent = '⛏️ MINING...';
                    indicator.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255, 215, 0, 0.9); color: black; padding: 4px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; z-index: 10000; pointer-events: none;';
                    element.style.position = 'relative';
                    element.appendChild(indicator);

                    // Actual mining - no fake delays
                    (async () => {
                        let points = 0;
                        let proofFound = false;

                        // Mine real proof based on element type
                        if (miningType === 'thread' && !isNaN(miningTarget)) {
                            try {
                                const proof = await mineProofForThread(miningTarget);
                                if (proof) {
                                    const response = await fetch('/api/submit-proof', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify({
                                            hash: proof.hash,
                                            nonce: proof.nonce,
                                            data: proof.data,
                                            pattern: proof.pattern,
                                            target_type: 'thread',
                                            target_id: miningTarget
                                        })
                                    });

                                    const result = await response.json();
                                    if (result.success) {
                                        points = result.points;
                                        proofFound = true;
                                    }
                                }
                            } catch (error) {
                                console.log('Thread mining failed:', error);
                            }
                        } else if (miningType === 'image' && !isNaN(miningTarget)) {
                            try {
                                const proof = await mineProofForImage(miningTarget);
                                if (proof) {
                                    const response = await fetch('/api/image-library/mine', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify({
                                            image_id: miningTarget,
                                            proof_hash: proof.hash,
                                            proof_nonce: proof.nonce,
                                            proof_data: proof.data,
                                            proof_pattern: proof.pattern
                                        })
                                    });

                                    const result = await response.json();
                                    if (result.success) {
                                        points = result.points || 1;
                                        proofFound = true;
                                    }
                                }
                            } catch (error) {
                                console.log('Image mining failed:', error);
                            }
                        }

                        // Remove old indicator
                        indicator.remove();

                        // Only show floating proof if we actually found one
                        if (proofFound && points > 0) {
                            createFloatingProof(element, points);
                        }

                        element.style.animation = '';
                    })();
                }
            }

            // Create floating proof animation
            function createFloatingProof(element, points) {
                const floater = document.createElement('div');
                floater.textContent = `⚡ +${points} PoW!`;
                floater.style.cssText = `
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(154, 184, 122, 0.95);
                    color: white;
                    padding: 6px 12px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                    z-index: 10001;
                    pointer-events: none;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    border: 1px solid rgba(255,255,255,0.3);
                `;

                // Ensure parent is positioned
                const originalPosition = element.style.position;
                if (!originalPosition || originalPosition === 'static') {
                    element.style.position = 'relative';
                }

                element.appendChild(floater);

                // Animate upwards and fade out
                let startTime = Date.now();
                function animate() {
                    const elapsed = Date.now() - startTime;
                    const duration = 2000;
                    const progress = elapsed / duration;

                    if (progress <= 1) {
                        // Move upwards by 50px over 2 seconds
                        const translateY = -50 * progress;
                        // Fade out over last 1.5 seconds
                        const opacity = progress < 0.25 ? 1 : Math.max(0, 1 - ((progress - 0.25) / 0.75));

                        floater.style.transform = `translate(-50%, calc(-50% + ${translateY}px))`;
                        floater.style.opacity = opacity;

                        requestAnimationFrame(animate);
                    } else {
                        // Animation complete - clean up
                        floater.remove();
                        if (!originalPosition || originalPosition === 'static') {
                            element.style.position = originalPosition || 'static';
                        }
                    }
                }

                requestAnimationFrame(animate);
            }

            // Mine actual proof for thread bumping
            async function mineProofForThread(threadId) {
                const data = `thread-${threadId}-${Date.now()}`;
                const pattern = '21e8';
                let nonce = crypto.getRandomValues(new Uint32Array(1))[0];
                let attempts = 0;
                const maxAttempts = 2000; // Allow more attempts for real mining

                while (attempts < maxAttempts) {
                    const hashInput = `${data}:${nonce}`;
                    const hash = await sha256(hashInput);

                    // Check for rare hash patterns (even if they don't match our target)
                    const isRare = checkForRareHash(hash);

                    // Trigger neural visualization
                    if (window.triggerMiningVisualization) {
                        window.triggerMiningVisualization(hash, isRare ? 'rare' : 'normal');
                    }

                    if (hash.startsWith(pattern)) {
                        return {
                            hash: hash,
                            nonce: nonce,
                            data: hashInput,
                            pattern: pattern
                        };
                    }

                    nonce++;
                    attempts++;

                    // Yield control occasionally
                    if (attempts % 50 === 0) {
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                }

                return null;
            }

            // Mine actual proof for image mining
            async function mineProofForImage(imageId) {
                const data = `image-${imageId}-${Date.now()}`;
                const pattern = '21e8';
                let nonce = crypto.getRandomValues(new Uint32Array(1))[0];
                let attempts = 0;
                const maxAttempts = 2000; // Allow more attempts for real mining

                while (attempts < maxAttempts) {
                    const hashInput = `${data}:${nonce}`;
                    const hash = await sha256(hashInput);

                    // Check for rare hash patterns (even if they don't match our target)
                    const isRare = checkForRareHash(hash);

                    // Trigger neural visualization
                    if (window.triggerMiningVisualization) {
                        window.triggerMiningVisualization(hash, isRare ? 'rare' : 'normal');
                    }

                    if (hash.startsWith(pattern)) {
                        return {
                            hash: hash,
                            nonce: nonce,
                            data: hashInput,
                            pattern: pattern
                        };
                    }

                    nonce++;
                    attempts++;

                    // Yield control occasionally
                    if (attempts % 50 === 0) {
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                }

                return null;
            }

            // SHA-256 hash function
            async function sha256(message) {
                const msgBuffer = new TextEncoder().encode(message);
                const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            // Rare hash detection and notification system
            function checkForRareHash(hash) {
                const rarePatterns = {
                    // Legendary patterns
                    'deadbeef': { points: 3133, rarity: '🏆 LEGENDARY', color: '#FFD700' },
                    '1337': { points: 1337, rarity: '👑 ELITE', color: '#FF4444' },
                    'c0de': { points: 1337, rarity: '👑 ELITE', color: '#FF4444' },
                    'b00b': { points: 800, rarity: '🔥 EPIC', color: '#FF6600' },
                    '777': { points: 777, rarity: '🍀 LUCKY', color: '#00FF00' },
                    '666': { points: 666, rarity: '😈 CURSED', color: '#FF0000' },

                    // Triple patterns
                    '000': { points: 500, rarity: '⚡ RARE', color: '#4444FF' },
                    '111': { points: 400, rarity: '⚡ RARE', color: '#4444FF' },
                    '222': { points: 300, rarity: '✨ UNCOMMON', color: '#8844FF' },
                    '333': { points: 350, rarity: '✨ UNCOMMON', color: '#8844FF' },
                    '444': { points: 300, rarity: '✨ UNCOMMON', color: '#8844FF' },
                    '555': { points: 450, rarity: '⚡ RARE', color: '#4444FF' },
                    '888': { points: 400, rarity: '⚡ RARE', color: '#4444FF' },
                    '999': { points: 350, rarity: '✨ UNCOMMON', color: '#8844FF' },

                    // Hex letters
                    'aaa': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'bbb': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'ccc': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'ddd': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'eee': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'fff': { points: 300, rarity: '✨ UNCOMMON', color: '#8844FF' },

                    // Words
                    'dead': { points: 400, rarity: '⚡ RARE', color: '#4444FF' },
                    'beef': { points: 300, rarity: '✨ UNCOMMON', color: '#8844FF' },
                    'cafe': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'face': { points: 200, rarity: '📝 NOTABLE', color: '#666666' },
                    'babe': { points: 180, rarity: '📝 NOTABLE', color: '#666666' },
                    'feed': { points: 200, rarity: '📝 NOTABLE', color: '#666666' },
                    'deed': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },
                    'fade': { points: 150, rarity: '📝 NOTABLE', color: '#666666' },
                    'pwnd': { points: 500, rarity: '⚡ RARE', color: '#4444FF' },
                    'rekt': { points: 400, rarity: '⚡ RARE', color: '#4444FF' },
                    'epic': { points: 300, rarity: '✨ UNCOMMON', color: '#8844FF' },
                    'chad': { points: 250, rarity: '📝 NOTABLE', color: '#666666' },

                    // 3-letter words
                    'ace': { points: 150, rarity: '📝 NOTABLE', color: '#666666' },
                    'bad': { points: 100, rarity: '📝 NOTABLE', color: '#666666' },
                    'dad': { points: 120, rarity: '📝 NOTABLE', color: '#666666' },
                    'ded': { points: 200, rarity: '📝 NOTABLE', color: '#666666' },
                    'fab': { points: 100, rarity: '📝 NOTABLE', color: '#666666' },
                    'fed': { points: 90, rarity: '📝 NOTABLE', color: '#666666' },
                    'cab': { points: 80, rarity: '📝 NOTABLE', color: '#666666' }
                };

                for (const [pattern, data] of Object.entries(rarePatterns)) {
                    if (hash.toLowerCase().startsWith(pattern.toLowerCase())) {
                        notifyRareHash(pattern, hash, data);
                        return true;
                    }
                }
                return false;
            }

            function notifyRareHash(pattern, fullHash, data) {
                // Show mini-dashboard notifications area
                const notificationArea = document.getElementById('rare-hash-notifications');
                const hashList = document.getElementById('rare-hash-list');

                if (notificationArea && hashList) {
                    notificationArea.style.display = 'block';

                    const timestamp = new Date().toLocaleTimeString();
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        margin: 3px 0;
                        padding: 4px;
                        background: ${data.color}22;
                        border-left: 3px solid ${data.color};
                        border-radius: 2px;
                        animation: rareHashGlow 2s ease-in-out;
                    `;

                    notification.innerHTML = `
                        <div style="color: ${data.color}; font-weight: bold; font-size: 8pt;">
                            ${data.rarity} ${pattern.toUpperCase()}
                        </div>
                        <div style="color: #444; font-size: 6pt; margin-top: 2px;">
                            ${fullHash.substring(0, 16)}... (+${data.points} pts)
                        </div>
                        <div style="color: #666; font-size: 6pt;">
                            ${timestamp}
                        </div>
                    `;

                    hashList.insertBefore(notification, hashList.firstChild);

                    // Keep only last 10 notifications
                    while (hashList.children.length > 10) {
                        hashList.removeChild(hashList.lastChild);
                    }

                    // Toolbar flash notification
                    flashToolbarRareHash(pattern, data);
                }
            }

            function flashToolbarRareHash(pattern, data) {
                // Flash the toolbar target display
                const toolbarTarget = document.getElementById('toolbar-target');
                if (toolbarTarget) {
                    const originalText = toolbarTarget.textContent;
                    const originalColor = toolbarTarget.style.color;

                    toolbarTarget.style.color = data.color;
                    toolbarTarget.style.animation = 'rareHashPulse 1s ease-in-out 3';
                    toolbarTarget.textContent = `${data.rarity} ${pattern.toUpperCase()}!`;

                    setTimeout(() => {
                        toolbarTarget.textContent = originalText;
                        toolbarTarget.style.color = originalColor;
                        toolbarTarget.style.animation = '';
                    }, 3000);
                }
            }

            // Real network statistics updater
            function updateNetworkStats() {
                fetch('/api/proof/stats')
                    .then(response => response.json())
                    .then(data => {
                        // Update toolbar stats
                        if (document.getElementById('network-total-pow')) {
                            document.getElementById('network-total-pow').textContent = data.total_proofs?.toLocaleString() || '0';
                        }
                        if (document.getElementById('network-active-miners')) {
                            document.getElementById('network-active-miners').textContent = data.active_miners || '1';
                        }

                        // Update mini-dashboard stats if visible
                        if (document.getElementById('dashboard-proofs')) {
                            document.getElementById('dashboard-proofs').textContent = data.session_proofs || '0';
                        }
                    })
                    .catch(error => {
                        console.log('Failed to update network stats:', error);
                    });
            }

            // Update network stats every 30 seconds
            updateNetworkStats();
            setInterval(updateNetworkStats, 30000);

            // NEURAL MINING VISUALIZATION ENGINE
            function initNeuralCanvas() {
                const canvas = document.getElementById('neural-mining-canvas');
                const ctx = canvas.getContext('2d');

                // Set canvas size
                function resizeCanvas() {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                }
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);

                // Neural network nodes
                const nodes = [];
                const connections = [];

                // Create neural nodes
                for (let i = 0; i < 50; i++) {
                    nodes.push({
                        x: Math.random() * canvas.width,
                        y: Math.random() * canvas.height,
                        vx: (Math.random() - 0.5) * 0.5,
                        vy: (Math.random() - 0.5) * 0.5,
                        activity: 0,
                        lastMined: 0
                    });
                }

                function drawNeuralNetwork() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    // Draw connections
                    ctx.strokeStyle = 'rgba(112, 139, 117, 0.1)';
                    ctx.lineWidth = 0.5;

                    for (let i = 0; i < nodes.length; i++) {
                        for (let j = i + 1; j < nodes.length; j++) {
                            const dx = nodes[i].x - nodes[j].x;
                            const dy = nodes[i].y - nodes[j].y;
                            const distance = Math.sqrt(dx * dx + dy * dy);

                            if (distance < 100) {
                                const opacity = (1 - distance / 100) * 0.2;
                                ctx.strokeStyle = `rgba(154, 184, 122, ${opacity})`;
                                ctx.beginPath();
                                ctx.moveTo(nodes[i].x, nodes[i].y);
                                ctx.lineTo(nodes[j].x, nodes[j].y);
                                ctx.stroke();
                            }
                        }
                    }

                    // Draw nodes
                    nodes.forEach(node => {
                        // Update position
                        node.x += node.vx;
                        node.y += node.vy;

                        // Bounce off edges
                        if (node.x < 0 || node.x > canvas.width) node.vx *= -1;
                        if (node.y < 0 || node.y > canvas.height) node.vy *= -1;

                        // Decay activity
                        node.activity *= 0.95;

                        // Draw node
                        const size = 2 + node.activity * 8;
                        const opacity = 0.3 + node.activity * 0.7;

                        ctx.fillStyle = `rgba(154, 184, 122, ${opacity})`;
                        ctx.beginPath();
                        ctx.arc(node.x, node.y, size, 0, Math.PI * 2);
                        ctx.fill();

                        // Pulse effect for recent mining
                        if (Date.now() - node.lastMined < 2000) {
                            const pulseIntensity = 1 - (Date.now() - node.lastMined) / 2000;
                            ctx.strokeStyle = `rgba(255, 215, 0, ${pulseIntensity})`;
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            ctx.arc(node.x, node.y, size + pulseIntensity * 10, 0, Math.PI * 2);
                            ctx.stroke();
                        }
                    });

                    requestAnimationFrame(drawNeuralNetwork);
                }

                drawNeuralNetwork();

                // Trigger mining visualization
                window.triggerMiningVisualization = function(hash, rarity = 'normal') {
                    const randomNode = nodes[Math.floor(Math.random() * nodes.length)];
                    randomNode.activity = 1.0;
                    randomNode.lastMined = Date.now();

                    // Create ripple effect for rare hashes
                    if (rarity !== 'normal') {
                        nodes.forEach(node => {
                            const distance = Math.sqrt(
                                (node.x - randomNode.x) ** 2 + (node.y - randomNode.y) ** 2
                            );
                            if (distance < 200) {
                                setTimeout(() => {
                                    node.activity = Math.max(node.activity, 0.5);
                                    node.lastMined = Date.now();
                                }, distance * 5);
                            }
                        });
                    }
                };
            }

            setupGlobalMining();
            initNeuralCanvas();
        });
    </script>
    <style>
        @keyframes mining-pulse {
            0% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.02); filter: brightness(1.2); }
            100% { transform: scale(1); filter: brightness(1); }
        }

        @keyframes rareHashGlow {
            0% { box-shadow: 0 0 0 rgba(255,215,0,0); transform: scale(1); }
            50% { box-shadow: 0 0 20px rgba(255,215,0,0.6); transform: scale(1.02); }
            100% { box-shadow: 0 0 0 rgba(255,215,0,0); transform: scale(1); }
        }

        @keyframes rareHashPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    </style>
    <script>
    // Force cache refresh and clear old mining system
    if (window.haichanMiner) {
        window.haichanMiner = null;
        delete window.haichanMiner;
    }
    if (window.simpleMiner) {
        window.simpleMiner = null;
        delete window.simpleMiner;
    }
    // Clear old dashboards
    document.addEventListener('DOMContentLoaded', () => {
        const oldDash = document.getElementById('mini-dashboard-overlay');
        if (oldDash) oldDash.remove();
    });
    </script>
    <!-- All styles are now in /css/haichan.css -->
</head>
<body data-theme="classic">

    <!-- Neural Mining Visualization Canvas -->
    <canvas id="neural-mining-canvas" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
        opacity: 0.1;
    "></canvas>


    <!-- Elegant Film Grain Mini Dashboard -->
    <div id="mini-dashboard" style="
        position: fixed;
        top: 100px;
        right: 20px;
        width: 360px;
        background: linear-gradient(135deg, rgba(245, 245, 220, 0.98) 0%, rgba(240, 240, 215, 0.98) 100%);
        border: 3px solid rgba(68, 75, 110, 0.6);
        border-radius: 12px;
        z-index: 10000;
        display: none;
        font-family: 'Courier New', monospace;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(15px);
        overflow: hidden;
    " class="film-grain-overlay">
        <!-- Film Grain Background -->
        <div class="film-grain" style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.15;
            mix-blend-mode: multiply;
            pointer-events: none;
            z-index: -1;
        "></div>
        
        <!-- Dashboard Header -->
        <div id="dashboard-header" style="
            background: linear-gradient(135deg, rgba(68, 75, 110, 0.95) 0%, rgba(112, 139, 117, 0.95) 100%);
            color: #FFFFEE;
            padding: 12px 16px;
            font-size: 11pt;
            font-weight: bold;
            cursor: move;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 9px 9px 0 0;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        ">
            <span>⛏️ HAICHAN MINER</span>
            <div>
                <button id="minimize-dashboard" style="
                    background: rgba(255, 255, 255, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    color: #FFFFEE;
                    padding: 4px 8px;
                    margin-right: 6px;
                    cursor: pointer;
                    font-size: 10px;
                    border-radius: 4px;
                    transition: all 0.2s ease;
                    backdrop-filter: blur(5px);
                " title="Minimize" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">−</button>
                <button id="close-dashboard" style="
                    background: rgba(255, 255, 255, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    color: #FFFFEE;
                    padding: 4px 8px;
                    cursor: pointer;
                    font-size: 10px;
                    border-radius: 4px;
                    transition: all 0.2s ease;
                    backdrop-filter: blur(5px);
                " title="Close" onmouseover="this.style.background='rgba(255,100,100,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">✕</button>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div id="dashboard-content" style="padding: 20px; font-size: 10pt; position: relative; z-index: 10;">
            <!-- Mining Target Display -->
            <div style="margin-bottom: 16px; padding: 12px; background: rgba(255, 255, 255, 0.4); border-radius: 8px; border: 1px solid rgba(154, 184, 122, 0.3);">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 6px; font-size: 9pt;">🎯 Mining Target</div>
                <div id="dashboard-target" style="color: #666; font-size: 8pt; font-family: monospace;">No target selected</div>
            </div>

            <!-- Performance Stats -->
            <div style="margin-bottom: 16px; padding: 12px; background: rgba(255, 255, 255, 0.4); border-radius: 8px; border: 1px solid rgba(154, 184, 122, 0.3);">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 8px; font-size: 9pt;">📊 Performance</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 8pt;">
                    <div>Hash Rate: <br><span id="dashboard-hashrate" style="color: #789922; font-weight: bold;">0 H/s</span></div>
                    <div>Difficulty: <br><span id="dashboard-difficulty" style="color: #789922; font-weight: bold;">21e8</span></div>
                    <div>Session Proofs: <br><span id="dashboard-proofs" style="color: #666; font-weight: bold;">0</span></div>
                    <div>Status: <br><span id="dashboard-status" style="color: #666; font-weight: bold;">IDLE</span></div>
                </div>
            </div>

            <!-- Elegant Gradient Power Slider -->
            <div style="margin-bottom: 16px; padding: 12px; background: rgba(255, 255, 255, 0.4); border-radius: 8px; border: 1px solid rgba(154, 184, 122, 0.3);">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 8px; font-size: 9pt;">⚡ Mining Power: <span id="power-level-display" style="color: #789922;">3</span>/10</div>
                
                <!-- Custom Gradient Slider -->
                <div style="position: relative; height: 20px; margin: 10px 0;">
                    <div style="
                        position: absolute;
                        width: 100%;
                        height: 20px;
                        background: linear-gradient(90deg, 
                            #4CAF50 0%,     /* Green - Low */
                            #8BC34A 20%,    /* Light Green */
                            #FFEB3B 40%,    /* Yellow */
                            #FF9800 60%,    /* Orange */
                            #FF5722 80%,    /* Red-Orange */
                            #F44336 100%    /* Red - High */
                        );
                        border-radius: 10px;
                        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
                    "></div>
                    <input type="range" id="dashboard-power-slider" min="0" max="10" value="0" oninput="directMining(this.value)" style="
                        position: absolute;
                        width: 100%;
                        height: 20px;
                        background: transparent;
                        -webkit-appearance: none;
                        appearance: none;
                        cursor: pointer;
                        border-radius: 10px;
                        z-index: 1000;
                        pointer-events: auto;
                    ">
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 7pt; color: #666; margin-top: 6px;">
                    <span style="color: #4CAF50;">💨 Whisper</span>
                    <span style="color: #FF9800;">🚀 Cruise</span>
                    <span style="color: #F44336;">🔥 OVERDRIVE</span>
                </div>
            </div>

            <!-- Current Hash Display -->
            <div style="padding: 12px; background: rgba(255, 255, 255, 0.4); border-radius: 8px; border: 1px solid rgba(154, 184, 122, 0.3);">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 6px; font-size: 9pt;">🔐 Current Hash</div>
                <div id="dashboard-current-hash" style="
                    font-family: 'Courier New', monospace;
                    font-size: 7pt;
                    color: #888;
                    word-break: break-all;
                    background: rgba(250, 250, 250, 0.8);
                    padding: 6px;
                    border: 1px solid rgba(221, 221, 221, 0.6);
                    border-radius: 4px;
                    line-height: 1.3;
                ">calculating...</div>
            </div>
            

            <!-- Quick Navigation -->
            <div style="margin-top: 16px; display: flex; gap: 8px; justify-content: space-between;">
                <a href="/catalog" style="
                    flex: 1;
                    text-align: center;
                    background: rgba(154, 184, 122, 0.3);
                    color: #444B6E;
                    text-decoration: none;
                    padding: 8px 4px;
                    border-radius: 6px;
                    font-size: 8pt;
                    font-weight: bold;
                    border: 1px solid rgba(154, 184, 122, 0.4);
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(154,184,122,0.5)'" onmouseout="this.style.background='rgba(154,184,122,0.3)'">🎯 MC</a>
                <a href="/library" style="
                    flex: 1;
                    text-align: center;
                    background: rgba(154, 184, 122, 0.3);
                    color: #444B6E;
                    text-decoration: none;
                    padding: 8px 4px;
                    border-radius: 6px;
                    font-size: 8pt;
                    font-weight: bold;
                    border: 1px solid rgba(154, 184, 122, 0.4);
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(154,184,122,0.5)'" onmouseout="this.style.background='rgba(154,184,122,0.3)'">🖼️ LIB</a>
                <a href="/mining" style="
                    flex: 1;
                    text-align: center;
                    background: rgba(154, 184, 122, 0.3);
                    color: #444B6E;
                    text-decoration: none;
                    padding: 8px 4px;
                    border-radius: 6px;
                    font-size: 8pt;
                    font-weight: bold;
                    border: 1px solid rgba(154, 184, 122, 0.4);
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='rgba(154,184,122,0.5)'" onmouseout="this.style.background='rgba(154,184,122,0.3)'">⛏️ FULL</a>
            </div>
        </div>
    </div>

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
                
                <a href="/mining" class="nav-link" style="
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
                    ⛏️ <span>MINE</span>
                </a>
                
                @if(session('bitcoin_auth_id'))
                <a href="/user/{{ session('bitcoin_auth_id') }}" class="nav-link" style="
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
                    👤 <span>PROFILE</span>
                </a>
                @endif
                
                <div id="site-dither-toggle" style="
                    color: var(--nav-link-color, #E8FFE8);
                    background: transparent;
                    border: none;
                    padding: 6px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    transition: all 0.2s ease;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    font-size: 11px;
                " onmouseover="this.style.background='var(--nav-link-hover, rgba(255,255,238,0.2))'" onmouseout="this.style.background='transparent'" onclick="toggleSiteDither()">
                    🎨 <span id="site-dither-status">Auto-Dither: OFF</span>
                </div>
                
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
                
                <!-- Board Dropdown -->
                <div class="dropdown" style="position: relative;">
                    <button id="board-dropdown-btn" style="
                        color: var(--nav-link-color, #E8FFE8);
                        background: transparent;
                        border: none;
                        padding: 6px 10px;
                        border-radius: 6px;
                        font-size: 10px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        display: flex;
                        align-items: center;
                        gap: 4px;
                    " onmouseover="this.style.background='var(--nav-link-hover, rgba(255,255,238,0.2))'" onmouseout="this.style.background='transparent'" onclick="toggleBoardDropdown()">
                        📋 <span>BOARDS</span> <span style="font-size: 8px;">▼</span>
                    </button>
                    <div id="board-dropdown" style="
                        position: absolute;
                        bottom: 100%;
                        left: 0;
                        min-width: 200px;
                        background: var(--dropdown-bg, rgba(68, 75, 110, 0.98));
                        border: 1px solid var(--dropdown-border, rgba(255, 255, 238, 0.3));
                        border-radius: 8px;
                        box-shadow: 0 8px 32px var(--dropdown-shadow, rgba(0, 0, 0, 0.4));
                        backdrop-filter: blur(20px);
                        margin-bottom: 8px;
                        max-height: 300px;
                        overflow-y: auto;
                        display: none;
                        z-index: 10000;
                    ">
                        @php
                        $boardIcons = [
                            'gen' => '💬', 'tech' => '💻', 'biz' => '💼', 'film' => '🎬',
                            'x' => '👽', 'lit' => '📚', 'meta' => '⚙️', 'mu' => '🎵'
                        ];
                        $allBoards = \App\Models\Board::orderBy('code')->get() ?? [];
                        @endphp
                        @foreach($allBoards as $board)
                        <a href="/{{ $board->code }}" style="
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            padding: 8px 12px;
                            color: var(--dropdown-text, #E8FFE8);
                            text-decoration: none;
                            font-size: 10px;
                            font-weight: 500;
                            transition: all 0.2s ease;
                        " onmouseover="this.style.background='var(--dropdown-hover, rgba(255,255,238,0.1))'" onmouseout="this.style.background='transparent'">
                            <span>{{ $boardIcons[$board->code] ?? '📌' }}</span>
                            <span>/{{ $board->code }}/</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Center Section: Logo & Status -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="text-align: center;">
                <a href="/" style="
                    text-decoration: none;
                    color: var(--logo-color, #FFFFEE);
                    font-family: 'Nova Cut', serif;
                    font-size: 16px;
                    font-weight: 300;
                    letter-spacing: 1px;
                    text-shadow: 0 2px 4px var(--logo-shadow, rgba(0,0,0,0.5));
                    transition: all 0.3s ease;
                " id="toolbar-logo" onmouseover="this.style.textShadow='0 0 12px var(--logo-glow, rgba(255,255,238,0.6))'" onmouseout="this.style.textShadow='0 2px 4px var(--logo-shadow, rgba(0,0,0,0.5))'">
                    HAICHAN
                </a>
                <div style="
                    font-size: 8px;
                    opacity: 0.7;
                    margin-top: 2px;
                    color: var(--status-text, #FFFFEE);
                ">
                    <span id="toolbar-status">Ready</span> • <span id="toolbar-users">{{ $activeSessions ?? 1 }}</span> online
                </div>
            </div>
        </div>

        <!-- Right Section: Theme & Dashboard -->
        <div style="display: flex; align-items: center; gap: 12px;">
            
            <!-- Dashboard Toggle -->
            <button id="toolbar-mini-dash-toggle" style="
                background: var(--dash-btn-bg, rgba(154, 184, 122, 0.8));
                border: 1px solid var(--dash-btn-border, rgba(255, 255, 238, 0.3));
                color: var(--dash-btn-text, #FFFFEE);
                padding: 8px 12px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 10px;
                font-weight: 700;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 2px 8px var(--dash-btn-shadow, rgba(154, 184, 122, 0.3));
                display: flex;
                align-items: center;
                gap: 4px;
            " title="Open Mining Dashboard" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px var(--dash-btn-shadow, rgba(154, 184, 122, 0.4))'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px var(--dash-btn-shadow, rgba(154, 184, 122, 0.3))'">
                ⛏️ <span>MINE</span>
            </button>
        </div>
    </div>

    <div class="container" style="margin-top: 20px; margin-bottom: 80px;">
        <div class="header">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
                <h1><a href="/" style="text-decoration: none; color: var(--header-color, #3D315B); font-family: 'Nova Cut', serif; font-size: 32px; font-weight: 300; letter-spacing: 3px; text-shadow: 0 2px 4px var(--header-shadow, rgba(0,0,0,0.1));" id="header-text">HAICHAN</a></h1>
            </div>
        </div>
        
        @yield('content')
    </div>


    <script>
        // Board Dropdown Toggle
        function toggleBoardDropdown() {
            const dropdown = document.getElementById('board-dropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('board-dropdown');
            const button = document.getElementById('board-dropdown-btn');
            if (dropdown && !dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Mini Dashboard Controls
        document.addEventListener('DOMContentLoaded', function() {
            const dashboard = document.getElementById('mini-dashboard');
            const dashboardHeader = document.getElementById('dashboard-header');
            const toggleBtn = document.getElementById('mini-dash-toggle');
            const toolbarToggleBtn = document.getElementById('toolbar-mini-dash-toggle');
            const minimizeBtn = document.getElementById('minimize-dashboard');
            const closeBtn = document.getElementById('close-dashboard');
            const dashboardContent = document.getElementById('dashboard-content');

            let isMinimized = false;
            let isDragging = false;
            let dragOffset = { x: 0, y: 0 };

            // Open dashboard (from both buttons)
            function openDashboard() {
                if (dashboard) {
                    dashboard.style.display = 'block';
                    if (isMinimized) {
                        dashboardContent.style.display = 'block';
                        isMinimized = false;
                    }
                }
            }

            if (toggleBtn) toggleBtn.addEventListener('click', openDashboard);
            if (toolbarToggleBtn) toolbarToggleBtn.addEventListener('click', openDashboard);

            // Minimize dashboard
            minimizeBtn.addEventListener('click', function() {
                dashboardContent.style.display = 'none';
                isMinimized = true;
            });

            // Close dashboard
            closeBtn.addEventListener('click', function() {
                dashboard.style.display = 'none';
                if (isMinimized) {
                    dashboardContent.style.display = 'block';
                    isMinimized = false;
                }
            });

            // Make dashboard draggable
            dashboardHeader.addEventListener('mousedown', function(e) {
                isDragging = true;
                const rect = dashboard.getBoundingClientRect();
                dragOffset.x = e.clientX - rect.left;
                dragOffset.y = e.clientY - rect.top;
                document.body.style.userSelect = 'none';
            });

            document.addEventListener('mousemove', function(e) {
                if (isDragging) {
                    const x = e.clientX - dragOffset.x;
                    const y = e.clientY - dragOffset.y;

                    // Keep within viewport
                    const maxX = window.innerWidth - dashboard.offsetWidth;
                    const maxY = window.innerHeight - dashboard.offsetHeight;

                    dashboard.style.left = Math.max(0, Math.min(x, maxX)) + 'px';
                    dashboard.style.top = Math.max(50, Math.min(y, maxY)) + 'px';
                    dashboard.style.right = 'auto';
                }
            });

            document.addEventListener('mouseup', function() {
                isDragging = false;
                document.body.style.userSelect = '';
            });

            // Keyboard shortcut (Ctrl+D)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'd') {
                    e.preventDefault();
                    toggleBtn.click();
                }
            });

            // Update dashboard with mining stats (safely handle missing elements)
            function updateMiningDisplays() {
                if (window.simpleMiner) {
                    const stats = window.simpleMiner.getStats();

                    // Update dashboard elements only if they exist
                    const elements = {
                        'dashboard-hashrate': (stats.hashRate?.toLocaleString() || '0') + ' H/s',
                        'dashboard-proofs': stats.proofsFound || '0',
                        'dashboard-target': stats.target || 'No target selected',
                        'dashboard-current-hash': stats.currentHash || 'calculating...',
                        'dashboard-status': stats.powerLevel > 0 ? 'MINING' : 'IDLE'
                    };

                    Object.entries(elements).forEach(([id, value]) => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.textContent = value;
                        }
                    });
                }
            }

            // Update displays every second
            setInterval(updateMiningDisplays, 1000);

            // Header seizure effect
            const headerText = document.getElementById('header-text');
            let seizureInterval;
            let seizureActive = false;

            headerText.addEventListener('mouseenter', function() {
                seizureActive = true;
                let count = 0;
                seizureInterval = setInterval(function() {
                    if (!seizureActive) {
                        clearInterval(seizureInterval);
                        return;
                    }
                    const letters = headerText.textContent.split('').map((letter, i) => {
                        const randomX = (Math.random() - 0.5) * 20;
                        const randomY = (Math.random() - 0.5) * 15;
                        const randomRotate = (Math.random() - 0.5) * 360;
                        const randomScale = 0.5 + Math.random() * 1.5;
                        const randomColor = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#FFF'][Math.floor(Math.random() * 7)];
                        return `<span style="display: inline-block; transform: translate(${randomX}px, ${randomY}px) rotate(${randomRotate}deg) scale(${randomScale}); color: ${randomColor}; text-shadow: ${Math.random()*10}px ${Math.random()*10}px ${Math.random()*20}px rgba(255,255,255,0.8);">${letter}</span>`;
                    }).join('');
                    headerText.innerHTML = letters;
                    count++;
                    if (count > 100) { // Prevent infinite seizure
                        clearInterval(seizureInterval);
                        seizureActive = false;
                    }
                }, 50); // Very fast seizure effect
            });

            headerText.addEventListener('mouseleave', function() {
                seizureActive = false;
                clearInterval(seizureInterval);
                headerText.innerHTML = 'HAICHAN';
                // Reset all inline styles and restore original appearance
                headerText.style.transform = '';
                headerText.style.color = '#3D315B';
                headerText.style.textShadow = '';
                headerText.style.letterSpacing = '2px';
                headerText.style.filter = '';
                headerText.style.fontSize = '28px';
                headerText.style.fontWeight = '300';
                headerText.style.fontFamily = "'Nova Cut', serif";
                headerText.style.textDecoration = 'none';
            });

            // Granular power level control (0-10 scale with persistence)
            const powerSlider = document.getElementById('dashboard-power-slider');
            const powerDisplay = document.getElementById('power-level-display');

            if (powerSlider && powerDisplay) {
                // Restore saved power level from localStorage
                const savedPower = localStorage.getItem('haichan_mining_power');
                const initialLevel = savedPower !== null ? parseInt(savedPower) : 0; // Default to 0/10
                powerSlider.value = initialLevel;
                powerDisplay.textContent = initialLevel;
                
                // Initialize power level on page load
                setTimeout(() => {
                    console.log('🔥 POWER INITIALIZATION... Level:', initialLevel);
                    // Apply initial power level to direct miner
                    directMining(initialLevel);
                    
                    if (window.emergencyMiner || window.haichanMiner) {
                        const miner = window.emergencyMiner || window.haichanMiner;
                        const powerPercent = initialLevel * 10;
                        console.log('⚡ SETTING INITIAL POWER:', powerPercent + '%');
                        
                        if (miner.power !== undefined) {
                            miner.power = powerPercent;
                        } else if (miner.powerLevel !== undefined) {
                            miner.powerLevel = powerPercent;
                        }
                        
                        if (powerPercent > 0) {
                            if (miner.start) {
                                miner.start();
                            } else if (miner.startMining) {
                                miner.startMining();
                            }
                        }
                        
                        if (miner.updateDashboard) {
                            miner.updateDashboard();
                        } else if (miner.updateUI) {
                            miner.updateUI();
                        }
                        
                        console.log('✅ MINER INITIALIZED');
                    } else {
                        console.log('❌ NO MINER FOUND');
                    }
                }, 1000);
                
                powerSlider.addEventListener('input', function(e) {
                    const level = parseInt(e.target.value);
                    powerDisplay.textContent = level;
                    
                    // Save power level to localStorage
                    localStorage.setItem('haichan_mining_power', level.toString());
                    
                    // Convert 0-10 range to 0-100 range for mining system  
                    const powerPercent = level * 10; // 0, 10, 20, 30, ..., 100
                    
                    console.log('🎚️ EMERGENCY SLIDER INPUT:', level, '->', powerPercent + '%');
                    
                    // Update emergency miner first, then fallback
                    if (window.emergencyMiner) {
                        console.log('✅ EmergencyMiner found, updating power');
                        window.emergencyMiner.power = powerPercent;
                        localStorage.setItem('emergency_power', powerPercent.toString());
                        if (powerPercent > 0) {
                            window.emergencyMiner.start();
                        } else {
                            window.emergencyMiner.stop();
                        }
                        window.emergencyMiner.updateDashboard();
                        console.log('🔄 Updated dashboard, mining status:', window.emergencyMiner.isActive);
                    } else if (window.haichanMiner) {
                        console.log('✅ HaichanMiner fallback, updating power');
                        window.haichanMiner.powerLevel = powerPercent;
                        localStorage.setItem('haichan_power_level', powerPercent.toString());
                        if (powerPercent > 0) {
                            window.haichanMiner.startMining();
                        } else {
                            window.haichanMiner.stopMining();
                        }
                        window.haichanMiner.updateUI();
                        console.log('🔄 Updated UI, mining status:', window.haichanMiner.isActive);
                    } else {
                        console.log('❌ NO MINER FOUND AT ALL');
                    }
                });
            }
        });

        // DIRECT MINING FUNCTION - INLINE SOLUTION
        window.directMiner = {
            isActive: false,
            power: 30,
            hashCount: 0,
            proofs: 0,
            startTime: 0,
            nonce: 0,
            currentTarget: 'direct:slider',
            currentHash: ''
        };

        window.directMining = function(sliderValue) {
            const power = parseInt(sliderValue) * 10;
            console.log('🎚️ DIRECT SLIDER:', sliderValue, '->', power + '%');
            
            window.directMiner.power = power;
            const powerDisplay = document.getElementById('power-level-display');
            if (powerDisplay) powerDisplay.textContent = sliderValue;
            
            if (power > 0) {
                if (!window.directMiner.isActive) {
                    console.log('🔥 STARTING DIRECT MINING AT', power + '%');
                    window.directMiner.isActive = true;
                    window.directMiner.startTime = Date.now();
                    directMineLoop();
                }
            } else {
                console.log('⏹️ STOPPING DIRECT MINING - POWER SET TO 0');
                if (window.directMiner.isActive) {
                    window.directMiner.isActive = false;
                }
                window.directMiner.isActive = false;
            }
            
            updateDirectDashboard();
        };

        async function directSha256(text) {
            const encoder = new TextEncoder();
            const data = encoder.encode(text);
            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        }

        async function directMineLoop() {
            while (window.directMiner.isActive) {
                const batch = Math.max(50, window.directMiner.power);
                
                for (let i = 0; i < batch && window.directMiner.isActive; i++) {
                    let data;
                    if (window.directMiner.currentTarget === 'direct:slider') {
                        // Mine the user themselves when no specific target
                        const userId = '{{ session("bitcoin_auth_id") ?? "anon" }}';
                        const userHash = '{{ session("bitcoin_auth_user")->public_key ?? "anonymous" }}';
                        data = `user:${userId}:${userHash}:${Date.now()}:${window.directMiner.nonce}`;
                    } else {
                        // Use current target for specific mining
                        data = `${window.directMiner.currentTarget}:${Date.now()}:${window.directMiner.nonce}`;
                    }
                    const hash = await directSha256(data);
                    window.directMiner.currentHash = hash; // Update current hash
                    window.directMiner.hashCount++;
                    window.directMiner.nonce++;
                    
                    if (hash.startsWith('21e8')) {
                        console.log('💎 DIRECT PROOF FOUND!', hash, 'TARGET:', window.directMiner.currentTarget);
                        window.directMiner.proofs++;
                        
                        // Parse target for submission
                        const [targetType, targetId] = window.directMiner.currentTarget.split(':', 2);
                        
                        try {
                            const response = await fetch('/api/submit-proof', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                                },
                                body: JSON.stringify({
                                    hash: hash,
                                    nonce: window.directMiner.nonce - 1,
                                    data: data,
                                    pattern: '21e8',
                                    target_type: targetType,
                                    target_id: targetId
                                })
                            });
                            
                            const result = await response.json();
                            console.log('🚀 PROOF SUBMITTED FOR', window.directMiner.currentTarget, 'RESULT:', result);
                            
                            if (result.success && targetType === 'thread') {
                                console.log('🎯 THREAD BUMPED! Points:', result.points);
                                
                                // Update thread score display if on thread page
                                const threadPowDisplay = document.getElementById('thread-pow-number');
                                if (threadPowDisplay) {
                                    const currentScore = parseFloat(threadPowDisplay.textContent) || 0;
                                    const newScore = currentScore + result.points;
                                    threadPowDisplay.textContent = newScore.toFixed(2);
                                    console.log('📈 UPDATED THREAD SCORE:', currentScore, '->', newScore);
                                }
                                
                                // Update any thread badges on listing pages
                                const threadBadges = document.querySelectorAll(`[data-thread-id="${targetId}"] .energy-badge, [data-thread-id="${targetId}"] .catalog-pow-badge`);
                                threadBadges.forEach(badge => {
                                    const currentValue = parseFloat(badge.textContent.replace(/[^\d.]/g, '')) || 0;
                                    const newValue = currentValue + result.points;
                                    badge.textContent = newValue.toFixed(1) + (badge.textContent.includes('⚡') ? '⚡' : ' PoW');
                                });
                            }
                            
                        } catch (error) {
                            console.error('❌ DIRECT SUBMIT ERROR:', error);
                        }
                    }
                }
                
                updateDirectDashboard();
                await new Promise(resolve => setTimeout(resolve, Math.max(20, 100 - window.directMiner.power)));
            }
        }

        function updateDirectDashboard() {
            const elapsed = window.directMiner.startTime ? (Date.now() - window.directMiner.startTime) / 1000 : 0;
            const rate = elapsed > 0 ? Math.floor(window.directMiner.hashCount / elapsed) : 0;
            
            document.getElementById('dashboard-status').textContent = window.directMiner.isActive ? 'DIRECT MINING' : 'IDLE';
            // Show user-friendly target display
            let targetDisplay = window.directMiner.currentTarget || 'direct:slider';
            if (targetDisplay === 'direct:slider') {
                const userName = '{{ session("bitcoin_auth_user")->username ?? "Anonymous" }}';
                targetDisplay = `user: ${userName}`;
            }
            document.getElementById('dashboard-target').textContent = targetDisplay;
            document.getElementById('dashboard-hashrate').textContent = rate + ' H/s';
            document.getElementById('dashboard-proofs').textContent = window.directMiner.proofs.toString();
            
            // Update current hash display
            const hashDisplay = window.directMiner.currentHash ? 
                window.directMiner.currentHash.substring(0, 16) + '...' : 
                'calculating...';
            document.getElementById('dashboard-current-hash').textContent = hashDisplay;
            
            console.log('📊 DIRECT:', window.directMiner.isActive ? 'MINING' : 'IDLE', '| TARGET:', window.directMiner.currentTarget, '| POWER:', window.directMiner.power + '%', '| RATE:', rate, 'H/s', '| HASH:', hashDisplay);
        }

        // MOUSEOVER MINING SYSTEM
        document.addEventListener('DOMContentLoaded', function() {
            // Add mouseover mining to threads
            const threadElements = document.querySelectorAll('[data-thread-id]');
            threadElements.forEach(thread => {
                thread.addEventListener('mouseenter', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        const threadId = thread.dataset.threadId;
                        console.log('🎯 MOUSEOVER THREAD:', threadId);
                        window.directMiner.currentTarget = `thread:${threadId}`;
                        updateDirectDashboard();
                    }
                });
                thread.addEventListener('mouseleave', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        console.log('⏪ MOUSELEAVE THREAD - RESET TO USER');
                        window.directMiner.currentTarget = 'direct:slider';
                        updateDirectDashboard();
                    }
                });
            });

            // Add mouseover mining to images
            const imageElements = document.querySelectorAll('img, .image, [data-image-id]');
            imageElements.forEach(image => {
                image.addEventListener('mouseenter', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        const imageId = image.dataset.imageId || image.src?.split('/').pop() || 'unknown';
                        console.log('🖼️ MOUSEOVER IMAGE:', imageId);
                        window.directMiner.currentTarget = `image:${imageId}`;
                        updateDirectDashboard();
                    }
                });
                image.addEventListener('mouseleave', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        console.log('⏪ MOUSELEAVE IMAGE - RESET TO USER');
                        window.directMiner.currentTarget = 'direct:slider';
                        updateDirectDashboard();
                    }
                });
            });

            // Add mouseover mining to posts/replies
            const postElements = document.querySelectorAll('.post, .reply, [data-post-id]');
            postElements.forEach(post => {
                post.addEventListener('mouseenter', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        const postId = post.dataset.postId || post.id || 'unknown';
                        console.log('💬 MOUSEOVER POST:', postId);
                        window.directMiner.currentTarget = `post:${postId}`;
                        updateDirectDashboard();
                    }
                });
                post.addEventListener('mouseleave', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        console.log('⏪ MOUSELEAVE POST - RESET TO USER');
                        window.directMiner.currentTarget = 'direct:slider';
                        updateDirectDashboard();
                    }
                });
            });

            // Add mouseover mining to users
            const userElements = document.querySelectorAll('.user-name, .author, [data-user-id]');
            userElements.forEach(user => {
                user.addEventListener('mouseenter', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        const userId = user.dataset.userId || user.textContent || 'anonymous';
                        console.log('👤 MOUSEOVER USER:', userId);
                        window.directMiner.currentTarget = `user:${userId}`;
                        updateDirectDashboard();
                    }
                });
                user.addEventListener('mouseleave', () => {
                    if (window.directMiner && window.directMiner.power > 0) {
                        console.log('⏪ MOUSELEAVE USER - RESET TO SELF');
                        window.directMiner.currentTarget = 'direct:slider';
                        updateDirectDashboard();
                    }
                });
            });

            console.log('🎯 MOUSEOVER MINING ENABLED');
        });

        // NUCLEAR MINING FUNCTION - BYPASSES EVERYTHING
        window.nuclearMining = function() {
            console.log('🚀🚀🚀 NUCLEAR MINING ACTIVATED 🚀🚀🚀');
            
            // Create nuclear miner instance
            window.nuclearMiner = {
                isActive: false,
                hashCount: 0,
                startTime: Date.now(),
                nonce: 0
            };
            
            async function nuclearSha256(text) {
                const encoder = new TextEncoder();
                const data = encoder.encode(text);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }
            
            async function nuclearMine() {
                window.nuclearMiner.isActive = true;
                console.log('⚡ NUCLEAR MINING STARTED');
                
                // Update button
                const btn = document.getElementById('nuclear-mine-btn');
                btn.textContent = '⚡ MINING...';
                btn.style.background = 'linear-gradient(135deg, #00FF00 0%, #00AA00 100%)';
                
                // Update dashboard immediately
                document.getElementById('dashboard-status').textContent = 'NUCLEAR MINING';
                document.getElementById('dashboard-target').textContent = 'nuclear:emergency';
                
                while (window.nuclearMiner.isActive) {
                    for (let i = 0; i < 1000; i++) {
                        const data = `nuclear:${Date.now()}:${window.nuclearMiner.nonce}`;
                        const hash = await nuclearSha256(data);
                        window.nuclearMiner.hashCount++;
                        window.nuclearMiner.nonce++;
                        
                        // Update dashboard every 100 hashes
                        if (window.nuclearMiner.hashCount % 100 === 0) {
                            const elapsed = (Date.now() - window.nuclearMiner.startTime) / 1000;
                            const rate = Math.floor(window.nuclearMiner.hashCount / elapsed);
                            document.getElementById('dashboard-hashrate').textContent = rate + ' H/s';
                            document.getElementById('dashboard-current-hash').textContent = hash.substring(0, 16) + '...';
                            console.log('💥', window.nuclearMiner.hashCount, 'hashes,', rate, 'H/s');
                        }
                        
                        // Check for proof
                        if (hash.startsWith('21e8')) {
                            console.log('💎💎💎 NUCLEAR PROOF FOUND! 💎💎💎', hash);
                            
                            try {
                                const response = await fetch('/api/submit-proof', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                                    },
                                    body: JSON.stringify({
                                        hash: hash,
                                        nonce: window.nuclearMiner.nonce - 1,
                                        data: data,
                                        pattern: '21e8',
                                        target_type: 'nuclear',
                                        target_id: 'emergency'
                                    })
                                });
                                
                                const result = await response.json();
                                console.log('🚀 NUCLEAR PROOF RESULT:', result);
                                
                                // Update proof count
                                const currentProofs = parseInt(document.getElementById('dashboard-proofs').textContent || '0');
                                document.getElementById('dashboard-proofs').textContent = (currentProofs + 1).toString();
                                
                            } catch (error) {
                                console.error('💥 NUCLEAR SUBMIT ERROR:', error);
                            }
                        }
                    }
                    
                    // Small delay to prevent browser freeze
                    await new Promise(resolve => setTimeout(resolve, 1));
                }
            }
            
            nuclearMine();
        };
        
        // EMERGENCY DEBUG BUTTON
        setTimeout(() => {
            console.log('🔍 CHECKING ALL MINING SYSTEMS...');
            console.log('emergencyMiner:', window.emergencyMiner ? '✅' : '❌');
            console.log('haichanMiner:', window.haichanMiner ? '✅' : '❌');
            console.log('nuclearMining:', window.nuclearMining ? '✅' : '❌');
            
            if (window.emergencyMiner) {
                console.log('🚨 EMERGENCY MINER IS LOADED');
                console.log('🎯 Current status:', window.emergencyMiner.isActive ? 'MINING' : 'IDLE');
                console.log('⚡ Current power:', window.emergencyMiner.power + '%');
                
                // Force start if not active and power > 0
                if (!window.emergencyMiner.isActive && window.emergencyMiner.power > 0) {
                    console.log('🔥 FORCE STARTING EMERGENCY MINER...');
                    window.emergencyMiner.start();
                }
            } else {
                console.log('❌ EMERGENCY MINER NOT LOADED');
            }
        }, 2000);
    </script>

    <!-- Film Grain & Dashboard CSS -->
    <style>
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes filmGrain {
            0% { transform: translateX(0) translateY(0); }
            10% { transform: translateX(-5%) translateY(10%); }
            20% { transform: translateX(-10%) translateY(5%); }
            30% { transform: translateX(5%) translateY(-10%); }
            40% { transform: translateX(-5%) translateY(15%); }
            50% { transform: translateX(-10%) translateY(5%); }
            60% { transform: translateX(15%) translateY(0%); }
            70% { transform: translateX(0%) translateY(15%); }
            80% { transform: translateX(-15%) translateY(10%); }
            90% { transform: translateX(10%) translateY(5%); }
            100% { transform: translateX(5%) translateY(0%); }
        }

        /* 8mm Film Grain Effect */
        .film-grain::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background-image: 
                radial-gradient(circle, transparent 1px, rgba(255,255,255,.1) 1px),
                radial-gradient(circle, transparent 1px, rgba(0,0,0,.05) 1px);
            background-size: 3px 3px, 7px 7px;
            background-position: 0 0, 3px 3px;
            animation: filmGrain 8s steps(10, end) infinite;
            mix-blend-mode: overlay;
        }

        /* Custom Gradient Slider Styling */
        #dashboard-power-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            border: 2px solid #444B6E;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.8);
            transition: all 0.2s ease;
        }

        #dashboard-power-slider::-webkit-slider-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.9);
        }

        #dashboard-power-slider::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            border: 2px solid #444B6E;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.8);
            transition: all 0.2s ease;
        }

        /* Dashboard hover effects */
        #mini-dash-toggle:hover {
            background: linear-gradient(135deg, rgba(154,184,122,1) 0%, rgba(112,139,117,1) 100%) !important;
        }

        /* Ensure dashboard elements are clickable */
        #dashboard-power-slider, #minimize-dashboard, #close-dashboard, #mini-dash-toggle, #toolbar-mini-dash-toggle {
            pointer-events: auto !important;
            cursor: pointer !important;
            z-index: 1000 !important;
            position: relative !important;
        }

        .film-grain-overlay {
            position: relative;
            overflow: hidden;
        }

        .film-grain-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle, transparent 1px, rgba(255,255,255,.08) 1px),
                radial-gradient(circle, transparent 1px, rgba(0,0,0,.04) 1px);
            background-size: 4px 4px, 8px 8px;
            background-position: 0 0, 4px 4px;
            animation: filmGrain 6s linear infinite;
            pointer-events: none;
            mix-blend-mode: overlay;
            z-index: -1;
        }

        /* Thread expand button styling */
        .thread-expand-btn {
            background: linear-gradient(135deg, rgba(154,184,122,0.8) 0%, rgba(112,139,117,0.8) 100%);
            border: 1px solid rgba(68, 75, 110, 0.4);
            color: #444B6E;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8pt;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(5px);
        }

        .thread-expand-btn:hover {
            background: linear-gradient(135deg, rgba(154,184,122,1) 0%, rgba(112,139,117,1) 100%);
            transform: scale(1.05);
        }
    </style>

    <!-- Theme Switching Script -->
    <script>
        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('haichan-theme') || 'light';
            applyTheme(savedTheme);
        });

        // Switch theme function
        function switchTheme(theme) {
            localStorage.setItem('haichan-theme', theme);
            applyTheme(theme);
        }

        // Apply theme to the page
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);

            // Update button states
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('theme-active');
            });
            document.getElementById('theme-' + theme).classList.add('theme-active');

            // Update button opacity for active state
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.style.opacity = btn.classList.contains('theme-active') ? '1' : '0.7';
            });
        }

        // Profanity blur system
        const profanityWords = [
            'fuck', 'shit', 'damn', 'bitch', 'ass', 'hell', 'crap', 'piss',
            'bastard', 'slut', 'whore', 'cock', 'dick', 'pussy', 'cunt', 'fag',
            'nigger', 'gay', 'homo', 'tranny', 'dyke', 'jew', 'kike',
            'chink', 'spic', 'wetback', 'gook', 'towelhead', 'sand', 'nigga',
            'sperg', 'autismo', 'downie', 'mongoloid', 'retardation', 'spastic',
            'gimp', 'cripple', 'tard'
        ];

        function blurProfanity() {
            const textNodes = [];
            const walker = document.createTreeWalker(
                document.body,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            let node;
            while (node = walker.nextNode()) {
                if (node.parentNode.tagName !== 'SCRIPT' &&
                    node.parentNode.tagName !== 'STYLE' &&
                    !node.parentNode.closest('script, style')) {
                    textNodes.push(node);
                }
            }

            textNodes.forEach(textNode => {
                let text = textNode.textContent;
                let modified = false;

                profanityWords.forEach(word => {
                    const regex = new RegExp(`\\b${word}\\b`, 'gi');
                    if (regex.test(text)) {
                        const parent = textNode.parentNode;
                        const wrapper = document.createElement('span');
                        wrapper.innerHTML = text.replace(regex, `<span class="blurred-profanity">${word}</span>`);

                        while (wrapper.firstChild) {
                            parent.insertBefore(wrapper.firstChild, textNode);
                        }
                        parent.removeChild(textNode);
                        modified = true;
                    }
                });
            });
        }

        // Run profanity blur on page load and after dynamic content updates
        document.addEventListener('DOMContentLoaded', blurProfanity);

        // Add MutationObserver to blur profanity in dynamically added content
        const observer = new MutationObserver(() => {
            blurProfanity();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    </script>

    <!-- Profanity Blur CSS -->
    <style>
        .blurred-profanity {
            filter: blur(4px);
            transition: filter 0.3s ease;
            cursor: pointer;
            border-radius: 3px;
            padding: 1px 2px;
            background: rgba(0,0,0,0.1);
        }

        .blurred-profanity:hover {
            filter: blur(0px);
        }
    </style>

    <!-- Theme Switcher JavaScript -->
    <script>
        // Permanent Day Mode - Green/Tan/Brown Theme
        let currentTheme = 'classic';
        
        // Force classic/day theme on load
        document.documentElement.setAttribute('data-theme', 'classic');
        console.log('🎨 Day mode theme active - green/tan/brown color scheme');

        // Ensure theme stays classic on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.documentElement.setAttribute('data-theme', 'classic');
        });

    </script>


    <script>
        // Site-wide Auto-Dithering System
        let siteDitherEnabled = localStorage.getItem('haichan_site_dither') === 'true';

        function toggleSiteDither() {
            siteDitherEnabled = !siteDitherEnabled;
            localStorage.setItem('haichan_site_dither', siteDitherEnabled.toString());
            updateDitherToggle();
            
            // Apply to all images on current page
            applyDitherToAllImages();
            
            // Show notification
            showDitherNotification();
        }

        function updateDitherToggle() {
            const statusEl = document.getElementById('site-dither-status');
            const toggleEl = document.getElementById('site-dither-toggle');
            if (statusEl && toggleEl) {
                statusEl.textContent = siteDitherEnabled ? 'Auto-Dither: ON' : 'Auto-Dither: OFF';
                toggleEl.style.background = siteDitherEnabled ? 
                    'var(--success-color, #9AB87A)' : 'var(--content-bg, #F5F5DC)';
                toggleEl.style.color = siteDitherEnabled ? '#FFFFFF' : 'var(--text-primary, #3D315B)';
            }
        }

        function applyDitherToAllImages() {
            const images = document.querySelectorAll('img:not([data-dithered])');
            images.forEach(img => {
                char.textContent = String.fromCharCode(0x30A0 + Math.random() * 96);
                char.style.cssText = `
                    position: absolute;
                    color: #00FF00;
                    font-family: monospace;
                    font-size: 14px;
                    left: ${Math.random() * 100}%;
                    top: -20px;
                    animation: matrixFall ${3 + Math.random() * 3}s linear infinite;
                    animation-delay: ${Math.random() * 2}s;
                `;
                matrixContainer.appendChild(char);
            }

            document.body.appendChild(matrixContainer);

            // Add matrix fall animation
            if (!document.getElementById('matrix-style')) {
                const style = document.createElement('style');
                style.id = 'matrix-style';
                style.textContent = `
                    @keyframes matrixFall {
                        to {
                            transform: translateY(100vh);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        function updateNeuralNetworkTheme(theme) {
            // Update neural network colors based on theme
            const canvas = document.getElementById('neural-mining-canvas');
            if (canvas && window.updateNeuralTheme) {
                window.updateNeuralTheme(theme);
            }
        }

        function showThemeNotification(themeName) {
            const notification = document.createElement('div');

            const themeNames = {
                'classic': '🏛️ Classic',
                'day': '☀️ Day',
                'cyberpunk': '🤖 Cyberpunk',
                'vaporwave': '🌈 Vaporwave',
                'matrix': '💊 Matrix',
                'terminal': '💻 Terminal',
                'synthwave': '🌆 Synthwave',
                'ocean': '🌊 Ocean Deep',
                'volcanic': '🌋 Volcanic',
                'arctic': '❄️ Arctic'
            };

            notification.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: var(--content-bg);
                color: var(--text-primary);
                padding: 20px 30px;
                border-radius: 15px;
                border: 2px solid var(--border-color);
                font-size: 18px;
                font-weight: bold;
                z-index: 10001;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                backdrop-filter: blur(10px);
                animation: themeNotification 2s ease-in-out;
            `;

            notification.textContent = `Theme: ${themeNames[themeName] || themeName}`;
            document.body.appendChild(notification);

            setTimeout(() => notification.remove(), 2000);
        }

        // Add notification animation
        const notificationStyle = document.createElement('style');
        notificationStyle.textContent = `
            @keyframes themeNotification {
                0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
                20% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
                30% { transform: translate(-50%, -50%) scale(1); }
                90% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                100% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
            }
        `;
        document.head.appendChild(notificationStyle);

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Apply saved theme
            if (currentTheme !== 'classic') {
                switchTheme(currentTheme);
            }

            // Add keyboard shortcut for theme switching
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'T') {
                    e.preventDefault();
                    const themes = ['classic', 'day', 'cyberpunk', 'vaporwave', 'matrix', 'terminal', 'synthwave', 'ocean', 'volcanic', 'arctic'];
                    const currentIndex = themes.indexOf(currentTheme);
                    const nextTheme = themes[(currentIndex + 1) % themes.length];
                    switchTheme(nextTheme);
                }
            });

            // Theme system removed - using permanent day mode
        });

        console.log('🎨 Day mode theme active - green/tan/brown color scheme');
    </script>


    <script>
        // Site-wide Auto-Dithering System
        let siteDitherEnabled = localStorage.getItem('haichan_site_dither') === 'true';

        function toggleSiteDither() {
            siteDitherEnabled = !siteDitherEnabled;
            localStorage.setItem('haichan_site_dither', siteDitherEnabled.toString());
            updateDitherToggle();
            
            // Apply to all images on current page
            applyDitherToAllImages();
            
            // Show notification
            showDitherNotification();
        }

        function updateDitherToggle() {
            const statusEl = document.getElementById('site-dither-status');
            const toggleEl = document.getElementById('site-dither-toggle');
            if (statusEl && toggleEl) {
                statusEl.textContent = siteDitherEnabled ? 'Auto-Dither: ON' : 'Auto-Dither: OFF';
                toggleEl.style.background = siteDitherEnabled ? 
                    'var(--success-color, #9AB87A)' : 'var(--content-bg, #F5F5DC)';
                toggleEl.style.color = siteDitherEnabled ? '#FFFFFF' : 'var(--text-primary, #3D315B)';
            }
        }

        function applyDitherToAllImages() {
            const images = document.querySelectorAll('img:not([data-dithered])');
            images.forEach(img => {
                if (siteDitherEnabled) {
                    applyDitherEffect(img);
                } else {
                    removeDitherEffect(img);
                }
            });
        }

        function applyDitherEffect(img) {
            if (img.dataset.dithered === 'true') return;
            
            img.style.imageRendering = 'pixelated';
            img.style.filter = 'contrast(1.2) brightness(0.9) saturate(0.8)';
            img.dataset.dithered = 'true';
            
            // Add subtle dithering animation
            img.style.animation = 'ditherPulse 2s infinite alternate ease-in-out';
        }

        function removeDitherEffect(img) {
            img.style.imageRendering = '';
            img.style.filter = '';
            img.style.animation = '';
            img.dataset.dithered = 'false';
        }

        function showDitherNotification() {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: var(--accent-color, #708B75);
                color: #FFFFFF;
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: bold;
                z-index: 10000;
                animation: ditherNotify 1.5s ease-in-out;
                box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            `;
            notification.innerHTML = `🎨 Auto-Dither ${siteDitherEnabled ? 'ENABLED' : 'DISABLED'}`;
            document.body.appendChild(notification);

            setTimeout(() => notification.remove(), 1500);
        }

        // Add CSS animations
        const ditherStyles = document.createElement('style');
        ditherStyles.textContent = `
            @keyframes ditherPulse {
                0% { filter: contrast(1.2) brightness(0.9) saturate(0.8); }
                100% { filter: contrast(1.3) brightness(0.8) saturate(0.9); }
            }
            
            @keyframes ditherNotify {
                0%, 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                10%, 90% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            }
            
            #site-dither-toggle:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
        `;
        document.head.appendChild(ditherStyles);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDitherToggle();
            
            // Apply to existing images if enabled
            if (siteDitherEnabled) {
                setTimeout(applyDitherToAllImages, 100);
            }
            
            // Auto-apply to new images
            const observer = new MutationObserver(mutations => {
                if (siteDitherEnabled) {
                    mutations.forEach(mutation => {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                const newImages = node.querySelectorAll ? 
                                    node.querySelectorAll('img:not([data-dithered])') : [];
                                newImages.forEach(applyDitherEffect);
                                
                                if (node.tagName === 'IMG' && !node.dataset.dithered) {
                                    applyDitherEffect(node);
                                }
                            }
                        });
                    });
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });

        console.log('🎨 Site-wide auto-dithering system initialized');
    </script>
</body>
</html>