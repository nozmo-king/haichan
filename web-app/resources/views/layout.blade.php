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
                <span style="color: rgba(255,255,238,0.7);">TOTAL HASHES:</span>
                <span id="network-total-hashes" style="color: #E8FFE8; font-weight: bold;">{{ number_format($totalHashes ?? 0) }}</span>
            </div>
            <div style="color: #FFFFEE; font-size: 9px;">
                <span style="color: rgba(255,255,238,0.7);">MINERS:</span>
                <span id="network-active-miners" style="color: #FFD8D8; font-weight: bold;">{{ $activeSessions ?? 1 }}</span>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Image Library Quick Access -->
            <a href="/library" style="
                background: #9AB87A;
                color: #FFFFEE;
                text-decoration: none;
                padding: 4px 8px;
                border-radius: 3px;
                font-size: 9px;
                font-weight: bold;
                border: 1px solid #708B75;
                transition: all 0.2s ease;
            " title="Access Image Library" onmouseover="this.style.background='#708B75'" onmouseout="this.style.background='#9AB87A'">🖼️ LIBRARY</a>

            <div id="current-mining-hash" style="
                font-family: 'Courier New', monospace;
                font-size: 9px;
                color: rgba(255,255,238,0.7);
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            ">21e8000abc123def...</div>
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
            <div style="color: rgba(255,255,238,0.9);">Total: <span id="toolbar-total-hashes" style="color: #E8FFE8; font-weight: bold;">0</span></div>
            <div style="color: rgba(255,255,238,0.9);">Target: <span id="toolbar-target" style="color: #FFD8D8; font-weight: bold;">None</span></div>

            <!-- Image Library Link in Bottom Toolbar -->
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
        </div>
        <div style="color: rgba(255,255,238,0.8); font-size: 8px;">Power: <span id="toolbar-power" style="color: #FFE8C8;">IDLE</span></div>
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
                    <div>Total Hashes: <span id="dashboard-total" style="color: #666;">0</span></div>
                    <div>Valid Proofs: <span id="dashboard-proofs" style="color: #666;">0</span></div>
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
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h1><a href="/" style="text-decoration: none; color: inherit; font-family: 'Nova Cut', serif;">HAICHAN</a></h1>

                <!-- Header Navigation Links -->
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="/library" style="
                        background: linear-gradient(135deg, #9AB87A 0%, #708B75 100%);
                        color: #FFFFEE;
                        text-decoration: none;
                        padding: 8px 16px;
                        border-radius: 5px;
                        font-size: 11px;
                        font-weight: bold;
                        border: 2px solid #444B6E;
                        transition: all 0.3s ease;
                        box-shadow: 0 2px 4px rgba(68, 75, 110, 0.2);
                        font-family: 'Courier New', monospace;
                    " title="Browse and Upload Images" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(68, 75, 110, 0.3)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 4px rgba(68, 75, 110, 0.2)'">🖼️ IMAGE LIBRARY</a>

                    <a href="/mining" style="
                        background: linear-gradient(135deg, #708B75 0%, #444B6E 100%);
                        color: #FFFFEE;
                        text-decoration: none;
                        padding: 8px 16px;
                        border-radius: 5px;
                        font-size: 11px;
                        font-weight: bold;
                        border: 2px solid #444B6E;
                        transition: all 0.3s ease;
                        box-shadow: 0 2px 4px rgba(68, 75, 110, 0.2);
                        font-family: 'Courier New', monospace;
                    " title="Mining Dashboard" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(68, 75, 110, 0.3)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 4px rgba(68, 75, 110, 0.2)'">⛏️ MINING</a>
                </div>
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
                    document.getElementById('toolbar-total-hashes').textContent = stats.totalHashes.toLocaleString();
                    document.getElementById('toolbar-target').textContent = stats.target || 'None';
                    document.getElementById('toolbar-power').textContent = stats.powerLevel || 'IDLE';

                    // Update dashboard
                    document.getElementById('dashboard-hashrate').textContent = stats.hashRate.toLocaleString() + ' H/s';
                    document.getElementById('dashboard-total').textContent = stats.totalHashes.toLocaleString();
                    document.getElementById('dashboard-proofs').textContent = stats.validProofs || '0';
                    document.getElementById('dashboard-target').textContent = stats.target || 'No target selected';
                    document.getElementById('dashboard-current-hash').textContent = stats.currentHash || 'calculating...';
                }
            }

            // Update displays every second
            setInterval(updateMiningDisplays, 1000);

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
</body>
</html>