<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Proof-of-Work Imageboard')</title>
    
    <!-- Flash data for points updates -->
    @if(session('points_awarded'))
        <meta name="flash-points_awarded" content="{{ session('points_awarded') }}">
    @endif
    @if(session('total_points'))
        <meta name="flash-total_points" content="{{ session('total_points') }}">
    @endif
    
    <!-- DNS prefetch and preconnect for performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Preload critical CSS -->
    <link rel="preload" href="/css/haichan.css" as="style">
    <!-- Load fonts asynchronously -->
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet" media="print" id="google-fonts">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet"></noscript>
    
    <!-- Single unified stylesheet -->
    <link rel="stylesheet" href="/css/haichan.css" id="main-stylesheet">
    <script nonce="{{ app('csp_nonce') }}">
        document.body.classList.add('css-loading');
        // Handle font loading
        document.getElementById('google-fonts').media = 'all';
        // Handle main CSS loading
        document.getElementById('main-stylesheet').addEventListener('load', function() {
            document.body.classList.remove('css-loading');
        });
    </script>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Non-critical JS loaded async -->
    <script src="/js/quick-navigation.js" nonce="{{ app('csp_nonce') }}" async></script>
    <script src="/js/wasm-sha256.js" data-wasm-sha256 nonce="{{ app('csp_nonce') }}" async></script>
    <script src="/js/content-processor.js" nonce="{{ app('csp_nonce') }}" async></script>
    
    <!-- Critical inline CSS to prevent FOUC -->
    <style nonce="{{ app('csp_nonce') }}">
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
    
    <!-- Duplicate CSS link removed - already loaded above -->

    <style nonce="{{ app('csp_nonce') }}">
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
    
    <script nonce="{{ app('csp_nonce') }}">
        // Layout initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Clean layout loaded');
            
            // Force classic theme ONLY
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.className = 'theme-classic';
            
            // Initialize emoji cycling
            initEmojiCycling();
        });

        function initEmojiCycling() {
            // Wait a bit more to ensure DOM is ready
            setTimeout(() => {
                const radioEmojis = ['📻', '📡', '📺', '💻', '🔊'];
                const lightningEmojis = ['⚡', '🌩️', '💥', '🌊', '🌍', '🔌'];
                
                // Get all emoji elements
                const allEmojis = document.querySelectorAll('.haichan-animated-header .haichan-emoji');
                console.log('🎯 Found emojis:', allEmojis.length);
                console.log('🎯 Emoji elements:', allEmojis);
                
                if (allEmojis.length === 6) {
                    // Left side emojis (first 3)
                    for (let i = 0; i < 3; i++) {
                        let emojiIndex = i;
                        console.log('🟢 Setting up LEFT emoji', i, allEmojis[i].textContent);
                        setInterval(() => {
                            allEmojis[i].textContent = radioEmojis[emojiIndex % radioEmojis.length];
                            emojiIndex++;
                        }, 2000 + (i * 400)); // Slower, staggered timing
                    }
                    
                    // Right side emojis (last 3)
                    for (let i = 3; i < 6; i++) {
                        let emojiIndex = i - 3;
                        console.log('🔵 Setting up RIGHT emoji', i, allEmojis[i].textContent);
                        setInterval(() => {
                            allEmojis[i].textContent = lightningEmojis[emojiIndex % lightningEmojis.length];
                            emojiIndex++;
                        }, 1800 + ((i - 3) * 400)); // Slower, staggered timing
                    }
                } else {
                    console.error('❌ Expected 6 emojis, found:', allEmojis.length);
                }
            }, 500); // Wait 500ms for DOM to be ready
        }
    </script>
</head>
<body data-theme="classic" class="theme-classic">

    <!-- SITE HEADER - AESTHETIC EXTREMIST -->
    <header class="site-header extreme-bordered-text">
        <div class="container">
            <div class="site-brand">
                <a href="/" class="brand-link haichan-animated-header">
                    <span class="haichan-emoji">📻</span>
                    <span class="haichan-emoji">📡</span>
                    <span class="haichan-emoji">📺</span>
                    <span class="haichan-letter">H</span>
                    <span class="haichan-letter">A</span>
                    <span class="haichan-letter">I</span>
                    <span class="haichan-letter">C</span>
                    <span class="haichan-letter">H</span>
                    <span class="haichan-letter">A</span>
                    <span class="haichan-letter">N</span>
                    <span class="haichan-emoji">⚡</span>
                    <span class="haichan-emoji">🌩️</span>
                    <span class="haichan-emoji">💥</span>
                </a>
            </div>
        </div>
    </header>
    
    <style nonce="{{ app('csp_nonce') }}">
        /* Spectacular HAICHAN animated header for main site */
        .haichan-animated-header {
            font-family: 'Nova Cut', cursive;
            font-size: 32px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .haichan-animated-header .haichan-letter {
            display: inline-block;
            color: #000000;
            text-shadow: 
                0 0 3px #00FF00,
                0 0 6px #00FF00,
                0 0 9px #00FF00;
            margin: 0 1px;
            animation-duration: 3s;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }

        .haichan-animated-header .haichan-emoji {
            display: inline-block;
            font-size: 36px;
            margin: 0 8px;
            animation-duration: 2s;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }

        /* Emoji animations with glow effects */
        .haichan-animated-header .haichan-emoji:nth-child(1) { 
            animation-name: emoji-pulse; 
            animation-duration: 2s;
            animation-iteration-count: infinite;
        }
        .haichan-animated-header .haichan-emoji:nth-child(2) { 
            animation-name: emoji-glow; 
            animation-duration: 2.2s;
            animation-delay: 0.2s; 
            animation-iteration-count: infinite;
        }
        .haichan-animated-header .haichan-emoji:nth-child(3) { 
            animation-name: emoji-pulse; 
            animation-duration: 1.8s;
            animation-delay: 0.4s; 
            animation-iteration-count: infinite;
        }
        .haichan-animated-header .haichan-emoji:nth-child(11) { 
            animation-name: emoji-glow; 
            animation-duration: 2s;
            animation-delay: 0.6s; 
            animation-iteration-count: infinite;
        }
        .haichan-animated-header .haichan-emoji:nth-child(12) { 
            animation-name: emoji-pulse; 
            animation-duration: 2.4s;
            animation-delay: 0.8s; 
            animation-iteration-count: infinite;
        }
        .haichan-animated-header .haichan-emoji:nth-child(13) { 
            animation-name: emoji-glow; 
            animation-duration: 1.9s;
            animation-delay: 1.0s; 
            animation-iteration-count: infinite;
        }

        @keyframes emoji-pulse {
            0%, 100% { 
                transform: scale(1);
                filter: drop-shadow(0 0 5px #2E9F82);
            }
            50% { 
                transform: scale(1.1);
                filter: drop-shadow(0 0 15px #2E9F82) drop-shadow(0 0 25px #68C170);
            }
        }

        @keyframes emoji-glow {
            0%, 100% { 
                filter: drop-shadow(0 0 8px #D6EC8C);
            }
            50% { 
                filter: drop-shadow(0 0 20px #D6EC8C) drop-shadow(0 0 30px #2E9F82);
            }
        }

        /* Site-cohesive letter animations using site color palette */
        .haichan-animated-header .haichan-letter:nth-child(4) { animation-name: shimmer-site-green; }
        .haichan-animated-header .haichan-letter:nth-child(5) { animation-name: shimmer-site-pink; animation-delay: 0.2s; }
        .haichan-animated-header .haichan-letter:nth-child(6) { animation-name: shimmer-site-green; animation-delay: 0.4s; }
        .haichan-animated-header .haichan-letter:nth-child(7) { animation-name: shimmer-site-pink; animation-delay: 0.6s; }
        .haichan-animated-header .haichan-letter:nth-child(8) { animation-name: shimmer-site-green; animation-delay: 0.8s; }
        .haichan-animated-header .haichan-letter:nth-child(9) { animation-name: shimmer-site-pink; animation-delay: 1.0s; }
        .haichan-animated-header .haichan-letter:nth-child(10) { animation-name: shimmer-site-green; animation-delay: 1.2s; }

        @keyframes shimmer-site-green {
            0%, 100% { 
                filter: drop-shadow(0 0 3px #68C170) drop-shadow(0 0 6px #68C170) drop-shadow(0 0 9px #68C170);
            }
            25% { 
                filter: drop-shadow(0 0 6px #2E9F82) drop-shadow(0 0 12px #2E9F82) drop-shadow(0 0 18px #2E9F82);
            }
            50% { 
                filter: drop-shadow(0 0 9px #D6EC8C) drop-shadow(0 0 15px #D6EC8C) drop-shadow(0 0 21px #D6EC8C);
            }
            75% { 
                filter: drop-shadow(0 0 6px #68C170) drop-shadow(0 0 12px #68C170) drop-shadow(0 0 18px #68C170);
            }
        }

        @keyframes shimmer-site-pink {
            0%, 100% { 
                filter: drop-shadow(0 0 3px #2E9F82) drop-shadow(0 0 6px #2E9F82) drop-shadow(0 0 9px #2E9F82);
            }
            25% { 
                filter: drop-shadow(0 0 6px #515661) drop-shadow(0 0 12px #515661) drop-shadow(0 0 18px #515661);
            }
            50% { 
                filter: drop-shadow(0 0 9px #D6EC8C) drop-shadow(0 0 15px #D6EC8C) drop-shadow(0 0 21px #D6EC8C);
            }
            75% { 
                filter: drop-shadow(0 0 6px #2E9F82) drop-shadow(0 0 12px #2E9F82) drop-shadow(0 0 18px #2E9F82);
            }
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .haichan-animated-header {
                font-size: 24px;
            }
        }

        /* Clean header layout */
        .site-header {
            padding: 20px 0;
            text-align: center;
        }
        
        .site-brand {
            text-align: center;
        }
    </style>

    <!-- Navigation Toolbar -->
    @if(session('bitcoin_auth_id'))
        @include('components.navigation')
    @endif

    <!-- Main Content Container -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <!-- Admin Updates Panel -->
        @include('components.updates-panel', ['boardCode' => $boardCode ?? null])
        
        @yield('content')
    </div>

    @yield('scripts')
    
    <!-- Global State Management System -->
    <script nonce="{{ app('csp_nonce') }}" src="/js/global-state.js?v={{ time() }}"></script>
    
    <!-- Persistent Toolbar System -->
    <script nonce="{{ app('csp_nonce') }}" src="/js/persistent-toolbar.js?v={{ time() }}"></script>
    
    <!-- Persistent Chat Overlay -->
    <script nonce="{{ app('csp_nonce') }}" src="/js/persistent-chat.js?v={{ time() }}"></script>
    
    <!-- PoW Mining System -->
    
    <script nonce="{{ app('csp_nonce') }}" src="/js/wasm-pow-integration.js" defer></script>
    
</body>
</html>
