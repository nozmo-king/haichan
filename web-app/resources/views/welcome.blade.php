<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haichan - A Proof-of-Work Image Board</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/global-mining.js"></script>
</head>
<body>
    <div class="container">
        @include('components.navigation')
        
        <div style="text-align: center; padding: 20px; background: rgba(245, 245, 220, 0.05);">
            <p style="font-size: 14pt; color: #708B75; margin: 0;">A proof-of-work image board</p>
        </div>

        <!-- User Count and Network Stats Display -->
        <div style="background: #9AB87A; padding: 15px; margin: 0 20px; border: 1px solid #708B75;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; text-align: center;">
                <div>
                    <div style="color: #444B6E; font-weight: bold; font-size: 11pt;">
                        Current Users: {{ $userCount }}/{{ $userCap }} 
                        @if($userCount >= $userCap)
                            <span style="color: #8B0000;">[FULL]</span>
                        @else
                            <span style="color: #006400;">[{{ $userCap - $userCount }} available]</span>
                        @endif
                    </div>
                </div>
                
                <div>
                    <div style="color: #444B6E; font-weight: bold; font-size: 11pt;">
                        Global Hashrate: {{ number_format($globalHashrate) }} H/s
                        @if($activeSessions > 0)
                            <span style="color: #006400;">[{{ $activeSessions }} active miners]</span>
                        @else
                            <span style="color: #8B0000;">[no active miners]</span>
                        @endif
                    </div>
                </div>
                
                <div>
                    <div style="color: #444B6E; font-weight: bold; font-size: 9pt;">
                        Network Total: {{ number_format($totalHashes) }} hashes<br>
                        Valid Proofs: {{ number_format($totalProofs) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="board-listing">
            <h2 style="text-align: center; margin-bottom: 30px;">Welcome to Haichan</h2>
            
            <!-- How It Works Section -->
            <div style="background: #F5F5DC; border: 1px solid #708B75; padding: 20px; margin-bottom: 20px;">
                <h3 style="color: #444B6E; margin-bottom: 15px; border-bottom: 1px solid #708B75; padding-bottom: 5px;">How It Works</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: #444B6E; margin-bottom: 8px;">🔐 Cryptographic Authentication</h4>
                        <p style="font-size: 9pt; line-height: 1.4;">
                            Sign in using secp256k1 public key cryptography. No passwords or personal data required.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #444B6E; margin-bottom: 8px;">⛏️ Proof-of-Work Posting</h4>
                        <p style="font-size: 9pt; line-height: 1.4;">
                            Mine SHA256 hashes to earn points. Use points to bump threads and increase visibility.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #444B6E; margin-bottom: 8px;">👥 Limited User Base</h4>
                        <p style="font-size: 9pt; line-height: 1.4;">
                            Only {{ $userCap }} users maximum. Quality over quantity in discussion.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Getting Started -->
            <div style="background: #FFFACD; border: 1px solid #708B75; padding: 20px; margin-bottom: 20px;">
                <h3 style="color: #444B6E; margin-bottom: 15px; border-bottom: 1px solid #708B75; padding-bottom: 5px;">Getting Started</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="text-align: center;">
                        <div style="font-size: 24pt; margin-bottom: 10px;">1️⃣</div>
                        <p style="font-size: 9pt;"><strong>Browse Anonymously</strong><br>View all boards and threads without an account</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="font-size: 24pt; margin-bottom: 10px;">2️⃣</div>
                        <p style="font-size: 9pt;"><strong>Try Mining</strong><br>Test the proof-of-work system on our mining dashboard</p>
                    </div>
                    
                    <div style="text-align: center;">
                        <div style="font-size: 24pt; margin-bottom: 10px;">3️⃣</div>
                        <p style="font-size: 9pt;"><strong>Get Access</strong><br>Friend codes or subscription required for posting</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin: 30px 0;">
                <a href="/boards" class="btn-primary" style="text-decoration: none; padding: 15px 30px; font-size: 12pt;">
                    📋 Browse Boards
                </a>
                <a href="/mining" style="background: #708B75; color: #FFFFEE; text-decoration: none; padding: 15px 30px; border: 1px solid #708B75; font-weight: bold; font-size: 12pt;">
                    ⛏️ Try Mining
                </a>
            </div>

            <!-- Available Boards -->
            @if($boards->count() > 0)
            <div style="background: #F5F5DC; border: 1px solid #708B75; padding: 20px;">
                <h3 style="color: #444B6E; margin-bottom: 15px; border-bottom: 1px solid #708B75; padding-bottom: 5px;">Available Boards</h3>
                <div class="boards-grid">
                    @foreach($boards as $board)
                    <div class="board-card" data-board-id="{{ $board->id }}" data-board-name="{{ $board->code }}">
                        <h4><a href="/{{ $board->code }}" style="color: #444B6E; text-decoration: none;">
                            <strong>/{{ $board->code }}/</strong> - {{ $board->name }}
                        </a></h4>
                        <p style="font-size: 9pt; color: #666; margin: 5px 0;">{{ $board->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: #444B6E; font-size: 8pt; background: #F5F5DC; border: 1px solid #708B75; margin: 20px;">
            <p style="margin-bottom: 8px;">Haichan combines anonymous discussion with computational proof-of-work for quality content curation.</p>
            <p>No tracking, no data collection, just pure discussion backed by cryptographic authenticity.</p>
        </div>
    </div>
</body>
</html>
