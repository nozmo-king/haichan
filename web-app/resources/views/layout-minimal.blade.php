<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Forum')</title>
    
    <!-- External Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Mining System -->
    <script src="/js/transparent-pow.js"></script>
    @vite('resources/js/haichan-unified.js')
</head>

<body>
    <!-- Mining Canvas Background -->
    <canvas id="neural-mining-canvas" class="mining-canvas"></canvas>
    
    <!-- Mining Status Toolbar -->
    @include('components.mining-toolbar')
    
    <!-- Main Content -->
    <main id="main-content">
        @yield('content')
    </main>
    
    <!-- Mining Dashboard -->
    @include('components.mining-dashboard')
    
    <!-- Theme Switcher -->
    @include('components.theme-switcher')
    
    <!-- Initialize Mining -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initializeGlobalMining === 'function') {
                initializeGlobalMining();
            }
        });
    </script>
</body>
</html>