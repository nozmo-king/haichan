<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Forum')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <script src="/js/transparent-pow.js"></script>
    <script src="/js/addictive-mining.js"></script>
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

    <!-- Quantum Mining Status Bar -->
    <div id="mining-status-bar" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: linear-gradient(135deg, rgba(154,184,122,0.95) 0%, rgba(112,139,117,0.95) 100%);
        backdrop-filter: blur(10px);
        color: #FFFFEE;
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-size: 11px;
        padding: 10px 20px;
        z-index: 9999;
        border-bottom: 2px solid #444B6E;
        box-shadow: 0 2px 8px rgba(68, 75, 110, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <span id="mining-indicator" style="
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    background: #708B75;
                    border-radius: 50%;
                    animation: pulse 1s infinite;
                "></span>
                <span style="color: #FFFFEE; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">HAICHAN MINING NETWORK</span>
            </div>
            <div style="color: #FFFFEE; font-size: 9px;">
                <span style="color: rgba(255,255,238,0.7);">CURRENT:</span>
                <span id="network-hashrate" style="color: #E8FFE8; font-weight: bold;">0 H/s</span>
            </div>
            <div style="color: #FFFFEE; font-size: 9px;">
                <span style="color: rgba(255,255,238,0.7);">TODAY:</span>
                <span id="network-daily-pow" style="color: #FFE8C8; font-weight: bold;">{{ $dailyProofs ?? 0 }} PoW</span>
            </div>
            <div style="color: #FFFFEE; font-size: 9px;">
                <span style="color: rgba(255,255,238,0.7);">WEEK:</span>
                <span id="network-weekly-pow" style="color: #E8FFE8; font-weight: bold;">{{ $weeklyProofs ?? 0 }} PoW</span>
            </div>
            <div style="color: #FFFFEE; font-size: 9px;">
                <span style="color: rgba(255,255,238,0.7);">GLOBAL PoW:</span>
                <span id="network-total-pow" style="color: #FFD8D8; font-weight: bold;">{{ number_format($totalProofs ?? 0) }}</span>
            </div>
            <div style="color: #FFFFEE; font-size: 9px;">
                <span style="color: rgba(255,255,238,0.7);">MINERS:</span>
                <span id="network-active-miners" style="color: #FFD8D8; font-weight: bold;">{{ $activeSessions ?? 1 }}</span>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <div id="current-mining-hash" style="
                font-family: 'Courier New', monospace;
                font-size: 9px;
                color: rgba(255,255,238,0.7);
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            ">calculating...</div>
            <div style="color: #FFFFEE;">
                <span style="color: rgba(255,255,238,0.8);">DIFFICULTY:</span>
                <span id="current-difficulty" style="color: #FFD8D8; font-weight: bold;">21e8</span>
            </div>
            <select style="
                background: #708B75;
                color: #FFFFEE;
                border: 1px solid #444B6E;
                padding: 4px 6px;
                border-radius: 3px;
                font-size: 9px;
                margin-left: 10px;
                cursor: pointer;
            " onchange="if(this.value) window.location.href=this.value">
                <option value="">🌐 Navigate</option>
                <option value="/catalog">🎯 The MC</option>
                <option value="/library">🖼️ Image Library</option>
                <option value="/mining">⛏️ Mining Dashboard</option>
                <option value="/faq">❓ FAQ & Help</option>
                <optgroup label="📋 Boards">
                @php
                $boardIcons = [
                    'gen' => '💬',
                    'tech' => '💻',
                    'biz' => '💼',
                    'film' => '🎬',
                    'x' => '👽',
                    'lit' => '📚',
                    'meta' => '⚙️',
                    'mu' => '🎵'
                ];
                $allBoards = \App\Models\Board::orderBy('code')->get();
                @endphp
                @foreach($allBoards as $board)
                <option value="/{{ $board->code }}">{{ $boardIcons[$board->code] ?? '📌' }} /{{ $board->code }}/</option>
                @endforeach
                </optgroup>
            </select>
            <button id="mini-dash-toggle" style="
                background: #708B75;
                border: none;
                color: white;
                padding: 4px 8px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 12px;
                margin-left: 5px;
            " title="Open Mining Dashboard">⛏️</button>

        </div>
    </div>

    <!-- Bottom Mining Toolbar (Always Visible) -->
    <div id="bottom-mining-toolbar" style="
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: linear-gradient(135deg, #708B75 0%, #9AB87A 100%);
        color: #FFFFEE;
        font-family: 'Courier New', monospace;
        font-size: 9px;
        padding: 6px 15px;
        z-index: 9998;
        border-top: 1px solid #444B6E;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 -2px 8px rgba(68, 75, 110, 0.2);
    ">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="color: #E8FFE8; font-weight: bold;">MINING STATUS</div>
            <div style="color: rgba(255,255,238,0.9);">Rate: <span id="toolbar-hashrate" style="color: #E8FFE8; font-weight: bold;">0 H/s</span></div>
            <div style="color: rgba(255,255,238,0.9);">Target: <span id="toolbar-target" style="color: #FFD8D8; font-weight: bold;">None</span></div>

            <!-- Navigation Links in Bottom Toolbar -->
            <a href="/catalog" style="
                background: rgba(255,255,238,0.1);
                color: #E8FFE8;
                text-decoration: none;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 8px;
                font-weight: bold;
                border: 1px solid rgba(255,255,238,0.2);
                transition: all 0.2s ease;
            " title="The MC" onmouseover="this.style.background='rgba(255,255,238,0.2)'" onmouseout="this.style.background='rgba(255,255,238,0.1)'">🎯 MC</a>

            <a href="/library" style="
                background: rgba(255,255,238,0.1);
                color: #E8FFE8;
                text-decoration: none;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 8px;
                font-weight: bold;
                border: 1px solid rgba(255,255,238,0.2);
                transition: all 0.2s ease;
            " title="Image Library" onmouseover="this.style.background='rgba(255,255,238,0.2)'" onmouseout="this.style.background='rgba(255,255,238,0.1)'">🖼️ LIB</a>

            <a href="/mining" style="
                background: rgba(255,255,238,0.1);
                color: #E8FFE8;
                text-decoration: none;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 8px;
                font-weight: bold;
                border: 1px solid rgba(255,255,238,0.2);
                transition: all 0.2s ease;
            " title="Mining Dashboard" onmouseover="this.style.background='rgba(255,255,238,0.2)'" onmouseout="this.style.background='rgba(255,255,238,0.1)'">⛏️ MINE</a>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="color: rgba(255,255,238,0.8); font-size: 8px;">Power: <span id="toolbar-power" style="color: #FFE8C8;">IDLE</span></div>

            <!-- Theme Switcher Dropdown -->
            <div style="position: relative; display: flex; align-items: center; gap: 8px;">
                <span style="color: rgba(255,255,238,0.8); font-size: 8px;">Theme:</span>
                <select id="theme-selector" onchange="switchTheme(this.value)" style="
                    background: rgba(255,255,238,0.1);
                    color: #E8FFE8;
                    border: 1px solid rgba(255,255,238,0.2);
                    padding: 2px 6px;
                    font-size: 7px;
                    border-radius: 3px;
                    cursor: pointer;
                    font-weight: bold;
                    outline: none;
                ">
                    <option value="classic">🏛️ Classic</option>
                    <option value="cyberpunk">🤖 Cyberpunk</option>
                    <option value="vaporwave">🌈 Vaporwave</option>
                    <option value="matrix">💊 Matrix</option>
                    <option value="terminal">💻 Terminal</option>
                    <option value="synthwave">🌆 Synthwave</option>
                    <option value="ocean">🌊 Ocean</option>
                    <option value="volcanic">🌋 Volcanic</option>
                    <option value="arctic">❄️ Arctic</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Moveable Mini Dashboard (Hidden by Default) -->
    <div id="mini-dashboard" style="
        position: fixed;
        top: 100px;
        right: 20px;
        width: 320px;
        background: #F5F5DC;
        border: 2px solid #444B6E;
        border-radius: 5px;
        z-index: 10000;
        display: none;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);
    ">
        <!-- Dashboard Header -->
        <div id="dashboard-header" style="
            background: linear-gradient(135deg, #444B6E 0%, #708B75 100%);
            color: #FFFFEE;
            padding: 8px 12px;
            font-size: 10pt;
            font-weight: bold;
            cursor: move;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 3px 3px 0 0;
        ">
            <span>⛏️ HAICHAN MINER</span>
            <div>
                <button id="minimize-dashboard" style="
                    background: transparent;
                    border: 1px solid #FFFFEE;
                    color: #FFFFEE;
                    padding: 1px 4px;
                    margin-right: 3px;
                    cursor: pointer;
                    font-size: 10px;
                    border-radius: 2px;
                " title="Minimize">−</button>
                <button id="close-dashboard" style="
                    background: transparent;
                    border: 1px solid #FFFFEE;
                    color: #FFFFEE;
                    padding: 1px 4px;
                    cursor: pointer;
                    font-size: 10px;
                    border-radius: 2px;
                " title="Close">✕</button>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div id="dashboard-content" style="padding: 15px; font-size: 9pt;">
            <div style="margin-bottom: 10px;">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Mining Target:</div>
                <div id="dashboard-target" style="color: #666; font-size: 8pt;">No target selected</div>
            </div>

            <div style="margin-bottom: 10px;">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Performance:</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 8pt;">
                    <div>Hash Rate: <span id="dashboard-hashrate" style="color: #789922; font-weight: bold;">0 H/s</span></div>
                    <div>Difficulty: <span id="dashboard-difficulty" style="color: #789922; font-weight: bold;">21e8</span></div>
                    <div>Session Proofs: <span id="dashboard-proofs" style="color: #666;">0</span></div>
                </div>
            </div>

            <!-- Rare Hash Notifications -->
            <div id="rare-hash-notifications" style="
                margin-bottom: 15px;
                max-height: 120px;
                overflow-y: auto;
                background: #FFFFEE;
                border: 1px solid #9AB87A;
                border-radius: 4px;
                padding: 8px;
                display: none;
            ">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px; font-size: 8pt;">
                    🎉 RARE HASH DISCOVERIES
                </div>
                <div id="rare-hash-list" style="font-size: 7pt; font-family: 'Courier New', monospace;">
                    <!-- Rare hashes will appear here -->
                </div>
            </div>

            <div style="margin-bottom: 10px;">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Mining Power: <span id="power-level-display">1</span>/10</div>
                <input type="range" id="dashboard-power-slider" min="1" max="10" value="1" style="
                    width: 100%;
                    margin: 5px 0;
                    background: #708B75;
                    border-radius: 5px;
                ">
                <div style="display: flex; justify-content: space-between; font-size: 7pt; color: #666;">
                    <span>1: Whisper (~50 H/s)</span>
                    <span>5: Cruise (~1K H/s)</span>
                    <span>10: OVERDRIVE (~10K H/s)</span>
                </div>
            </div>

            <div>
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Current Hash:</div>
                <div id="dashboard-current-hash" style="
                    font-family: monospace;
                    font-size: 7pt;
                    color: #888;
                    word-break: break-all;
                    background: #FAFAFA;
                    padding: 3px;
                    border: 1px solid #DDD;
                    border-radius: 2px;
                ">calculating...</div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 50px; margin-bottom: 40px;">
        <div class="header">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <h1><a href="/" style="text-decoration: none; color: #3D315B; font-family: 'Nova Cut', serif; font-size: 28px; font-weight: 300; letter-spacing: 2px;" id="header-text">HAICHAN</a></h1>
            </div>
        </div>
        
        @yield('content')
    </div>

    <!-- Simple Haichan Mining System -->
    @vite('resources/js/simple-mining.js')

    <script>
        // Mini Dashboard Controls
        document.addEventListener('DOMContentLoaded', function() {
            const dashboard = document.getElementById('mini-dashboard');
            const dashboardHeader = document.getElementById('dashboard-header');
            const toggleBtn = document.getElementById('mini-dash-toggle');
            const minimizeBtn = document.getElementById('minimize-dashboard');
            const closeBtn = document.getElementById('close-dashboard');
            const dashboardContent = document.getElementById('dashboard-content');

            let isMinimized = false;
            let isDragging = false;
            let dragOffset = { x: 0, y: 0 };

            // Open dashboard
            toggleBtn.addEventListener('click', function() {
                dashboard.style.display = 'block';
                if (isMinimized) {
                    dashboardContent.style.display = 'block';
                    isMinimized = false;
                }
            });

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

            // Update toolbar and dashboard with mining stats
            function updateMiningDisplays() {
                if (window.simpleMiner) {
                    const stats = window.simpleMiner.getStats();

                    // Update bottom toolbar
                    document.getElementById('toolbar-hashrate').textContent = stats.hashRate.toLocaleString() + ' H/s';
                    document.getElementById('toolbar-target').textContent = stats.target || 'None';
                    document.getElementById('toolbar-power').textContent = stats.powerLevel || 'IDLE';

                    // Update dashboard
                    document.getElementById('dashboard-hashrate').textContent = stats.hashRate.toLocaleString() + ' H/s';
                    document.getElementById('dashboard-proofs').textContent = stats.proofsFound || '0';
                    document.getElementById('dashboard-target').textContent = stats.target || 'No target selected';
                    document.getElementById('dashboard-current-hash').textContent = stats.currentHash || 'calculating...';
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

            // Granular power level control (1-10 scale)
            const powerSlider = document.getElementById('dashboard-power-slider');
            const powerDisplay = document.getElementById('power-level-display');

            powerSlider.addEventListener('input', function(e) {
                const level = parseInt(e.target.value);
                powerDisplay.textContent = level;

                // Update power level in mining system
                if (window.simpleMiner && window.simpleMiner.setPowerLevel) {
                    const powerLevels = {
                        1: 'whisper',    // ~50 H/s
                        2: 'quiet',      // ~100 H/s
                        3: 'low',        // ~200 H/s
                        4: 'medium-low', // ~400 H/s
                        5: 'cruise',     // ~1K H/s
                        6: 'active',     // ~2K H/s
                        7: 'high',       // ~3K H/s
                        8: 'turbo',      // ~5K H/s
                        9: 'maximum',    // ~7K H/s
                        10: 'overdrive'  // ~10K H/s
                    };
                    window.simpleMiner.setPowerLevel(powerLevels[level], level);
                }
            });
        });
    </script>

    <!-- Additional CSS for pulse animation -->
    <style>
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Dashboard hover effects */
        #mini-dash-toggle:hover {
            background: #9AB87A !important;
            transform: scale(1.1);
        }

        #minimize-dashboard:hover, #close-dashboard:hover {
            background: rgba(255,255,255,0.2) !important;
        }

        #dashboard-header:hover {
            background: linear-gradient(135deg, #708B75 0%, #9AB87A 100%) !important;
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
        // Theme System
        let currentTheme = localStorage.getItem('haichan-theme') || 'classic';

        function switchTheme(themeName) {
            console.log('🎨 Switching to theme:', themeName);

            // Update body data-theme
            document.body.setAttribute('data-theme', themeName);
            currentTheme = themeName;

            // Save to localStorage
            localStorage.setItem('haichan-theme', themeName);

            // Update theme selector dropdown
            const themeSelector = document.getElementById('theme-selector');
            if (themeSelector) {
                themeSelector.value = themeName;
            }

            // Theme-specific effects
            applyThemeEffects(themeName);

            // Update neural network colors if it exists
            updateNeuralNetworkTheme(themeName);

            // Show theme change notification
            showThemeNotification(themeName);
        }

        function applyThemeEffects(theme) {
            const body = document.body;

            // Remove existing theme classes
            body.classList.remove('matrix-effect', 'cyberpunk-effect', 'vaporwave-effect');

            switch(theme) {
                case 'matrix':
                    body.classList.add('matrix-effect');
                    if (!document.querySelector('.matrix-rain')) {
                        createMatrixRain();
                    }
                    break;
                case 'cyberpunk':
                    body.classList.add('cyberpunk-effect');
                    break;
                case 'vaporwave':
                    body.classList.add('vaporwave-effect');
                    break;
            }
        }

        function createMatrixRain() {
            const matrixContainer = document.createElement('div');
            matrixContainer.className = 'matrix-rain';
            matrixContainer.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: -1;
                overflow: hidden;
            `;

            // Create falling characters
            for (let i = 0; i < 50; i++) {
                const char = document.createElement('div');
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
                    const themes = ['classic', 'cyberpunk', 'vaporwave', 'matrix', 'terminal', 'synthwave', 'ocean', 'volcanic', 'arctic'];
                    const currentIndex = themes.indexOf(currentTheme);
                    const nextTheme = themes[(currentIndex + 1) % themes.length];
                    switchTheme(nextTheme);
                }
            });

            // Initialize theme selector dropdown
            const themeSelector = document.getElementById('theme-selector');
            if (themeSelector && currentTheme !== 'classic') {
                themeSelector.value = currentTheme;
            }
        });

        console.log('🎨 Theme system initialized - Current theme:', currentTheme);
        console.log('💡 Tip: Use Ctrl+Shift+T to cycle through themes!');
    </script>
</body>
</html>