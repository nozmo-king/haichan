<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Proof-of-Work Imageboard')</title>
    
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
    
    <!-- UI Tokens for consistent design system -->
    <link rel="stylesheet" href="/assets/ui-tokens.css">
    <!-- Design System Tokens -->
    <link rel="stylesheet" href="/css/design-tokens.css">
    
    <!-- Critical CSS first with loading callback -->
    <link rel="stylesheet" href="/css/haichan.css" onload="document.body.classList.remove('css-loading')">
    <link rel="stylesheet" href="/css/board-layout.css">
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
        // Layout initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Clean layout loaded');
            
            // Force classic theme ONLY
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.className = 'theme-classic';
        });
    </script>
</head>
<body data-theme="classic" class="theme-classic">

    <!-- Site Header -->
    <div style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%), radial-gradient(circle at 50% 0%, rgba(0, 169, 165, 0.1) 0%, transparent 50%); padding: 48px 0; margin-bottom: 36px; border-bottom: 1px solid var(--border-primary); box-shadow: var(--shadow-elevation); backdrop-filter: blur(12px);">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 24px;">
            <div style="text-align: center; position: relative;">
                <h1 style="font-family: var(--font-display); font-size: 36px; color: var(--text-primary); margin: 0 0 16px 0; font-weight: 300; letter-spacing: 2px; text-shadow: 0 2px 8px rgba(144, 194, 231, 0.3); position: relative;">
                    <a href="/" style="text-decoration: none; color: inherit; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: inline-block;" onmouseover="this.style.textShadow='0 0 16px rgba(0, 169, 165, 0.4), 0 2px 8px rgba(144, 194, 231, 0.3)'; this.style.transform='translateY(-1px) scale(1.02)'" onmouseout="this.style.textShadow='0 2px 8px rgba(144, 194, 231, 0.3)'; this.style.transform='none'">
                        ⛏️ HAICHAN
                    </a>
                    <div style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 60px; height: 2px; background: linear-gradient(90deg, transparent, var(--accent-primary), transparent); opacity: 0.6;"></div>
                </h1>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 16px 0 0 0; font-weight: 400; font-family: var(--font-secondary); letter-spacing: 0.3px;">
                    A Proof-of-Work <span style="color: var(--text-accent); font-weight: 600;">Imageboard</span>
                </p>
            </div>
        </div>
    </div>

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
    <script src="/js/global-state.js?v={{ time() }}"></script>
    
    <!-- Persistent Toolbar System -->
    <script src="/js/persistent-toolbar.js?v={{ time() }}"></script>
    
    <!-- Persistent Chat Overlay -->
    <script src="/js/persistent-chat.js?v={{ time() }}"></script>
    
    <!-- WASM PoW Integration -->
    <script src="/js/wasm-pow-integration.js" defer></script>
    
</body>
</html>