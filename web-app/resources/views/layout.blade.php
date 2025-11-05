<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'haichan - a proof-of-work imageboard')</title>
    
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
    
    <!-- Single unified stylesheet -->
    <link rel="stylesheet" href="/css/haichan.css">
    <!-- Dynamic theme based on time -->
    @php
        $currentHour = now()->format('H');
        $isNightMode = ($currentHour >= 7 && $currentHour < 22); // 7am-10pm = night mode
    @endphp
    @if($isNightMode)
        <!-- Serpiente night mode override -->
        <link rel="stylesheet" href="{{ asset('serpiente-assets/serpiente.css') }}">
        <link rel="stylesheet" href="{{ asset('serpiente-assets/serpiente-override.css') }}">
    @endif
    <!-- Load fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=UnifrakturMaguntia&display=swap" rel="stylesheet">
    
    <script nonce="{{ app('csp_nonce') }}">
        // Layout initialization
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('css-loading');
            console.log('✅ Clean layout loaded');
            
            // Force classic theme ONLY
            document.documentElement.setAttribute('data-theme', 'classic');
            document.body.className = 'theme-classic';
            
            // Initialize emoji cycling
            console.log('🚀 About to initialize emoji cycling...');
            
            // Simple test first
            setTimeout(() => {
                const testEmoji = document.querySelector('.haichan-emoji');
                if (testEmoji) {
                    console.log('✅ Found first emoji:', testEmoji.textContent);
                    testEmoji.textContent = '🎯'; // Change it to see if it works
                    console.log('✅ Changed first emoji to test target');
                } else {
                    console.log('❌ Could not find any .haichan-emoji elements');
                }
            }, 100);
            
            initEmojiCycling();
        });

        function initEmojiCycling() {
            // Wait a bit more to ensure DOM is ready
            setTimeout(() => {
                const radioEmojis = ['📻', '📡', '📺', '💻', '🔊', '📱', '🎧', '📞', '📟', '📠'];
                const lightningEmojis = ['⚡', '🌩️', '💥', '🌊', '🌍', '🔌', '🔋', '💡', '🌟', '✨', '🔥', '🌈'];
                
                // Get all emoji elements
                const allEmojis = document.querySelectorAll('.haichan-animated-header .haichan-emoji');
                console.log('🎯 Found emojis:', allEmojis.length);
                
                if (allEmojis.length >= 6) {
                    // Left side emojis (first 3)
                    for (let i = 0; i < 3; i++) {
                        if (allEmojis[i]) {
                            let emojiIndex = i;
                            
                            // Start cycling immediately 
                            allEmojis[i].textContent = radioEmojis[emojiIndex % radioEmojis.length];
                            
                            setInterval(() => {
                                emojiIndex++;
                                allEmojis[i].textContent = radioEmojis[emojiIndex % radioEmojis.length];
                            }, 1000); // Faster cycling - every 1 second
                        }
                    }
                    
                    // Right side emojis (last 3)  
                    for (let i = 3; i < 6 && i < allEmojis.length; i++) {
                        if (allEmojis[i]) {
                            let emojiIndex = i - 3;
                            
                            // Start cycling immediately
                            allEmojis[i].textContent = lightningEmojis[emojiIndex % lightningEmojis.length];
                            
                            setInterval(() => {
                                emojiIndex++;
                                allEmojis[i].textContent = lightningEmojis[emojiIndex % lightningEmojis.length];
                            }, 800); // Even faster cycling - every 0.8 seconds
                        }
                    }
                    
                    console.log('✅ Emoji cycling initialized successfully!');
                } else {
                    console.error('❌ Expected at least 6 emojis, found:', allEmojis.length);
                    console.log('❌ Available emojis:', allEmojis);
                    
                    // Fallback: try to find emojis with different selector
                    setTimeout(() => {
                        const fallbackEmojis = document.querySelectorAll('span:contains("📻"), span:contains("⚡")');
                        console.log('🔧 Fallback search found:', fallbackEmojis.length, 'emojis');
                    }, 1000);
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
            color: #68C170;
            text-shadow: 
                -1px -1px 0 #515661,
                1px -1px 0 #515661,
                -1px 1px 0 #515661,
                1px 1px 0 #515661;
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
            border: none !important;
            box-shadow: none !important;
            background: none !important;
        }
        
        .site-header .container {
            border: none !important;
            box-shadow: none !important;
            background: none !important;
        }
        
        .site-brand {
            text-align: center;
            border: none !important;
            box-shadow: none !important;
            background: none !important;
        }
        
        /* Remove hover underline from header link */
        .haichan-animated-header:hover,
        .brand-link:hover {
            text-decoration: none !important;
            border-bottom: none !important;
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
    
    {{-- ARCHIVED: Recursive 21e8 Mining Toolbar - Elite Interface removed but code preserved --}}
    
    <style nonce="{{ app('csp_nonce') }}">
    .main-content {
        min-height: calc(100vh - 200px); /* Normal height without toolbar */
        margin-bottom: 20px; /* Normal spacing without toolbar */
    }
    </style>

    <!-- PoW Mining - Essential Only -->
    <script nonce="{{ app('csp_nonce') }}" src="/js/simple-pow.js?v={{ time() }}"></script>
    <script nonce="{{ app('csp_nonce') }}" src="/js/wasm-pow-integration.js" defer></script>
    <script nonce="{{ app('csp_nonce') }}" src="/js/enhanced-mouseover-mining.js" defer></script>
    
    <!-- Global State Management -->
    <script nonce="{{ app('csp_nonce') }}" src="/js/global-state.js?v={{ time() }}"></script>
    
    <!-- Persistent Toolbar -->
    <script nonce="{{ app('csp_nonce') }}" src="/js/persistent-toolbar.js?v={{ time() }}"></script>
    
</body>
</html>
