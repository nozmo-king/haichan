<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Forum')</title>
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
    <script src="/js/haichan-unified.js"></script>
    <script src="/js/wasm-sha256.js" data-wasm-sha256></script>
    <script src="/js/enhanced-dashboard.js"></script>
    <script src="/js/mining-brain.js"></script>
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

</body>
</html>
