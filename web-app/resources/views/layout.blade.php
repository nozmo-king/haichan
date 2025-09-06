<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haichan - PoW Forum')</title>
    <link rel="stylesheet" href="/css/haichan.css">
    @vite('resources/js/global-mining.js')
    <style>
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 30px;
        }
        
        .board-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .board-item {
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .board-code {
            font-size: 24px;
            font-weight: bold;
            color: #789922;
            margin-bottom: 10px;
        }
        
        .board-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .board-desc {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .board-stats {
            font-size: 12px;
            color: #888;
        }
        
        .thread-list {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        
        .thread-list th,
        .thread-list td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        
        .thread-list th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .thread-title {
            font-weight: bold;
        }
        
        .thread-meta {
            font-size: 12px;
            color: #666;
        }
        
        .post {
            border: 1px solid #ccc;
            margin: 10px 0;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .post-header {
            font-size: 12px;
            color: #117743;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .post-content {
            line-height: 1.4;
        }
        
        a {
            color: #34345c;
            text-decoration: underline;
        }
        
        a:hover {
            color: #dd0000;
        }
        
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #34345c;
        }
        
        .thread-preview {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .thread-header {
            margin-bottom: 10px;
        }
        
        .post-preview {
            margin-bottom: 15px;
            font-style: italic;
            color: #333;
        }
        
        .replies-preview {
            margin-left: 20px;
        }
        
        .reply-preview {
            margin: 8px 0;
            font-size: 14px;
            color: #555;
        }
        
        .reply-author {
            font-weight: bold;
            color: #117743;
        }
        
        .nested-reply {
            margin-left: 25px;
            margin-top: 5px;
            font-size: 13px;
            color: #666;
        }
        
        .more-replies, .more-posts {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
        
        .more-posts a {
            color: #789922;
            font-weight: bold;
        }
        
        .reply-form {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
        }
        
        .reply-form input, .reply-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
        }
        
        .reply-form button {
            padding: 8px 15px;
            background-color: #789922;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .reply-form button:hover {
            background-color: #5a7019;
        }
        
        .nested-post {
            margin-left: 30px;
            border-left: 3px solid #ccc;
            padding-left: 15px;
        }
        
        .post-image, .thread-image {
            margin: 10px 0;
        }
        
        .post-image img, .thread-image img {
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }
        
        .post-image img:hover, .thread-image img:hover {
            border-color: #789922;
        }
        
        .form-group input[type="file"] {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .thread-preview-image, .post-preview-image, .reply-preview-image {
            display: inline-block;
            flex-shrink: 0;
        }
        
        .thread-preview-image img, .post-preview-image img, .reply-preview-image img {
            border-radius: 3px;
            transition: all 0.3s ease;
            cursor: pointer;
            object-fit: cover;
        }
        
        .thread-preview-image img:hover, .post-preview-image img:hover, .reply-preview-image img:hover {
            border-color: #789922;
            transform: scale(1.15);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .thread-preview-image img {
            width: 180px;
            height: 180px;
        }
        
        .post-preview-image img {
            width: 120px;
            height: 120px;
        }
        
        .reply-preview-image img {
            width: 80px;
            height: 80px;
        }
        
        .reply-preview {
            margin: 20px 0;
            font-size: 15px;
            color: #555;
            line-height: 1.6;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
            min-height: 60px;
        }
        
        .nested-reply {
            margin-left: 35px;
            margin-top: 12px;
            font-size: 14px;
            color: #666;
            line-height: 1.5;
            padding: 10px 0;
            border-left: 3px solid #e8e8e8;
            padding-left: 15px;
            background-color: #fefefe;
        }
        
        .deeply-nested-reply {
            margin-left: 40px;
            margin-top: 10px;
            font-size: 13px;
            color: #777;
            line-height: 1.4;
            padding: 8px 0;
            border-left: 3px solid #f0f0f0;
            padding-left: 12px;
            background-color: #fdfdfd;
            min-height: 30px;
        }
        
        .deeply-nested-reply img {
            width: 60px;
            height: 60px;
        }
        
        .thread-preview {
            padding: 25px;
            border-bottom: 2px solid #ddd;
            min-height: 200px;
            margin-bottom: 20px;
        }
        
        .post-preview {
            margin-bottom: 25px;
            font-style: italic;
            color: #333;
            line-height: 1.7;
            padding: 20px;
            background-color: #fafafa;
            border-left: 4px solid #789922;
            font-size: 15px;
        }
        
        .replies-preview {
            margin-left: 30px;
            margin-top: 25px;
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 5px;
            border: 1px solid #e8e8e8;
        }
        
        .more-posts a {
            color: #789922;
            font-weight: bold;
            text-decoration: none;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-radius: 3px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .more-posts a:hover {
            background-color: #789922;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Constant Mining Status Bar -->
    <div id="mining-status-bar" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #9AB87A;
        color: #444B6E;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        padding: 8px 0;
        z-index: 9999;
        border-bottom: 1px solid #708B75;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 20px;
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
                <span id="network-hashrate" style="color: #006400; font-weight: bold;">0 H/s</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">TOTAL HASHES:</span>
                <span id="network-total-hashes" style="color: #006400; font-weight: bold;">0</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">VALID PROOFS:</span>
                <span id="network-valid-proofs" style="color: #708B75; font-weight: bold;">0</span>
            </div>
            <div style="color: #444B6E;">
                <span style="color: #666;">ACTIVE MINERS:</span>
                <span id="network-active-miners" style="color: #8B0000; font-weight: bold;">1</span>
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
            @unless(request()->is('/'))
            <button id="mini-dash-toggle" style="
                background: #708B75;
                border: none;
                color: white;
                padding: 4px 8px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 12px;
                margin-left: 5px;
            " title="Toggle Mini Dashboard (Ctrl+D)">⛏️</button>
            @endunless
        </div>
    </div>

    <!-- Mini Dashboard Overlay (Hidden by default, on mining page, and homepage) -->
    @unless(request()->is('mining') || request()->is('/') || request()->is(''))
    <div id="mini-dashboard-overlay" style="
        position: fixed;
        top: 60px;
        right: 20px;
        width: 350px;
        height: 250px;
        background: #F5F5DC;
        border: 2px solid #708B75;
        border-radius: 5px;
        padding: 15px;
        z-index: 9998;
        display: none;
        font-family: 'Courier New', monospace;
        font-size: 11px;
        color: #444B6E;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: height 0.3s ease;
        overflow: hidden;
    ">
        <div id="mini-dash-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #708B75; padding-bottom: 8px; cursor: move;">
            <span style="font-weight: bold; color: #444B6E;">⛏️ HAICHAN MINING DASHBOARD</span>
            <div style="display: flex; gap: 5px;">
                <button id="mini-dash-minimize" style="background: #708B75; border: 1px solid #444B6E; color: #FFFFEE; padding: 2px 6px; font-size: 12px; cursor: pointer; border-radius: 2px; font-weight: bold;" title="Minimize">−</button>
                <button id="mini-dash-close" style="background: #8B0000; border: 1px solid #444B6E; color: #FFFFEE; padding: 2px 6px; font-size: 12px; cursor: pointer; border-radius: 2px; font-weight: bold;" title="Close">×</button>
            </div>
        </div>
        
        <div class="mini-dash-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div>
                <div style="color: #666; font-size: 9px;">Current Mode</div>
                <div id="mini-mining-mode" style="color: #006400; font-weight: bold;">IDLE</div>
            </div>
            <div>
                <div style="color: #666; font-size: 9px;">Personal Rate</div>
                <div id="mini-personal-rate" style="color: #006400; font-weight: bold;">0 H/s</div>
            </div>
        </div>
        
        <div class="mini-dash-content" style="margin-bottom: 15px;">
            <div style="color: #666; font-size: 9px; margin-bottom: 5px;">Mining Controls</div>
            <div style="display: flex; gap: 5px;">
                <button id="mini-idle-btn" style="background: #FFFACD; border: 1px solid #708B75; padding: 3px 6px; font-size: 9px; cursor: pointer; border-radius: 2px;">IDLE</button>
                <button id="mini-active-btn" style="background: #FFFACD; border: 1px solid #708B75; padding: 3px 6px; font-size: 9px; cursor: pointer; border-radius: 2px;">ACTIVE</button>
                <button id="mini-hyper-btn" style="background: #FFFACD; border: 1px solid #708B75; padding: 3px 6px; font-size: 9px; cursor: pointer; border-radius: 2px;">HYPER</button>
                <button id="mini-stop-btn" style="background: #F8D7DA; border: 1px solid #8B0000; padding: 3px 6px; font-size: 9px; cursor: pointer; border-radius: 2px;">STOP</button>
            </div>
        </div>
        
        <div class="mini-dash-content" style="margin-bottom: 10px;">
            <div style="color: #666; font-size: 9px; margin-bottom: 3px;">Current Hash Target</div>
            <div id="mini-hash-preview" style="
                font-family: 'Courier New', monospace;
                font-size: 8px;
                color: #666;
                background: #FFFACD;
                padding: 3px;
                border: 1px solid #DDD;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            ">21e8000abc123def...</div>
        </div>
        
        <div class="mini-dash-content" style="border-top: 1px solid #708B75; padding-top: 8px; text-align: center;">
            <a href="/mining" style="color: #708B75; text-decoration: none; font-size: 10px;">
                🎯 Open Full Dashboard
            </a>
        </div>
    </div>
    @endunless
    
    <div class="container" style="margin-top: 50px;">
        <div class="header">
            <h1><a href="/" style="text-decoration: none; color: inherit;">HAICHAN</a></h1>
            
            
        </div>
        
        @yield('content')
    </div>

    <!-- Global Haichan Mining System -->
    @vite('resources/js/global-mining.js')
    
    <!-- Additional CSS for pulse animation -->
    <style>
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</body>
</html>