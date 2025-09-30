<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mining Brain - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style>
        .mining-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #FFFFEE;
            min-height: 100vh;
            padding: 20px;
        }

        .mining-header {
            background: #708B75;
            padding: 15px;
            text-align: center;
            color: #FFFFEE;
            margin-bottom: 20px;
            border: 2px solid #444B6E;
        }

        .info-panel {
            background: #F5F5DC;
            border: 1px solid #708B75;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-panel h3 {
            color: #444B6E;
            margin-bottom: 15px;
            font-size: 16pt;
        }

        .info-panel p {
            color: #444B6E;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .brain-status {
            background: #9AB87A;
            color: #444B6E;
            padding: 8px 16px;
            border: 1px solid #708B75;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }

        .instructions {
            background: #444B6E;
            color: #FFFFEE;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .instructions h4 {
            margin-bottom: 10px;
            color: #9AB87A;
        }

        .instructions ul {
            margin-left: 20px;
        }

        .instructions li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="mining-container">
        <div class="mining-header">
            <h1><a href="/" style="color: #FFFFEE; text-decoration: none;">Haichan</a></h1>
            <h2>🧠 Mining Brain Control Center 🧠</h2>
            <p>Unified mining intelligence for all Haichan operations</p>
            <nav style="margin-top: 10px;">
                <a href="/boards" style="color: #FFFFEE; margin: 0 10px;">📋 Boards</a>
                <a href="/catalog" style="color: #FFFFEE; margin: 0 10px;">🗂️ Catalog</a>
            </nav>
        </div>

        <div class="info-panel">
            <h3>🧠 Mining Brain Active</h3>
            <p>The new centralized Mining Brain has replaced all previous mining systems.</p>
            <p>All mining operations are now controlled through the unified brain interface.</p>
            <div class="brain-status" id="brain-status">BRAIN OPERATIONAL</div>
        </div>

        <div class="instructions">
            <h4>🎮 How to Use the Mining Brain:</h4>
            <ul>
                <li><strong>Floating Brain Button:</strong> Click the 🧠 BRAIN button (bottom-right) to open the control panel</li>
                <li><strong>Mouseover Mining:</strong> Default mode - automatically mines when you hover over posts, threads, and images</li>
                <li><strong>Power Control:</strong> Adjust mining intensity from 0 (disabled) to 10 (maximum power)</li>
                <li><strong>Manual Mode:</strong> Switch to manual control for targeted mining operations</li>
                <li><strong>Background Mode:</strong> Continuous mining without requiring user interaction</li>
                <li><strong>Real-time Stats:</strong> Live hash rate, proofs found, and points earned</li>
                <li><strong>Performance Monitor:</strong> CPU usage and hash computation time tracking</li>
            </ul>

            <h4>✨ New Features:</h4>
            <ul>
                <li>🎯 Smart target detection (posts, threads, images)</li>
                <li>⚡ Optimized performance with dynamic batch sizing</li>
                <li>📊 Advanced statistics and performance monitoring</li>
                <li>🎮 Multiple mining modes for different use cases</li>
                <li>💎 Enhanced rare pattern detection and rewards</li>
                <li>🧠 Centralized control - no more conflicting systems</li>
            </ul>
        </div>
    </div>

    <script src="{{ mix('js/app.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update status when brain loads
            setTimeout(() => {
                if (window.haichanMiningBrain && window.haichanMiningBrain.isInitialized) {
                    document.getElementById('brain-status').textContent = 'BRAIN FULLY OPERATIONAL';
                    document.getElementById('brain-status').style.background = '#00ff88';
                    document.getElementById('brain-status').style.color = '#000';
                    console.log('🧠 Mining Brain dashboard: Brain detected and operational');
                } else {
                    document.getElementById('brain-status').textContent = 'BRAIN LOADING...';
                    document.getElementById('brain-status').style.background = '#ffc107';
                    console.log('🧠 Mining Brain dashboard: Waiting for brain initialization');
                }
            }, 1000);

            // Check every few seconds
            const checkInterval = setInterval(() => {
                if (window.haichanMiningBrain && window.haichanMiningBrain.isInitialized) {
                    document.getElementById('brain-status').textContent = 'BRAIN FULLY OPERATIONAL';
                    document.getElementById('brain-status').style.background = '#00ff88';
                    document.getElementById('brain-status').style.color = '#000';
                    clearInterval(checkInterval);
                }
            }, 2000);
        });
    </script>
</body>
</html>