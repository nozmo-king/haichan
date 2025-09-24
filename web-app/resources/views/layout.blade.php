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
    @vite('resources/js/simple-mining.js')
</head>
<body data-theme="classic">

    <!-- Mining Status Bar -->
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
                <span style="
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    background: #708B75;
                    border-radius: 50%;
                "></span>
                <span style="color: #FFFFEE; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">HAICHAN MINING NETWORK</span>
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
                @if(session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin)
                <option value="/admin">👑 Admin Control Panel</option>
                @endif
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

    <!-- Bottom Mining Toolbar -->
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

            <!-- Navigation Links -->
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
            " title="The MC">🎯 MC</a>

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
            " title="Image Library">🖼️ LIB</a>

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
            " title="Mining Dashboard">⛏️ MINE</a>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <!-- Theme Switcher -->
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

    <!-- Clean Mini Dashboard -->
    <div id="mini-dashboard" style="
        position: fixed;
        top: 100px;
        right: 20px;
        width: 300px;
        background: #F5F5DC;
        border: 2px solid #444B6E;
        border-radius: 5px;
        z-index: 10000;
        display: none;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);
    ">
        <!-- Simple Header -->
        <div style="background: #444B6E; color: #FFFFEE; padding: 8px; font-weight: bold; font-size: 10px; display: flex; justify-content: space-between; align-items: center;">
            <div>⛏️ MINING</div>
            <button id="close-dashboard" style="background: transparent; border: none; color: #FFFFEE; cursor: pointer; padding: 0; font-size: 10px;" title="Close">✕</button>
        </div>

        <!-- Clean Content -->
        <div style="padding: 15px; font-size: 9pt;">
            <div style="margin-bottom: 10px;">
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Target:</div>
                <div id="dashboard-target" style="color: #666; font-size: 8pt;">No target</div>
            </div>

            <div>
                <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Hash Rate:</div>
                <div id="dashboard-hashrate" style="color: #789922; font-weight: bold;">0 H/s</div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 50px; margin-bottom: 40px;">
        <div class="header">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <h1><a href="/" style="text-decoration: none; color: #3D315B; font-family: 'Nova Cut', serif; font-size: 28px; font-weight: 300; letter-spacing: 2px;">HAICHAN</a></h1>
            </div>
        </div>

        @yield('content')
    </div>

    <script>
        // Simple dashboard controls
        document.addEventListener('DOMContentLoaded', function() {
            const dashboard = document.getElementById('mini-dashboard');
            const closeBtn = document.getElementById('close-dashboard');
            const toggleBtn = document.getElementById('mini-dash-toggle');

            // Toggle dashboard
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    if (dashboard) {
                        dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
                    }
                });
            }

            // Close dashboard
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    if (dashboard) dashboard.style.display = 'none';
                });
            }

            // Keyboard shortcut to toggle dashboard (Ctrl+D)
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'd') {
                    e.preventDefault();
                    if (dashboard) {
                        dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
                    }
                }
            });
        });

        // Theme System
        let currentTheme = localStorage.getItem('haichan-theme') || 'classic';

        function switchTheme(themeName) {
            document.body.setAttribute('data-theme', themeName);
            currentTheme = themeName;
            localStorage.setItem('haichan-theme', themeName);

            const themeSelector = document.getElementById('theme-selector');
            if (themeSelector) {
                themeSelector.value = themeName;
            }
        }

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (currentTheme !== 'classic') {
                switchTheme(currentTheme);
            }

            const themeSelector = document.getElementById('theme-selector');
            if (themeSelector && currentTheme !== 'classic') {
                themeSelector.value = currentTheme;
            }
        });
    </script>

</body>
</html>