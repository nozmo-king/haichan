<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Imageboard')</title>
    
    <!-- DNS prefetch and preconnect for performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Preload critical CSS -->
    <link rel="preload" href="/css/haichan.css" as="style">
    <!-- Load fonts asynchronously -->
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet"></noscript>
    
    <!-- Critical CSS first with loading callback -->
    <link rel="stylesheet" href="/css/haichan.css" onload="document.body.classList.remove('css-loading')">
    <script>document.body.classList.add('css-loading')</script>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Non-critical JS loaded async -->
    <script src="/js/quick-navigation.js" async></script>
    <script src="/js/wasm-sha256.js" data-wasm-sha256 async></script>
    <script src="/js/content-processor.js" async></script>
    
    <!-- Critical inline CSS to prevent FOUC -->
    <style>
        /* CRITICAL STYLES - Loaded immediately */
        :root {
            --bg-main: rgba(248, 241, 226, 0.92);
            --text-primary: #3d2f1b;
            --border-primary: #b89b6e;
            --font-main: 'Courier New', monospace;
        }
        html, body { 
            margin: 0; 
            padding: 0;
            font-family: var(--font-main);
            background: linear-gradient(135deg, #f8efd5 0%, #e6d2ab 45%, #9ea985 100%);
            background-attachment: fixed;
            color: var(--text-primary);
        }
        /* Essential layout */
        .post, .thread, .nav-links {
            background: var(--bg-main);
            border: 1px solid var(--border-primary);
            margin: 5px;
            padding: 10px;
        }
        
        /* Loading states */
        .loading { opacity: 0.6; }
        .css-loading * { 
            transition: none !important;
            animation: none !important;
        }
        .css-loading::after {
            content: "Loading...";
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            z-index: 9999;
        }
    </style>
    
    <!-- Single Clean CSS -->
    <link rel="stylesheet" href="/css/haichan.css">

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
        
        // Clean mining system initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Layout loaded - Mining system active');
        });
    </script>
</head>
<body data-theme="classic" class="theme-classic">


    <!-- Quantum Header -->
    <div style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%), radial-gradient(circle at 50% 0%, rgba(0, 169, 165, 0.1) 0%, transparent 50%); padding: 48px 0; margin-bottom: 36px; border-bottom: 1px solid var(--border-primary); box-shadow: var(--shadow-elevation); backdrop-filter: blur(12px);">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 24px;">
            <div style="text-align: center; position: relative;">
                <div style="position: absolute; top: 16px; right: 24px; background: var(--bg-mining); color: var(--text-accent); padding: 6px 12px; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; border-radius: 4px; border: 1px solid var(--border-accent); box-shadow: var(--shadow-surface); font-family: var(--font-primary);">QUANTUM</div>
                <h1 style="font-family: var(--font-display); font-size: 36px; color: var(--text-primary); margin: 0 0 16px 0; font-weight: 300; letter-spacing: 2px; text-shadow: 0 2px 8px rgba(144, 194, 231, 0.3); position: relative;">
                    <a href="/" style="text-decoration: none; color: inherit; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block;" onmouseover="this.style.textShadow='0 0 16px rgba(0, 169, 165, 0.4), 0 2px 8px rgba(144, 194, 231, 0.3)'; this.style.transform='translateY(-1px) scale(1.02)'" onmouseout="this.style.textShadow='0 2px 8px rgba(144, 194, 231, 0.3)'; this.style.transform='none'">
                        ⛏️ HAICHAN
                    </a>
                    <div style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 60px; height: 2px; background: linear-gradient(90deg, transparent, var(--accent-primary), transparent); opacity: 0.6;"></div>
                </h1>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 16px 0 0 0; font-weight: 400; font-family: var(--font-secondary); letter-spacing: 0.3px;">
                    Proof-of-Work <span style="color: var(--text-accent); font-weight: 600;">Trading Platform</span> • Mine to Post
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <!-- Admin Updates Panel -->
        @include('components.updates-panel', ['boardCode' => $boardCode ?? null])
        
        <!-- Test Mining Elements (for toolbar testing) -->
        <div style="display: none;">
            <div class="test-mine-post" data-mine-type="post" data-post-id="test1" data-board-code="test">Test Post 1</div>
            <div class="test-mine-thread" data-mine-type="thread" data-thread-id="test2" data-board-code="test">Test Thread 1</div>
            <img class="test-mine-image" data-mine-type="image" data-thread-id="test3" src="#" style="width:1px;height:1px;">
        </div>
        
        @yield('content')
    </div>

    <!-- Mining toolbar removed per user request -->

    <script>
        // Additional initialization for unified mining system
        document.addEventListener('DOMContentLoaded', function() {
            // Force classic theme ONLY
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.className = 'theme-classic';
            
            // QUANTUM BACKGROUND SYSTEM
            const applyQuantumTheme = () => {
                const quantumGradient = `
                    radial-gradient(circle at 20% 20%, rgba(0, 169, 165, 0.15) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(78, 128, 152, 0.1) 0%, transparent 50%),
                    linear-gradient(135deg, #092327 0%, #041419 50%, rgba(11, 83, 81, 0.8) 100%)
                `;
                document.documentElement.style.setProperty('background', quantumGradient, 'important');
                document.body.style.setProperty('background', quantumGradient, 'important');
                
                // Apply quantum transparency to layout containers
                const containersToTransparent = document.querySelectorAll('.container, #app, main, section, .wrapper, .layout');
                containersToTransparent.forEach(el => {
                    el.style.setProperty('background-color', 'transparent', 'important');
                    el.style.setProperty('background-image', 'none', 'important');
                });
                
                // Apply quantum glass morphism to content areas
                const contentAreas = document.querySelectorAll('.post, .thread-preview, .form-container');
                contentAreas.forEach(el => {
                    el.style.setProperty('backdrop-filter', 'blur(8px) saturate(1.1)', 'important');
                });
            };
            
            applyQuantumTheme();
            
            // Maintain quantum theme integrity
            setInterval(applyQuantumTheme, 2000);
            
            console.log('⚡ QUANTUM THEME APPLIED');
        });
    </script>

    <!-- Essential Mining System -->
    <script src="/js/simple-pow.js?v={{ time() }}"></script>
    <!-- mining-miner.js disabled to prevent conflicts with simple-pow.js -->
    <script>
        // Initialize toolbar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toolbar = {
                updateStats: function() {
                    const hashrateEl = document.getElementById('toolbar-hashrate');
                    const sessionsEl = document.getElementById('toolbar-sessions');
                    const proofsEl = document.getElementById('toolbar-proofs');
                    const targetEl = document.getElementById('toolbar-target');
                    const miningStatusEmoji = document.getElementById('mining-status-emoji');
                    
                    if (window.mouseoverMiner) {
                        const miner = window.mouseoverMiner;
                        const stats = miner.stats || {};
                        
                        if (hashrateEl) {
                            const hashrate = stats.hashes > 0 ? Math.floor(stats.hashes / 60) : 0;
                            hashrateEl.textContent = hashrate.toLocaleString() + ' H/s';
                        }
                        
                        if (sessionsEl) {
                            sessionsEl.textContent = miner.enabled ? (miner.currentTarget ? 1 : 0) : 0;
                        }
                        
                        if (proofsEl) {
                            proofsEl.textContent = stats.proofs || 0;
                        }
                        
                        if (targetEl) {
                            if (miner.currentTarget) {
                                const type = miner.currentTarget.dataset?.mineType || 'content';
                                targetEl.textContent = type.charAt(0).toUpperCase() + type.slice(1);
                            } else {
                                targetEl.textContent = miner.enabled ? 'Ready' : 'Disabled';
                            }
                        }
                        
                        // Update mining status based on activity
                        if (miningStatusEmoji) {
                            miningStatusEmoji.textContent = miner.currentTarget ? '⚡' : '💤';
                        }
                    } else {
                        // Fallback if mining brain not loaded yet
                        if (hashrateEl) hashrateEl.textContent = '0 H/s';
                        if (sessionsEl) sessionsEl.textContent = '0';
                        if (proofsEl) proofsEl.textContent = '0';
                        if (targetEl) targetEl.textContent = 'Loading...';
                        if (miningStatusEmoji) miningStatusEmoji.textContent = '💤';
                    }
                },
                
                initToggle: function() {
                    const toggleBtn = document.getElementById('mining-toggle');
                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', function() {
                            if (window.mouseoverMiner) {
                                const miner = window.mouseoverMiner;
                                if (miner.enabled) {
                                    // Disable mining
                                    miner.enabled = false;
                                    this.textContent = 'Auto-Mine: OFF';
                                    this.style.background = 'rgba(220, 53, 53, 0.3)';
                                } else {
                                    // Enable auto-mining
                                    miner.enabled = true;
                                    this.textContent = 'Auto-Mine: ON';
                                    this.style.background = 'rgba(245, 245, 220, 0.1)';
                                    
                                    // Try to start mining on current page elements
                                    const posts = document.querySelectorAll('.post, .thread');
                                    if (posts.length > 0) {
                                        const firstPost = posts[0];
                                        const target = {
                                            id: 'page-' + Date.now(),
                                            displayName: 'Page Content',
                                            element: firstPost,
                                            data: 'mouseover-mining-' + window.location.pathname
                                        };
                                        miner.startMining(target);
                                    }
                                }
                            } else {
                                this.textContent = 'Mining Brain Loading...';
                            }
                        });
                        
                        // Set initial state
                        if (window.mouseoverMiner?.enabled) {
                            toggleBtn.textContent = 'Auto-Mine: ON';
                        } else {
                            toggleBtn.textContent = 'Auto-Mine: OFF';
                            toggleBtn.style.background = 'rgba(220, 53, 53, 0.3)';
                        }
                    }
                }
            };
            
            // Listen for mining system ready event
            window.addEventListener('mouseoverMinerReady', (event) => {
                console.log('✅ Toolbar connected to mining system via event');
                toolbar.updateStats();
                
                // Update stats every 2 seconds
                setInterval(() => toolbar.updateStats(), 2000);
            });
            
            // Fallback: Wait for mining system with polling (in case event missed)
            const waitForMiner = () => {
                if (window.mouseoverMiner) {
                    console.log('✅ Toolbar connected to mining system via polling');
                    toolbar.updateStats();
                    
                    // Update stats every 2 seconds
                    setInterval(() => toolbar.updateStats(), 2000);
                } else {
                    console.log('⏳ Waiting for mining system... (attempt:', Date.now(), ')');
                    setTimeout(waitForMiner, 500);
                }
            };
            
            // Start polling after a delay as fallback
            setTimeout(waitForMiner, 3000);
        });
    </script>
    
    <script>
        // Initialize simple mining system
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔨 Initializing simple mining system...');
            
            if (window.mouseoverMiner) {
                console.log('✅ Mouseover mining system available');
            } else {
                console.log('⏳ Waiting for mouseover mining system...');
            }
        });
            
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
    
    <!-- Quantum Trading Platform Toolbar -->
    <div id="haichan-toolbar" style="position: fixed; bottom: 0; left: 0; right: 0; background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%); border-top: 1px solid var(--border-primary); padding: 12px 24px; z-index: 10000; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-floating); font-family: var(--font-primary); font-size: 12px; backdrop-filter: blur(16px) saturate(1.2);">
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
        
        <!-- Center Section: Mining Info -->
        <div style="display: flex; align-items: center; gap: 25px;">
            <div style="color: #9AB87A; font-size: 12px;">
                <span id="mining-status-emoji">⛏️</span> Hash: <span id="toolbar-hashrate">0 H/s</span>
            </div>
            
            <div style="color: #9AB87A; font-size: 12px;">
                ⚡ Sessions: <span id="toolbar-sessions">0</span>
            </div>
            
            <div style="color: #9AB87A; font-size: 12px;">
                💎 Proofs: <span id="toolbar-proofs">0</span>
            </div>
            
            <div style="color: #9AB87A; font-size: 12px;">
                🎯 Target: <span id="toolbar-target">None</span>
            </div>
        </div>
        
        <!-- Right Section: Links & Anonymous Mode -->
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="/shop" style="color: #F5F5DC; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.color='#FFE4B5'; this.style.transform='translateY(-1px)'" 
               onmouseout="this.style.color='#F5F5DC'; this.style.transform='translateY(0)'">
                🛒 Shop
            </a>
            
            <a href="/mining" style="color: #F5F5DC; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.color='#FFE4B5'; this.style.transform='translateY(-1px)'" 
               onmouseout="this.style.color='#F5F5DC'; this.style.transform='translateY(0)'">
                🎯 Mining Range
            </a>
            
            <button id="toggle-anonymous" style="background: none; border: 1px solid #666; color: #999; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; transition: all 0.2s;"
                    onclick="toggleAnonymousMode()"
                    onmouseover="this.style.borderColor='#9AB87A'; this.style.color='#9AB87A'" 
                    onmouseout="this.style.borderColor='#666'; this.style.color='#999'">
                <span id="anon-emoji">👻</span> Anon
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
    
    /* Emoji Animation Enhancements */
    .emoji-animated {
        display: inline-block;
        transition: transform 0.1s ease;
    }
    
    .emoji-animated:hover {
        transform: scale(1.1);
    }
    
    .emoji-animated-btn {
        position: relative;
        overflow: hidden;
    }
    
    .emoji-animated-btn::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.5s;
        opacity: 0;
    }
    
    .emoji-animated-btn:hover::before {
        animation: shimmer 1.5s infinite;
        opacity: 1;
    }
    
    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }
    
    /* Enhanced glow effect for animated emojis */
    #boards-emoji, #chat-emoji, #library-emoji, #catalog-emoji, 
    #anon-emoji, #mining-status-emoji, #submit-emoji, 
    #send-emoji, #status-emoji, #save-nickname-emoji,
    #create-thread-emoji {
        display: inline-block;
        filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.4));
        transition: all 0.15s ease;
    }
    
    /* Intense strobe animation for high-activity states */
    @keyframes intense-strobe {
        0% { 
            filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8)) brightness(1.2);
            transform: scale(1);
        }
        25% { 
            filter: drop-shadow(0 0 10px rgba(255, 20, 147, 0.8)) brightness(1.4);
            transform: scale(1.1);
        }
        50% { 
            filter: drop-shadow(0 0 8px rgba(0, 255, 255, 0.8)) brightness(1.3);
            transform: scale(1.05);
        }
        75% { 
            filter: drop-shadow(0 0 12px rgba(255, 69, 0, 0.8)) brightness(1.5);
            transform: scale(1.15);
        }
        100% { 
            filter: drop-shadow(0 0 6px rgba(255, 215, 0, 0.8)) brightness(1.2);
            transform: scale(1);
        }
    }
    
    .emoji-intense-strobe {
        animation: intense-strobe 0.8s ease-in-out infinite;
    }
    
    /* Pulse animation for notifications */
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    /* Chat notification pulse */
    #chat-notif {
        animation: pulse 1.5s ease-in-out infinite;
    }
    
    /* Reduced motion accessibility */
    @media (prefers-reduced-motion: reduce) {
        .emoji-animated, #chat-notif {
            animation: none !important;
            transition: none !important;
        }
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
        
        /* Smaller emoji on mobile */
        #boards-emoji, #chat-emoji, #library-emoji, #catalog-emoji, 
        #anon-emoji, #mining-status-emoji {
            font-size: 14px;
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
    
    
    // Chat notifications (placeholder for WebSocket integration)
    // This would connect to your chat system to show unread messages
    </script>
    
    <!-- Emoji Animation System -->
    <script>
    // Haichan Emoji Animation Engine
    class EmojiAnimator {
        constructor() {
            this.animations = new Map();
            this.isEnabled = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.initAnimations();
        }
        
        initAnimations() {
            // Enhanced navigation emoji animations with more dynamic sequences
            this.startAnimation('boards-emoji', ['🏠', '🏡', '🏢', '🏠'], 150);
            this.startAnimation('chat-emoji', ['💬', '💭', '🗨️', '💬'], 200);
            this.startAnimation('library-emoji', ['🖼️', '📸', '🎨', '🖼️'], 175);
            this.startAnimation('catalog-emoji', ['📚', '📖', '📝', '📚'], 180);
            
            // Mining status animation (context-aware)
            this.startMiningAnimation();
            
            // Anonymous mode animation with ghostly theme
            this.startAnimation('anon-emoji', ['👻', '🌫️', '👤', '👻'], 400);
            
            // Enhanced toolbar animations
            this.initToolbarAnimations();
            
            // Add hover pause functionality
            this.addHoverPause();
            
            // Initialize context-aware animations
            this.initContextualAnimations();
        }
        
        startAnimation(elementId, emojiSequence, interval) {
            if (!this.isEnabled) return;
            
            const element = document.getElementById(elementId);
            if (!element) return;
            
            // Clear any existing animation for this element
            if (this.animations.has(elementId)) {
                clearInterval(this.animations.get(elementId).intervalId);
                this.animations.delete(elementId);
            }
            
            let currentIndex = 0;
            const animate = () => {
                if (element && document.contains(element)) {
            const intervalId = setInterval(animate, interval);
            this.animations.set(elementId, { intervalId, element, paused: false, emojiSequence, interval });
        }
        
        stopAnimation(elementId) {
            if (this.animations.has(elementId)) {
                clearInterval(this.animations.get(elementId).intervalId);
                this.animations.delete(elementId);
            }
        }
        
        startMiningAnimation() {
            const element = document.getElementById('mining-status-emoji');
            if (!element || !this.isEnabled) return;
            
            // Check mining system state for context-aware animation
            const getMiningState = () => {
                if (window.mouseoverMiner) {
                    const miner = window.mouseoverMiner;
                    if (miner.currentTarget) {
                        // Check mining intensity
                        const stats = miner.stats || {};
                        const hashrate = stats.hashes > 0 ? Math.floor(stats.hashes / 60) : 0;
                        if (hashrate > 1000) return 'intense';
                        if (hashrate > 100) return 'active';
                        return 'mining';
                    }
                    return miner.enabled ? 'ready' : 'idle';
                }
                return 'idle';
            };
            
            let currentIndex = 0;
            const sequences = {
                intense: ['💎', '💰', '⭐', '🔥', '💫', '⚡', '🌟', '💎'],  // Intense mining strobe with wealth/success theme
                active: ['⛏️', '💎', '⚡', '🔥', '💰', '⭐', '⛏️'],   // Active mining with success indicators
                mining: ['⛏️', '🔨', '💎', '⚡', '⛏️', '🔨'], // Basic mining activity with rewards
                ready: ['⚡', '💎', '⭐', '✨', '⚡', '💎'],    // Ready to mine with sparkle effects
                idle: ['💤', '😴', '🌙', '💭', '💤', '😴']      // Sleeping/inactive with dream theme
            };
            
            const intervals = {
                intense: 60,   // Ultra-fast strobe for maximum visual impact
                active: 100,   // Fast animation with more variety
                mining: 180,   // Medium speed with reward focus
                ready: 250,    // Gentle pulse with anticipation
                idle: 600      // Slow dreamy cycle
            };
            
            const animate = () => {
                if (element && document.contains(element)) {
                    const state = getMiningState();
                    const sequence = sequences[state] || sequences.idle;
                    element.textContent = sequence[currentIndex];
                    currentIndex = (currentIndex + 1) % sequence.length;
                    
                    // Dynamically adjust interval based on state
                    const newInterval = intervals[state] || intervals.idle;
                    if (this.animations.has('mining-status-emoji')) {
                        const animation = this.animations.get('mining-status-emoji');
                        if (animation.currentInterval !== newInterval) {
                            clearInterval(animation.intervalId);
                            animation.intervalId = setInterval(animate, newInterval);
                            animation.currentInterval = newInterval;
                        }
                    }
                } else {
                    this.stopAnimation('mining-status-emoji');
                }
            };
            
            const initialState = getMiningState();
            const initialInterval = intervals[initialState] || intervals.idle;
            const intervalId = setInterval(animate, initialInterval);
            
            this.animations.set('mining-status-emoji', { 
                intervalId, 
                element, 
                paused: false,
                currentInterval: initialInterval
            });
        }
        
        addHoverPause() {
            // Pause animations on hover for better UX
            this.animations.forEach((animation, elementId) => {
                if (animation.element) {
                    animation.element.parentElement.addEventListener('mouseenter', () => {
                        if (!animation.paused) {
                            clearInterval(animation.intervalId);
                            animation.paused = true;
                        }
                    });
                    
                    animation.element.parentElement.addEventListener('mouseleave', () => {
                        if (animation.paused) {
                            // Restart animation after hover
                            setTimeout(() => {
                                if (elementId === 'mining-status-emoji') {
                                    this.startMiningAnimation();
                                } else {
                                    this.startAnimation(elementId, animation.emojiSequence, animation.interval);
                                }
                                animation.paused = false;
                            }, 100);
                        }
                    });
                }
            });
        }
        
        initToolbarAnimations() {
            // Add shop and mining range animations if elements exist
            const shopElement = document.querySelector('[href="/shop"]');
            if (shopElement) {
                const shopEmoji = shopElement.querySelector('span') || shopElement;
                this.startElementAnimation(shopEmoji, ['🛒', '🏪', '💰', '💎', '🏆', '💸', '🛒'], 230);
            }
            
            const miningElement = document.querySelector('[href="/mining"]');
            if (miningElement) {
                const miningEmoji = miningElement.querySelector('span') || miningElement;
                this.startElementAnimation(miningEmoji, ['🎯', '⚡', '💎', '⛏️', '🔥', '💰', '🎯'], 190);
            }
            
            // Enhanced hover effects for toolbar links
            this.addToolbarHoverEffects();
        }
        
        initContextualAnimations() {
            // Board-specific animations based on current URL
            const path = window.location.pathname;
            const boardMatch = path.match(/\/([a-z]+)(?:\/|$)/);
            
            if (boardMatch) {
                const board = boardMatch[1];
                this.applyBoardTheme(board);
            }
            
            // Initialize form button animations if present
            this.initFormAnimations();
            
            // Initialize chat animations if in chat room
            if (path.includes('/chat/')) {
                this.initChatAnimations();
            }
        }
        
        applyBoardTheme(boardCode) {
            const boardThemes = {
                'b': ['🎲', '🎯', '🎪', '🎭', '🎊', '🎈', '🎨', '🎲'],      // Random/General - Party theme
                'g': ['💻', '⚙️', '🔧', '⚡', '🖥️', '📱', '🔋', '💻'],     // Technology - Digital theme
                'pol': ['🏛️', '🗳️', '📊', '🎯', '⚖️', '🏆', '📈', '🏛️'],   // Politics - Government theme
                'art': ['🎨', '🖌️', '🖼️', '✨', '🎭', '🌈', '💫', '🎨'],   // Art - Creative theme
                'ddl': ['📂', '💾', '🔗', '📁', '💿', '📀', '💻', '📂'],   // Downloads - Data theme
                'mu': ['🎵', '🎶', '🎸', '🎤', '🎹', '🎺', '🥁', '🎵'],    // Music - Musical instruments
                'fit': ['💪', '🏋️', '🏃', '⚡', '🤸', '🏅', '🔥', '💪'],   // Fitness - Exercise theme
                'biz': ['💼', '💰', '📈', '💎', '🏆', '💵', '📊', '💼']     // Business - Finance theme
            };
            
            const theme = boardThemes[boardCode] || boardThemes['b'];
            
            // Apply theme to navigation if on board page
            const boardsEmoji = document.getElementById('boards-emoji');
            if (boardsEmoji) {
                this.startAnimation('boards-emoji', theme, 200);
            }
        }
        
        initFormAnimations() {
            // Look for submit buttons and enhance them
            const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach((button, index) => {
                const emojiSpan = button.querySelector('span[id$="-emoji"]');
                if (emojiSpan) {
                    // Button already has emoji system, enhance it
                    this.enhanceSubmitButton(button, emojiSpan);
                }
            });
            
            // Look for reply buttons
            const replyButtons = document.querySelectorAll('button[id*="reply"], button[onclick*="Reply"]');
            replyButtons.forEach(button => {
                this.enhanceReplyButton(button);
            });
        }
        
        initChatAnimations() {
            // Enhance chat-specific elements
            const sendButton = document.getElementById('send-button');
            if (sendButton) {
                this.enhanceChatSendButton(sendButton);
            }
            
            const nicknameButton = document.getElementById('save-nickname');
            if (nicknameButton) {
                this.enhanceNicknameButton(nicknameButton);
            }
        }
        
        enhanceSubmitButton(button, emojiSpan) {
            // Add mouseover effects for submit buttons with intense strobe
            button.addEventListener('mouseenter', () => {
                if (!button.disabled && emojiSpan.textContent !== '⏳') {
                    this.startElementAnimation(emojiSpan, ['⚡', '💎', '✨', '🌟', '💫', '⭐', '🔥', '⚡'], 120);
                }
            });
            
            button.addEventListener('mouseleave', () => {
                this.stopElementAnimation(emojiSpan);
                // Reset to default emoji based on button state
                if (!button.disabled) {
                    emojiSpan.textContent = '⚡';
                }
            });
        }
        
        enhanceReplyButton(button) {
            const emojiSpan = button.querySelector('span') || button;
            
            button.addEventListener('mouseenter', () => {
                if (!button.disabled) {
                    this.startElementAnimation(emojiSpan, ['💬', '💭', '📝', '✨', '🌟', '💫', '💬'], 150);
                }
            });
            
            button.addEventListener('mouseleave', () => {
                this.stopElementAnimation(emojiSpan);
                emojiSpan.textContent = '💬';
            });
        }
        
        enhanceChatSendButton(button) {
            button.addEventListener('mouseenter', () => {
                if (!button.disabled) {
                    const emojiSpan = button.querySelector('#send-emoji');
                    if (emojiSpan) {
                        this.startElementAnimation(emojiSpan, ['💬', '📨', '✈️', '💬'], 200);
                    }
                }
            });
        }
        
        enhanceNicknameButton(button) {
            button.addEventListener('mouseenter', () => {
                const emojiSpan = button.querySelector('#save-nickname-emoji');
                if (emojiSpan) {
                    this.startElementAnimation(emojiSpan, ['✏️', '💾', '✅', '✏️'], 250);
                }
            });
        }
        
        startElementAnimation(element, sequence, interval) {
            if (!this.isEnabled || !element) return;
            
            const elementId = element.id || 'temp-' + Math.random().toString(36);
            element.id = elementId;
            
            let currentIndex = 0;
            const animate = () => {
                if (element && document.contains(element)) {
                    element.textContent = sequence[currentIndex];
                    currentIndex = (currentIndex + 1) % sequence.length;
                } else {
                    this.stopElementAnimation(element);
                }
            };
            
            const intervalId = setInterval(animate, interval);
            this.animations.set(elementId, { intervalId, element, paused: false });
        }
        
        stopElementAnimation(element) {
            if (!element || !element.id) return;
            
            if (this.animations.has(element.id)) {
                const animation = this.animations.get(element.id);
                clearInterval(animation.intervalId);
                this.animations.delete(element.id);
            }
        }
        
        // Method to trigger celebration animations
        celebrateSuccess(elementId, duration = 3000) {
            if (!this.isEnabled) return;
            
            const element = document.getElementById(elementId);
            if (!element) return;
            
            // Stop current animation
            this.stopAnimation(elementId);
            
            // Start intense celebration sequence with visual effects
            this.startAnimation(elementId, ['🎉', '🏆', '⭐', '✨', '🌟', '💫', '🎊', '🥳'], 120);
            element.classList.add('emoji-intense-strobe');
            
            // Return to normal after duration
            setTimeout(() => {
                this.stopAnimation(elementId);
                element.classList.remove('emoji-intense-strobe');
                // Reset to default emoji
                element.textContent = '⚡';
            }, duration);
        }
        
        // Method for loading state animations
        showLoadingState(elementId, loadingSequence = ['⚙️', '🔄', '⏳', '🔍'], interval = 200) {
            if (!this.isEnabled) return;
            
            const element = document.getElementById(elementId);
            if (!element) return;
            
            this.startAnimation(elementId, loadingSequence, interval);
        }
        
        // Method for error state with warning strobe
        showErrorState(elementId, duration = 2000) {
            if (!this.isEnabled) return;
            
            const element = document.getElementById(elementId);
            if (!element) return;
            
            // Stop current animation
            this.stopAnimation(elementId);
            
            // Start error sequence with strobe effect
            this.startAnimation(elementId, ['❌', '💥', '⚠️', '🔥', '💢', '⛔', '❌'], 180);
            element.classList.add('emoji-intense-strobe');
            
            // Return to normal after duration
            setTimeout(() => {
                this.stopAnimation(elementId);
                element.classList.remove('emoji-intense-strobe');
                element.textContent = '❌';
            }, duration);
        }
        
        addToolbarHoverEffects() {
            // Add enhanced hover effects to toolbar links
            const toolbarLinks = document.querySelectorAll('#haichan-toolbar a, #haichan-toolbar button');
            toolbarLinks.forEach(link => {
                const originalText = link.textContent;
                
                link.addEventListener('mouseenter', () => {
                    // Add shimmer effect to buttons
                    if (!link.classList.contains('emoji-animated-btn')) {
                        link.classList.add('emoji-animated-btn');
                    }
                    
                    // Special effects for specific links
                    if (link.href && link.href.includes('/shop')) {
                        const emoji = link.querySelector('span') || link;
                        this.startElementAnimation(emoji, ['🛒', '💰', '💎', '🏆', '✨', '🌟', '🛒'], 140);
                    } else if (link.href && link.href.includes('/mining')) {
                        const emoji = link.querySelector('span') || link;
                        this.startElementAnimation(emoji, ['🎯', '⚡', '🔥', '💥', '💎', '⛏️', '🎯'], 150);
                    }
                });
                
                link.addEventListener('mouseleave', () => {
                    // Clean up animations
                    const emojis = link.querySelectorAll('span[id$="-emoji"]');
                    emojis.forEach(emoji => {
                        this.stopElementAnimation(emoji);
                    });
                });
            });
        }
        
        toggleEnabled() {
            this.isEnabled = !this.isEnabled;
            if (!this.isEnabled) {
                // Stop all animations
                this.animations.forEach(animation => {
                    clearInterval(animation.intervalId);
                });
                this.animations.clear();
            } else {
                // Restart animations
                this.initAnimations();
            }
        }
    }
    
    // Initialize emoji animator when DOM is loaded
    let emojiAnimator;
    document.addEventListener('DOMContentLoaded', function() {
        emojiAnimator = new EmojiAnimator();
        
        // Add accessibility toggle (optional)
        if (localStorage.getItem('emoji-animations-disabled') === 'true') {
            emojiAnimator.toggleEnabled();
        }
    });
    
    // Export for global access
    window.emojiAnimator = emojiAnimator;
    </script>
    
    
    
    <script>
    // Simple mining system status
    // (Legacy mining brain code removed)
    </script>
    
</body>
</html>
