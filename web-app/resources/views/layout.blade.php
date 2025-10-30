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
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet"></noscript>
    
    <!-- AESTHETIC EXTREMIST - SURGICAL PRECISION DESIGN SYSTEM -->
    <link rel="stylesheet" href="/css/aesthetic-extremist.css?v={{ time() }}">
    <link rel="stylesheet" href="/css/aesthetic-animations.css?v={{ time() }}">
    
    <!-- AMAZING DESIGN OVERHAUL -->
    <link rel="stylesheet" href="/css/amazing.css?v={{ time() }}">
    
    <!-- FORCE YELLOW BUTTONS -->
    <link rel="stylesheet" href="/css/yellow-buttons.css?v={{ time() }}">
    <script src="/js/yellow-buttons.js?v={{ time() }}"></script>
    
    <!-- FORCE GREEN AND PINK -->
    <link rel="stylesheet" href="/css/force-colors.css?v={{ time() }}">
    
    <!-- Fallback for legacy compatibility -->
    <link rel="stylesheet" href="/assets/ui-tokens.css">
    
    <script>
        // Layout initialization
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('css-loading');
            console.log('✅ Clean layout loaded');
            
            // Force classic theme ONLY
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.className = 'theme-classic';
        });
    </script>
</head>
<body data-theme="classic" class="theme-classic">

    <!-- SITE HEADER - AESTHETIC EXTREMIST -->
    <header class="site-header extreme-bordered-text">
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
        background: var(--neutral-1);
        border-bottom: var(--border-width) solid var(--neutral-4);
        padding: var(--space-6) 0;
    }
    
    .site-brand {
        text-align: center;
    }
    
    .brand-link {
        font-family: 'Nova Cut', serif;
        font-size: 48px;
        font-weight: normal;
        color: var(--neutral-8);
        text-decoration: none;
        letter-spacing: 3px;
        display: inline-block;
        text-shadow: 
            -1px -1px 0 var(--neutral-10),
            1px -1px 0 var(--neutral-10),
            -1px 1px 0 var(--neutral-10),
            1px 1px 0 var(--neutral-10);
        animation: brandWobble 3s ease-in-out infinite, brandFade 4s ease-in-out infinite alternate;
    }
    
    .brand-link:hover {
        color: var(--neutral-9);
        text-shadow: 
            -2px -2px 0 var(--neutral-10),
            2px -2px 0 var(--neutral-10),
            -2px 2px 0 var(--neutral-10),
            2px 2px 0 var(--neutral-10);
        animation-duration: 1s, 2s;
    }
    
    @keyframes brandWobble {
        0%, 100% { transform: rotate(0deg) translateY(0px); }
        25% { transform: rotate(0.5deg) translateY(-1px); }
        50% { transform: rotate(0deg) translateY(0px); }
        75% { transform: rotate(-0.5deg) translateY(1px); }
    }
    
    @keyframes brandFade {
        0% { opacity: 0.8; }
        100% { opacity: 1; }
    }
    
    .brand-subtitle {
        font-family: 'Nova Cut', serif;
        font-size: 24px;
        color: var(--neutral-8);
        margin-top: var(--space-1);
        letter-spacing: 2px;
        text-shadow: 
            -1px -1px 0 var(--neutral-10),
            1px -1px 0 var(--neutral-10),
            -1px 1px 0 var(--neutral-10),
            1px 1px 0 var(--neutral-10);
        animation: brandWobble 4s ease-in-out infinite, brandFade 5s ease-in-out infinite alternate;
    }
    </style>

    <!-- Navigation Toolbar -->
    @if(session('bitcoin_auth_id'))
        @include('components.navigation')
    @endif

    <!-- MAIN CONTENT - SURGICAL PRECISION -->
    <main class="main-content extreme-bordered-block">
        @yield('content')
    </main>
    
    <style>
    .main-content {
        min-height: calc(100vh - 200px);
    }
    </style>

    <!-- PoW Mining - Essential Only -->
    <script src="/js/simple-pow.js?v={{ time() }}"></script>
    <script src="/js/wasm-pow-integration.js" defer></script>
    <script src="/js/enhanced-mouseover-mining.js" defer></script>
    
    <!-- Global State Management -->
    <script src="/js/global-state.js?v={{ time() }}"></script>
    
    <!-- Persistent Toolbar -->
    <script src="/js/persistent-toolbar.js?v={{ time() }}"></script>
    
</body>
</html>