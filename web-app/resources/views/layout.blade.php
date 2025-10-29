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
    
    <!-- AESTHETIC EXTREMIST - SURGICAL PRECISION DESIGN SYSTEM -->
    <link rel="stylesheet" href="/css/aesthetic-extremist.css?v={{ time() }}">
    <link rel="stylesheet" href="/css/aesthetic-animations.css?v={{ time() }}">
    
    <!-- Fallback for legacy compatibility -->
    <link rel="stylesheet" href="/assets/ui-tokens.css">
    
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
        /* AESTHETIC EXTREMIST OVERRIDES - ELIMINATE CHAOS */
        .glow-text { 
            color: var(--accent-6) !important; 
            text-shadow: none !important; 
            animation: none !important; 
        }
        .admin-glow span, 
        .mod-glow span { 
            color: var(--accent-7) !important; 
            text-shadow: none !important; 
            animation: none !important; 
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

    <!-- SITE HEADER - AESTHETIC EXTREMIST -->
    <header class="site-header">
        <div class="container">
            <div class="site-brand">
                <a href="/" class="brand-link">HAICHAN</a>
                <div class="brand-subtitle">Proof-of-Work Imageboard</div>
            </div>
        </div>
    </header>
    
    <style>
    /* SITE HEADER - SURGICAL PRECISION */
    .site-header {
        background: var(--neutral-0);
        border-bottom: var(--border-width) solid var(--neutral-4);
        padding: var(--space-6) 0;
    }
    
    .site-brand {
        text-align: center;
    }
    
    .brand-link {
        font-size: var(--font-size-xxl);
        font-weight: var(--font-weight-medium);
        color: var(--neutral-9);
        text-decoration: none;
        letter-spacing: 2px;
        transition: all var(--transition);
        display: inline-block;
    }
    
    .brand-link:hover {
        color: var(--accent-6);
        transform: translateY(-1px);
    }
    
    .brand-subtitle {
        font-size: var(--font-size-sm);
        color: var(--neutral-6);
        margin-top: var(--space-1);
        letter-spacing: 0.5px;
    }
    </style>

    <!-- Navigation Toolbar -->
    @if(session('bitcoin_auth_id'))
        @include('components.navigation')
    @endif

    <!-- MAIN CONTENT - SURGICAL PRECISION -->
    <main class="main-content">
        @include('components.updates-panel', ['boardCode' => $boardCode ?? null])
        @yield('content')
    </main>
    
    <style>
    .main-content {
        min-height: calc(100vh - 200px);
    }
    </style>

    <!-- Elite Mining Dashboard Component -->
    @include('components.mining-dashboard')

    @yield('scripts')
    
    <!-- CRITICAL: Complete Site Fix - Load FIRST -->
    <script src="/js/site-fix.js?v={{ time() }}"></script>
    
    <!-- Emergency Fixes -->
    <script src="/js/toolbar-fix.js?v={{ time() }}"></script>
    
    <!-- Global State Management System -->
    <script src="/js/global-state.js?v={{ time() }}"></script>
    
    <!-- Persistent Toolbar System -->
    <script src="/js/persistent-toolbar.js?v={{ time() }}"></script>
    
    <!-- Persistent Chat Overlay -->
    <script src="/js/persistent-chat.js?v={{ time() }}"></script>
    
    <!-- WASM PoW Integration -->
    <script src="/js/wasm-pow-integration.js" defer></script>
    
    <!-- ELITE MINING SYSTEM - Premium experience for 256 elite users -->
    <script src="/js/enhanced-mouseover-mining.js" defer></script>
    <script src="/js/premium-mini-dashboard.js" defer></script>
    <script src="/js/visual-mining-effects.js" defer></script>
    <script src="/js/elite-mining-integration.js" defer></script>
    
</body>
</html>