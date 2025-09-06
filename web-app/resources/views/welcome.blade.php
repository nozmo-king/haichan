<!DOCTYPE html>
<html>
<head>
    <title>Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/global-mining.js')
    <style>
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Mining Status Bar -->
    <div id="mining-status-bar" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #9AB87A;
        color: #444B6E;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        padding: 8px 20px;
        z-index: 9999;
        border-bottom: 1px solid #708B75;
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <span id="mining-indicator" style="
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    background: #708B75;
                    border-radius: 50%;
                    animation: pulse 1s infinite;
                "></span>
                <span style="color: #444B6E; font-weight: bold;">HAICHAN MINING NETWORK</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">HASH RATE:</span>
                <span id="network-hashrate" style="color: #006400; font-weight: bold;">{{ number_format($globalHashrate) }} H/s</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">TOTAL HASHES:</span>
                <span id="network-total-hashes" style="color: #006400; font-weight: bold;">{{ number_format($totalHashes) }}</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">VALID PROOFS:</span>
                <span id="network-valid-proofs" style="color: #708B75; font-weight: bold;">{{ number_format($totalProofs) }}</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">ACTIVE MINERS:</span>
                <span id="network-active-miners" style="color: #8B0000; font-weight: bold;">{{ $activeSessions }}</span>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <div id="current-mining-hash" style="
                font-family: 'Courier New', monospace;
                font-size: 9px;
                color: #666;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            ">21e8000abc123def...</div>
            <div style="color: #444B6E;">
                <span style="color: #666;">DIFFICULTY:</span>
                <span id="current-difficulty" style="color: #8B0000; font-weight: bold;">21e8</span>
            </div>
            <select style="
                background: #708B75;
                color: #FFFFEE;
                border: 1px solid #444B6E;
                padding: 4px 6px;
                border-radius: 3px;
                font-size: 9px;
                margin-left: 10px;
                cursor: pointer;
            " onchange="if(this.value) window.location.href=this.value">
                <option value="">📋 Boards</option>
                <option value="/gen">💬 /gen/</option>
                <option value="/film">🎬 /film/</option>
                <option value="/biz">💼 /biz/</option>
                <option value="/lit">📚 /lit/</option>
                <option value="/x">👽 /x/</option>
                <option value="/meta">⚙️ /meta/</option>
                <option value="/mu">🎵 /mu/</option>
            </select>
        </div>
    </div>
    
    <div class="container" style="margin-top: 50px;">
        <div class="header">
            <h1><a href="/">HAICHAN</a></h1>
        </div>
        
        @if($boards->count() > 0)
        <div class="board-listing">
            <div class="boards-grid">
                @foreach($boards as $board)
                <div class="board-card">
                    <h3><a href="/{{ $board->code }}">💬 /{{ $board->code }}/</a></h3>
                    <p>{{ $board->description }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <div style="text-align: center; margin: 30px auto; padding: 15px; background: #F5F5DC; border: 1px solid #708B75; max-width: 600px; font-size: 9pt; line-height: 1.4;">
            <strong>What is Haichan?</strong><br>
            A proof-of-work imageboard requiring computational mining for posting. Anonymous discussion with cryptographic authenticity.
        </div>
    </div>
</body>
</html>
