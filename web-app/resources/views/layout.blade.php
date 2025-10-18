<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Imageboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/quick-navigation.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <script src="/js/quick-navigation.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="/js/wasm-sha256.js" data-wasm-sha256></script>
    <script src="/js/content-processor.js"></script>
    <style>
        /* FORCE TAN BACKGROUND - SELECTIVE */
        html, body {
            background: linear-gradient(135deg, #f8efd5 0%, #e6d2ab 45%, #9ea985 100%) !important;
            background-attachment: fixed !important;
        }
        
        /* Content areas get proper backgrounds */
        .post, .haichan-form-panel, .nav-links, input, textarea, button, select {
            background: #F5F5DC !important;
            border: 1px solid #708B75 !important;
        }
        
        /* Form sections */
        div[style*="background: var(--primary-bg)"] {
            background: #F5F5DC !important;
        }
        
        div[style*="background: var(--content-bg)"] {
            background: #FFFACD !important;
        }
        
        /* Force mining dashboard tan colors */
        #mining-dashboard, #enhanced-mining-dashboard {
            background: #F5F5DC !important;
            border-color: #708B75 !important;
        }
        
        #mining-dashboard .dashboard-header, #enhanced-mining-dashboard .dashboard-header {
            background: linear-gradient(135deg, #708B75, #9AB87A) !important;
        }
        
        .glow-text {
            color: #9AB87A;
            text-shadow:
                0 0 5px #9AB87A,
                0 0 10px #9AB87A,
                0 0 15px #9AB87A,
                0 0 20px #9AB87A;
            animation: glow-pulse 2s ease-in-out infinite alternate;
        }

        @keyframes glow-pulse {
            from {
                text-shadow:
                    0 0 5px #9AB87A,
                    0 0 10px #9AB87A,
                    0 0 15px #9AB87A,
                    0 0 20px #9AB87A;
            }
            to {
                text-shadow:
                    0 0 2px #9AB87A,
                    0 0 5px #9AB87A,
                    0 0 8px #9AB87A,
                    0 0 12px #9AB87A;
            }
        }

        /* Admin/Mod glow effects */
        .admin-glow span, .mod-glow span {
            animation: admin-glow-pulse 1.5s ease-in-out infinite alternate;
        }

        @keyframes admin-glow-pulse {
            from {
                text-shadow:
                    0 0 10px #00ff00,
                    0 0 20px #00ff00,
                    0 0 30px #00ff00,
                    0 0 40px #00ff00;
            }
            to {
                text-shadow:
                    0 0 5px #00ff00,
                    0 0 10px #00ff00,
                    0 0 15px #00ff00,
                    0 0 20px #00ff00;
            }
        }
    </style>
    <script>
        // Unified Mining System Integration
        console.log('🚀 HAICHAN UNIFIED MINING - Loading...');
        
        // Legacy mining system removed - using haichan-unified.js
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Layout loaded - Unified mining system active');
            
            // Cleanup any old mining elements that might conflict
            const oldMiningIndicators = document.querySelectorAll('.mining-indicator, .mining-status');
            oldMiningIndicators.forEach(el => el.remove());
        });
    </script>
</head>
<body data-theme="classic" class="theme-classic">


    <!-- Modern Header -->
    <div style="background: linear-gradient(135deg, #FFFACD, #F5F5DC); padding: 20px 0; margin-bottom: 30px; border-bottom: 2px solid #708B75; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div style="text-align: center;">
                <h1 style="font-family: 'Nova Cut', serif; font-size: 42px; color: #3D315B; margin: 0 0 10px 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <a href="/" style="text-decoration: none; color: #3D315B;">
                        ⛏️ HAICHAN
                    </a>
                </h1>
                <p style="color: #6B7A6B; font-size: 14px; margin: 0;">
                    Proof-of-Work <span class="glow-text">Imageboard</span> • Mine to Post
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <!-- Admin Updates Panel -->
        @include('components.updates-panel', ['boardCode' => $boardCode ?? null])
        
        @yield('content')
    </div>

    <!-- Mining Toolbar (for compatibility) -->
    <div id="mining-toolbar" style="display: none;">
        <div id="toolbar-target">None</div>
    </div>

    <script>
        // Additional initialization for unified mining system
        document.addEventListener('DOMContentLoaded', function() {
            // Force classic theme ONLY
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.className = 'theme-classic';
            
            // SELECTIVE TAN BACKGROUND OVERRIDE
            const forceBackground = () => {
                const tanGradient = 'linear-gradient(135deg, #f8efd5 0%, #e6d2ab 45%, #9ea985 100%)';
                document.documentElement.style.setProperty('background', tanGradient, 'important');
                document.body.style.setProperty('background', tanGradient, 'important');
                
                // Only force transparent on main containers, not content areas
                const containersToTransparent = document.querySelectorAll('.container, #app, main, section, .wrapper, .layout');
                containersToTransparent.forEach(el => {
                    el.style.setProperty('background-color', 'transparent', 'important');
                    el.style.setProperty('background-image', 'none', 'important');
                });
            };
            
            forceBackground();
            
            // Re-apply every second to override any dynamic changes
            setInterval(forceBackground, 1000);
            
            console.log('🎯 NUCLEAR TAN BACKGROUND APPLIED');
        });
    </script>

    <!-- Mining System Scripts -->
    <script src="/js/fallback-mining.js?v={{ time() }}"></script>
    <script src="/js/simple-pow.js?v={{ time() }}"></script>
    <script src="/js/mining-brain.js?v={{ time() }}"></script>
    <script src="/js/global-mouseover-mining.js?v={{ time() }}"></script>
    
    <script>
        // Initialize mining systems and create compatibility layer
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔨 Initializing mining systems...');
            
            // Initialize Simple PoW
            if (typeof SimpleProofOfWork !== 'undefined') {
                window.simplePoW = new SimpleProofOfWork();
                console.log('✅ Simple PoW initialized');
            }
            
            // Initialize mining brain compatibility
            if (typeof HaichanMiningBrain !== 'undefined') {
                try {
                    window.haichanMiningBrain = new HaichanMiningBrain();
                    console.log('✅ Mining Brain initialized');
                } catch (error) {
                    console.warn('Mining Brain initialization failed:', error);
                    // Fallback to simple PoW
                    if (window.simplePoW) {
                        window.haichanMiningBrain = window.simplePoW;
                        console.log('✅ Using Simple PoW as Mining Brain fallback');
                    }
                }
            } else if (window.simplePoW) {
                // Use simple PoW as mining brain if brain not available
                window.haichanMiningBrain = window.simplePoW;
                console.log('✅ Using Simple PoW as Mining Brain');
            }
            
            // Add mouseover mining status indicator
            setTimeout(() => {
                if (window.globalMouseoverMining) {
                    createMiningStatusIndicator();
                }
            }, 1000);
        });
        
        function createMiningStatusIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'mouseover-mining-indicator';
            indicator.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(112, 139, 117, 0.9);
                color: #F5F5DC;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 12px;
                font-family: monospace;
                z-index: 9999;
                transition: all 0.3s ease;
                cursor: pointer;
                border: 1px solid #708B75;
            `;
            indicator.innerHTML = '🖱️ Mouseover Mining: Ready';
            indicator.title = 'Click to toggle mouseover mining';
            
            indicator.addEventListener('click', () => {
                if (window.globalMouseoverMining) {
                    window.globalMouseoverMining.toggle();
                    updateMiningIndicator();
                }
            });
            
            document.body.appendChild(indicator);
            
            // Update indicator every 2 seconds
            setInterval(updateMiningIndicator, 2000);
        }
        
        function updateMiningIndicator() {
            const indicator = document.getElementById('mouseover-mining-indicator');
            if (!indicator || !window.globalMouseoverMining) return;
            
            const stats = window.globalMouseoverMining.getStats();
            
            if (!stats.isActive) {
                indicator.innerHTML = '🖱️ Mining: Disabled';
                indicator.style.background = 'rgba(220, 53, 69, 0.9)';
                return;
            }
            
            if (stats.currentTarget) {
                indicator.innerHTML = `⛏️ Mining ${stats.currentTarget} @ ${stats.currentPower} H/s`;
                indicator.style.background = 'rgba(40, 167, 69, 0.9)';
            } else {
                indicator.innerHTML = `🖱️ Ready @ ${stats.currentPower} H/s | ${stats.totalPoints}⚡`;
                indicator.style.background = 'rgba(112, 139, 117, 0.9)';
            }
        }
    </script>
    
    @if(session('download_key') && session('username'))
    <script>
    // Auto-download key file after successful registration
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const keyContent = atob('{{ session('download_key') }}');
            const username = '{{ session('username') }}';
            
            // Create and trigger download
            const blob = new Blob([keyContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = username + '_haichan.key';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            console.log('🔑 Key file downloaded for user:', username);
        } catch (error) {
            console.error('Key download failed:', error);
        }
    });
    </script>
    @endif
    
    <!-- Global Bottom Toolbar -->
    <div id="haichan-toolbar" style="position: fixed; bottom: 0; left: 0; right: 0; background: linear-gradient(to right, #2c2c2c, #1a1a1a); border-top: 2px solid #708B75; padding: 8px 20px; z-index: 10000; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 -2px 10px rgba(0,0,0,0.3); font-family: monospace; font-size: 13px;">
        <!-- Left Section: User Info -->
        <div style="display: flex; align-items: center; gap: 20px;">
            @if(session('bitcoin_auth_id'))
                @php
                    $user = session('bitcoin_auth_user');
                    $isAdmin = $user->is_admin ?? false;
                    $isMod = $user->is_moderator ?? false;
                @endphp
                <a href="{{ route('user.profile', session('bitcoin_auth_id')) }}" 
                   @if($isAdmin || $isMod)
                       class="toolbar-link user-link {{ $isAdmin ? 'admin-glow' : 'mod-glow' }}"
                       style="color: #00ff00; text-decoration: none; display: flex; align-items: center; gap: 8px;"
                   @else
                       class="toolbar-link user-link"
                       style="color: #9AB87A; text-decoration: none; display: flex; align-items: center; gap: 8px;"
                       onmouseover="this.style.color='#b8d99a'" onmouseout="this.style.color='#9AB87A'"
                   @endif>
                    <span style="font-size: 16px;">👤</span> 
                    <span style="font-weight: bold; @if($isAdmin || $isMod) text-shadow: 0 0 10px #00ff00, 0 0 20px #00ff00, 0 0 30px #00ff00; @endif">
                        {{ $user->username }}
                    </span>
                </a>
                <a href="{{ route('user.profile.edit') }}" style="color: #F5F5DC; text-decoration: none; padding: 4px 12px; background: #708B75; border-radius: 4px; font-size: 11px; transition: all 0.2s;"
                   onmouseover="this.style.background='#5a7860'" onmouseout="this.style.background='#708B75'">
                    ✏️ Edit
                </a>
                <div style="color: #9AB87A; font-size: 12px;">
                    ⚡ {{ number_format(session('bitcoin_auth_user')->total_pow_points ?? 0) }} pts
                </div>
            @else
                <a href="/auth/login" style="color: #9AB87A; text-decoration: none;"
                   onmouseover="this.style.color='#b8d99a'" onmouseout="this.style.color='#9AB87A'">
                    🔐 Login
                </a>
            @endif
        </div>
        
        <!-- Center Section: Quick Links -->
        <div style="display: flex; align-items: center; gap: 25px;">
            <a href="/" style="color: #F5F5DC; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.color='#FFE4B5'; this.style.transform='translateY(-1px)'" 
               onmouseout="this.style.color='#F5F5DC'; this.style.transform='translateY(0)'">
                <span style="font-size: 16px;">🏠</span> Boards
            </a>
            
            <a href="/chat" style="color: #F5F5DC; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.color='#FFE4B5'; this.style.transform='translateY(-1px)'" 
               onmouseout="this.style.color='#F5F5DC'; this.style.transform='translateY(0)'">
                <span style="font-size: 16px;">💬</span> Chat
                <span id="chat-notif" style="background: #FF6B35; color: white; padding: 1px 6px; border-radius: 10px; font-size: 10px; display: none;">0</span>
            </a>
            
            <a href="/library" style="color: #F5F5DC; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.color='#FFE4B5'; this.style.transform='translateY(-1px)'" 
               onmouseout="this.style.color='#F5F5DC'; this.style.transform='translateY(0)'">
                <span style="font-size: 16px;">🖼️</span> Image Library
            </a>
            
            <a href="/catalog" style="color: #F5F5DC; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.color='#FFE4B5'; this.style.transform='translateY(-1px)'" 
               onmouseout="this.style.color='#F5F5DC'; this.style.transform='translateY(0)'">
                <span style="font-size: 16px;">📚</span> The MC
            </a>
        </div>
        
        <!-- Right Section: Mining Status -->
        <div style="display: flex; align-items: center; gap: 15px;">
            <div id="toolbar-mining-status" style="color: #9AB87A; font-size: 12px;">
                <span id="toolbar-hashrate">0 H/s</span> | 
                <span id="toolbar-hashes">0 hashes</span>
            </div>
            <button onclick="toggleMiningDashboard()" style="background: none; border: 1px solid #666; color: #999; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; transition: all 0.2s;"
                    onmouseover="this.style.borderColor='#9AB87A'; this.style.color='#9AB87A'" 
                    onmouseout="this.style.borderColor='#666'; this.style.color='#999'">
                ⛏️ Dashboard
            </button>
            <button id="toggle-anonymous" style="background: none; border: 1px solid #666; color: #999; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; transition: all 0.2s;"
                    onclick="toggleAnonymousMode()"
                    onmouseover="this.style.borderColor='#9AB87A'; this.style.color='#9AB87A'" 
                    onmouseout="this.style.borderColor='#666'; this.style.color='#999'">
                👻 Anon
            </button>
        </div>
    </div>
    
    <!-- Add padding to body for toolbar -->
    <style>
    body {
        padding-bottom: 50px !important;
    }
    
    /* Anonymous mode styles */
    body.anonymous-mode {
        filter: invert(1) hue-rotate(180deg) !important;
        background: #000 !important;
    }
    
    body.anonymous-mode img,
    body.anonymous-mode video,
    body.anonymous-mode iframe {
        filter: invert(1) hue-rotate(180deg) !important;
    }
    
    body.anonymous-mode #haichan-toolbar {
        background: linear-gradient(to right, #d3d3d3, #e5e5e5) !important;
    }
    
    /* Toolbar responsive */
    @media (max-width: 768px) {
        #haichan-toolbar {
            flex-wrap: wrap;
            padding: 6px 10px;
            font-size: 11px;
        }
        
        #haichan-toolbar > div {
            gap: 10px !important;
        }
    }
    </style>
    
    <script>
    // Check anonymous mode on load
    if (sessionStorage.getItem('anonymous_mode') === 'true') {
        document.body.classList.add('anonymous-mode');
        document.getElementById('toggle-anonymous').style.background = '#333';
        document.getElementById('toggle-anonymous').style.color = '#fff';
        document.getElementById('toggle-anonymous').style.borderColor = '#fff';
    }
    
    // Toggle anonymous mode
    function toggleAnonymousMode() {
        const isAnon = document.body.classList.toggle('anonymous-mode');
        sessionStorage.setItem('anonymous_mode', isAnon);
        
        const btn = document.getElementById('toggle-anonymous');
        if (isAnon) {
            btn.style.background = '#333';
            btn.style.color = '#fff';
            btn.style.borderColor = '#fff';
        } else {
            btn.style.background = 'none';
            btn.style.color = '#999';
            btn.style.borderColor = '#666';
        }
    }
    
    // Update mining stats in toolbar
    setInterval(() => {
        if (window.globalMouseoverMining && window.globalMouseoverMining.stats) {
            const stats = window.globalMouseoverMining.stats;
            document.getElementById('toolbar-hashrate').textContent = stats.hashrate || '0 H/s';
            document.getElementById('toolbar-hashes').textContent = (stats.totalHashes || 0).toLocaleString() + ' hashes';
        }
    }, 1000);
    
    // Chat notifications (placeholder for WebSocket integration)
    // This would connect to your chat system to show unread messages
    </script>
    
    <!-- Include Mining Dashboard -->
    @include('components.mining-dashboard')
    
    <!-- Enhanced Mining Dashboard (mini dash) -->
    <div id="enhanced-mining-dashboard" style="position: fixed; bottom: 60px; right: 20px; background: rgba(26, 26, 26, 0.95); border: 2px solid #708B75; border-radius: 8px; padding: 15px; min-width: 280px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); font-family: monospace; font-size: 12px; display: none; z-index: 9999;">
        <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #708B75;">
            <h4 style="margin: 0; color: #9AB87A; font-size: 14px;">⛏️ Mining Dashboard</h4>
            <button onclick="toggleMiningDashboard()" style="background: none; border: none; color: #666; cursor: pointer; font-size: 16px;">×</button>
        </div>
        
        <div id="mining-stats" style="color: #F5F5DC;">
            <div style="margin-bottom: 8px;">
                <span style="color: #9AB87A;">Hashrate:</span> 
                <span id="current-hashrate">0 H/s</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span style="color: #9AB87A;">Total Hashes:</span> 
                <span id="total-hashes">0</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span style="color: #9AB87A;">Valid Proofs:</span> 
                <span id="valid-proofs">0</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span style="color: #9AB87A;">Current Target:</span> 
                <span id="current-target" style="font-family: monospace;">None</span>
            </div>
            <div id="best-hash" style="margin-bottom: 8px;">
                <span style="color: #9AB87A;">Best Hash:</span> 
                <span id="best-hash-value" style="font-family: monospace; font-size: 10px; color: #FFD700;">None</span>
            </div>
        </div>
        
        <div id="mining-activity" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #333;">
            <div style="color: #666; font-size: 11px;">Hover over mineable content to start</div>
        </div>
    </div>
    
    <script>
    // Toggle mining dashboard visibility
    function toggleMiningDashboard() {
        const dashboard = document.getElementById('enhanced-mining-dashboard');
        dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
        localStorage.setItem('mining-dashboard-visible', dashboard.style.display !== 'none');
    }
    
    // Show dashboard if it was previously visible
    if (localStorage.getItem('mining-dashboard-visible') === 'true') {
        document.getElementById('enhanced-mining-dashboard').style.display = 'block';
    }
    
    // Listen for mining events to update dashboard
    document.addEventListener('mining-update', function(e) {
        if (e.detail) {
            document.getElementById('current-hashrate').textContent = e.detail.hashrate || '0 H/s';
            document.getElementById('total-hashes').textContent = e.detail.totalHashes || '0';
            document.getElementById('valid-proofs').textContent = e.detail.validProofs || '0';
            if (e.detail.currentTarget) {
                document.getElementById('current-target').textContent = e.detail.currentTarget;
            }
            if (e.detail.bestHash) {
                document.getElementById('best-hash-value').textContent = e.detail.bestHash.substring(0, 16) + '...';
            }
        }
    });
    
    // Ensure mining brain is available globally
    window.addEventListener('load', function() {
        // Try multiple times to ensure mining brain is initialized
        let attempts = 0;
        const tryInitMiningBrain = () => {
            attempts++;
            
            // Check if we need to create the mining brain instance
            if (!window.haichanMiningBrain && window.HaichanMiningBrain) {
                try {
                    console.log('Manually initializing mining brain (attempt ' + attempts + ')...');
                    window.haichanMiningBrain = new HaichanMiningBrain();
                    console.log('✅ Mining brain initialized successfully');
                } catch (error) {
                    console.error('Failed to initialize mining brain:', error);
                    if (attempts < 5) {
                        setTimeout(tryInitMiningBrain, 500);
                    }
                }
            }
            
            // Log available mining systems
            console.log('Mining systems status (attempt ' + attempts + '):', {
                HaichanMiningBrain: !!window.HaichanMiningBrain,
                haichanMiningBrain: !!window.haichanMiningBrain,
                simplePoW: !!window.simplePoW,
                haichanMiner: !!window.haichanMiner,
                fallbackMining: !!window.fallbackMining
            });
            
            // If still no mining brain after several attempts, retry
            if (!window.haichanMiningBrain && attempts < 5) {
                setTimeout(tryInitMiningBrain, 500);
            }
        };
        
        // Start initialization attempts
        setTimeout(tryInitMiningBrain, 100);
    });
    </script>
    
</body>
</html>
