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
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    @vite('resources/js/simple-mining.js')
    <script>
        // Force complete cache refresh
        console.log('🔄 LAYOUT LOADED - New mining system should initialize');
    </script>
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
<body>
    <!-- Mining Status Bar -->
    <div id="mining-status-bar" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: linear-gradient(135deg, #9AB87A 0%, #708B75 100%);
        color: #FFFFEE;
        font-family: 'Courier New', monospace;
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

            <!-- Theme Switcher -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="color: rgba(255,255,238,0.8); font-size: 8px;">Theme:</span>
                <button id="theme-light" class="theme-btn theme-active" style="
                    background: #FFFFEE;
                    color: #444B6E;
                    border: 1px solid #708B75;
                    padding: 2px 6px;
                    font-size: 7px;
                    border-radius: 3px;
                    cursor: pointer;
                    font-weight: bold;
                " onclick="switchTheme('light')">☀️ LIGHT</button>
                <button id="theme-night" class="theme-btn" style="
                    background: #2A2A2A;
                    color: #CCCCCC;
                    border: 1px solid #555566;
                    padding: 2px 6px;
                    font-size: 7px;
                    border-radius: 3px;
                    cursor: pointer;
                    font-weight: bold;
                " onclick="switchTheme('night')">🌙 NIGHT</button>
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
                headerText.style.transform = '';
                headerText.style.color = '';
                headerText.style.textShadow = '';
                headerText.style.letterSpacing = '';
                headerText.style.filter = '';
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
</body>
</html>