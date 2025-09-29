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
    @vite(['resources/css/grunge-chaos-scoped.css', 'resources/css/chaos-effects.css'])
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <script src="/js/quick-navigation.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="/js/haichan-unified.js"></script>
    <script src="/js/enhanced-dashboard.js"></script>
    <script src="/js/content-processor.js"></script>
    <script src="/js/mouseover-mining-v2.js"></script>
    <script src="/js/chaos-engine.js"></script>
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
<body data-theme="classic" class="theme-classic chaos-distort floating-element tv-static">

    <!-- Neural Mining Visualization Canvas -->
    <canvas id="neural-mining-canvas" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
        opacity: 0.1;
    "></canvas>

    <!-- Main Content Container -->
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
                <h1><a href="/" class="glitch-container hologram neon-pulse chaos-sparkle" data-text="HAICHAN" style="text-decoration: none; color: var(--header-color, #3D315B); font-family: 'Nova Cut', serif; font-size: 32px; font-weight: 300; letter-spacing: 3px; text-shadow: 0 2px 4px var(--header-shadow, rgba(0,0,0,0.1));" id="header-text">HAICHAN</a></h1>
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
            // Force classic theme
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.classList.add('theme-classic');
            
            // Override with simple rising rainbow text
            window.createFloatingProof = function(element, points) {
                const rect = element.getBoundingClientRect();
                const message = `+${points}`;
                
                // Create floating text with rainbow letters
                const floater = document.createElement('div');
                floater.style.cssText = `
                    position: fixed;
                    left: ${rect.left + rect.width/2}px;
                    top: ${rect.top + rect.height/2}px;
                    transform: translate(-50%, -50%);
                    font-family: 'Courier New', monospace;
                    font-weight: bold;
                    font-size: 14px;
                    z-index: 10000;
                    pointer-events: none;
                    white-space: nowrap;
                `;
                
                // Rainbow color each letter
                const colors = ['#22c55e', '#16a34a', '#15803d', '#166534', '#14532d'];
                for (let i = 0; i < message.length; i++) {
                    const span = document.createElement('span');
                    span.textContent = message[i];
                    span.style.color = colors[i % colors.length];
                    span.style.textShadow = `0 0 8px ${colors[i % colors.length]}80`;
                    floater.appendChild(span);
                }
                
                document.body.appendChild(floater);
                
                // Simple rising animation
                let startTime = Date.now();
                const animate = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = elapsed / 1500; // 1.5 seconds
                    
                    if (progress <= 1) {
                        const y = rect.top + rect.height/2 - (60 * progress);
                        const opacity = Math.max(0, 1 - progress);
                        
                        floater.style.top = y + 'px';
                        floater.style.opacity = opacity;
                        
                        requestAnimationFrame(animate);
                    } else {
                        floater.remove();
                    }
                };
                
                requestAnimationFrame(animate);
            };
            
            console.log('🎯 Haichan frontend ready with themed PoW notifications');
        });
    </script>

</body>
</html>