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
        /* AESTHETIC EXTREMIST OVERRIDES - TOTAL UI SILENCE */
        * {
            animation: none !important;
            transition: all 0.15s ease-out !important;
        }
        
        .glow-text, 
        .admin-glow, 
        .mod-glow, 
        .username-glow,
        [class*="glow"] { 
            color: var(--neutral-8) !important; 
            text-shadow: none !important; 
            animation: none !important;
            font-weight: var(--font-weight-medium) !important;
        }
        
        /* Remove all emoji and noise */
        .emoji, [class*="emoji"] { display: none !important; }
        
        /* Consistent backgrounds - no gradients */
        body, html {
            background: var(--neutral-1) !important;
            background-image: none !important;
            background-attachment: unset !important;
        }
        
        /* Remove all shadows and effects */
        * {
            box-shadow: none !important;
            text-shadow: none !important;
        }
        
        /* Minimal borders only */
        .post, .thread, .nav-links, 
        .tui-reply-form, .unified-post-form,
        .mining-dashboard, .chat-overlay {
            background: var(--neutral-0) !important;
            border: var(--border-width) solid var(--neutral-4) !important;
            border-radius: var(--border-radius) !important;
            margin: var(--space-3) !important;
            padding: var(--space-4) !important;
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

    <!-- Minimal Dashboard Removed - Using Clean Hashrate Toolbar -->

    @yield('scripts')
    
    <!-- Core Systems - Essential Only -->
    <script src="/js/global-state.js?v={{ time() }}"></script>
    
    <!-- Minimal Hashrate Toolbar - Clean & Fast -->
    <script src="/js/minimal-hashrate-toolbar.js?v={{ time() }}"></script>
    
    <!-- PoW Mining - Essential Only -->
    <script src="/js/wasm-pow-integration.js" defer></script>
    <script src="/js/enhanced-mouseover-mining.js" defer></script>
    
</body>
</html>