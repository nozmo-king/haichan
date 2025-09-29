<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Forum')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/elegant-themes.css">
    <link rel="stylesheet" href="/css/quick-navigation.css">
    <link rel="stylesheet" href="/css/enhanced-dashboard.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <script src="/js/quick-navigation.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="/js/haichan-unified.js"></script>
    <script src="/js/enhanced-dashboard.js"></script>
    <script src="/js/content-processor.js"></script>
    <script src="/js/mouseover-mining-v2.js"></script>
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


    <!-- Main Content Container -->
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
                <h1><a href="/" style="text-decoration: none; color: var(--header-color, #3D315B); font-family: 'Nova Cut', serif; font-size: 32px; font-weight: 300; letter-spacing: 3px; text-shadow: 0 2px 4px var(--header-shadow, rgba(0,0,0,0.1));" id="header-text">HAICHAN</a></h1>
            </div>
        </div>
        
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
            
            console.log('🎯 CLASSIC THEME LOCKED');
        });
    </script>

</body>
</html>