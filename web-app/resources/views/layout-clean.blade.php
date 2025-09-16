<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - Crypto Forum')</title>
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/haichan.css">
    @vite('resources/js/simple-mining.js')

    <style>
        :root {
            --bg-primary: #F5F5DC;
            --bg-secondary: #FFFACD;
            --text-primary: #444B6E;
            --text-secondary: #666;
            --accent-green: #9AB87A;
            --accent-dark: #708B75;
            --border-color: #708B75;
        }

        [data-theme="dark"] {
            --bg-primary: #2d2d30;
            --bg-secondary: #383838;
            --text-primary: #E8FFE8;
            --text-secondary: #ccc;
            --accent-green: #9AB87A;
            --accent-dark: #708B75;
            --border-color: #555;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background 0.3s, color 0.3s;
        }

        .theme-toggle {
            position: fixed;
            top: 10px;
            right: 20px;
            z-index: 10000;
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .clean-header {
            background: var(--bg-secondary);
            padding: 20px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .clean-nav a {
            background: var(--accent-green);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
            transition: all 0.2s;
        }

        .clean-nav a:hover {
            background: var(--accent-dark);
            transform: translateY(-1px);
        }
    </style>
</head>
<body data-theme="light">
    <button class="theme-toggle" onclick="toggleTheme()">🌙 Night</button>

    <div class="container" style="max-width: 1024px; margin: 0 auto; background: var(--bg-primary); min-height: 100vh; border-left: 2px solid var(--border-color); border-right: 2px solid var(--border-color);">
        <div class="clean-header">
            <h1 style="font-family: 'Nova Cut', serif; color: #3D315B; margin: 0;">
                <a href="/" style="text-decoration: none; color: inherit;">HAICHAN</a>
            </h1>

            <nav class="clean-nav">
                <a href="/library">🖼️ Images</a>
                <a href="/mining">⛏️ Mining</a>
                <select onchange="if(this.value) window.location.href=this.value" style="background: var(--accent-dark); color: white; border: none; padding: 8px; border-radius: 4px; margin-left: 10px;">
                    <option value="">Boards</option>
                    @php
                    $boardIcons = ['gen' => '💬', 'tech' => '💻', 'biz' => '💼', 'film' => '🎬', 'x' => '👽', 'lit' => '📚', 'meta' => '⚙️', 'mu' => '🎵'];
                    $allBoards = \App\Models\Board::orderBy('code')->get();
                    @endphp
                    @foreach($allBoards as $board)
                    <option value="/{{ $board->code }}">{{ $boardIcons[$board->code] ?? '📌' }} /{{ $board->code }}/</option>
                    @endforeach
                </select>
            </nav>
        </div>

        @yield('content')
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            const button = document.querySelector('.theme-toggle');

            if (body.getAttribute('data-theme') === 'dark') {
                body.setAttribute('data-theme', 'light');
                button.textContent = '🌙 Night';
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                button.textContent = '☀️ Day';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                document.querySelector('.theme-toggle').textContent = '☀️ Day';
            }
        });

        // Simple mining integration (minimal)
        if (window.simpleMiner) {
            setInterval(() => {
                const stats = window.simpleMiner.getStats();
                // Update any mining displays if needed
            }, 5000);
        }
    </script>
</body>
</html>