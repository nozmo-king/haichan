@extends('layout')

@section('title', 'Haichan - Proof-of-Work Imageboard')

@section('content')
    <!-- Compact Header -->
    <div style="margin: 20px auto; max-width: 1200px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.3);"> 

        <!-- Compact Header -->
        <div style="background: #FFFACD; padding: 15px 20px; border-bottom: 2px solid #708B75; position: relative; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center;">
                <h1 style="font-size: 24px; color: #3D315B; margin: 0; font-weight: 300; letter-spacing: 2px;">
                    <span class="strobing-emoji" style="font-size: 20px; color: #B87333;">📻</span>
                    <span style="font-family: 'Nova Cut', serif; margin: 0 6px;">Haichan</span>
                    <span class="strobing-emoji" style="font-size: 20px; color: #CD5C5C;">⚡</span>
                </h1>
                <span style="color: #6B7A6B; font-size: 11px; margin-left: 15px; opacity: 0.8;">PoW Imageboard • {{ $userCount }}/{{ $userCap }} users • {{ number_format($globalHashrate) }} H/hr</span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="background: #3D315B; color: #FFFFEE; padding: 2px 6px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">β版</div>
                <div style="background: #708B75; color: #FFFFEE; padding: 2px 6px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">{{ number_format($totalHashes) }} total hashes</div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div style="padding: 20px; background: #FFFACD;">

            <!-- Live Network Stats -->
            <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; text-align: center;">
                    <div style="padding: 8px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                        <div style="font-size: 16px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ $activeSessions }}</div>
                        <div style="font-size: 9px; color: #6B7A6B; text-transform: uppercase;">Active Miners</div>
                    </div>
                    <div style="padding: 8px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                        <div style="font-size: 16px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($totalProofs) }}</div>
                        <div style="font-size: 9px; color: #6B7A6B; text-transform: uppercase;">Total Proofs</div>
                    </div>
                    <div style="padding: 8px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                        <div style="font-size: 16px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">{{ number_format($globalHashrate) }}</div>
                        <div style="font-size: 9px; color: #6B7A6B; text-transform: uppercase;">Global H/hr</div>
                    </div>
                    <div style="padding: 8px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 4px;">
                        <div style="font-size: 16px; font-weight: bold; color: #444B6E; font-family: 'Courier New', monospace;">{{ $userCount }}/{{ $userCap }}</div>
                        <div style="font-size: 9px; color: #6B7A6B; text-transform: uppercase;">Users</div>
                    </div>
                </div>
            </div>

            <!-- Boards -->
            <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3 style="color: #708B75; font-size: 16px; margin: 0 0 15px 0; font-weight: 600;">📋 Boards</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    @php
                        $boards = \App\Models\Board::all();
                    @endphp
                    @foreach($boards as $board)
                    <a href="/{{ $board->code }}" 
                       class="board-box"
                       style="text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 12px 16px; 
                              background: linear-gradient(145deg, #9AB87A, #708B75); 
                              border: none; border-radius: 6px; 
                              box-shadow: 4px 4px 8px rgba(0,0,0,0.2), -2px -2px 6px rgba(255,255,255,0.1);
                              transition: all 0.2s ease;">
                        <span style="font-size: 24px; filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.3));">
                            @switch($board->code)
                                @case('gen') 💬 @break
                                @case('tech') 💻 @break
                                @case('biz') 💼 @break
                                @case('film') 🎬 @break
                                @case('x') 👽 @break
                                @case('lit') 📚 @break
                                @case('meta') ⚙️ @break
                                @case('mu') 🎵 @break
                                @default 📌
                            @endswitch
                        </span>
                        <div style="flex: 1;">
                            <div style="color: #FFFFEE; font-size: 14px; font-weight: 700; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">/{{ $board->code }}/</div>
                            <div style="color: #F5F5DC; font-size: 11px; text-shadow: 1px 1px 1px rgba(0,0,0,0.2);">{{ $board->name ?? ucfirst($board->code) }}</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 4px;">
                            <span style="color: #FFFFEE; font-size: 12px; font-weight: 600; text-shadow: 1px 1px 1px rgba(0,0,0,0.3);">{{ $board->threads()->count() }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 2px solid #708B75;">
                    <a href="/catalog" style="color: #708B75; text-decoration: none; font-size: 13px; font-weight: 600;">[Catalog]</a> • 
                    <a href="/boards" style="color: #708B75; text-decoration: none; font-size: 13px; font-weight: 600;">[Full Directory]</a>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Quick Actions -->
                <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 15px;">
                    <h3 style="color: #708B75; font-size: 14px; margin: 0 0 10px 0; font-weight: 500; letter-spacing: 0.5px;">⚡ Quick Access</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px;">
                        <a href="/anon" style="display: block; text-decoration: none; background: #3D315B; color: #FFFFEE; padding: 8px; border-radius: 4px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 14px; margin-bottom: 2px;">👤</div>
                            <div style="font-size: 10px; font-weight: 600;">Anonymous</div>
                        </a>
                        <a href="/library" style="display: block; text-decoration: none; background: #708B75; color: #FFFFEE; padding: 8px; border-radius: 4px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 14px; margin-bottom: 2px;">🖼️</div>
                            <div style="font-size: 10px; font-weight: 600;">Images</div>
                        </a>
                        <a href="/mining" style="display: block; text-decoration: none; background: #9AB87A; color: #FFFFEE; padding: 8px; border-radius: 4px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 14px; margin-bottom: 2px;">⛏️</div>
                            <div style="font-size: 10px; font-weight: 600;">Mining</div>
                        </a>
                        <a href="/stats" style="display: block; text-decoration: none; background: #CD5C5C; color: #FFFFEE; padding: 8px; border-radius: 4px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 14px; margin-bottom: 2px;">📊</div>
                            <div style="font-size: 10px; font-weight: 600;">Stats</div>
                        </a>
                        <a href="/chat" style="display: block; text-decoration: none; background: #8A2BE2; color: #FFFFEE; padding: 8px; border-radius: 4px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 14px; margin-bottom: 2px;">💬</div>
                            <div style="font-size: 10px; font-weight: 600;">Chat</div>
                        </a>
                        <a href="/faq" style="display: block; text-decoration: none; background: #444B6E; color: #FFFFEE; padding: 8px; border-radius: 4px; text-align: center; transition: all 0.2s ease;">
                            <div style="font-size: 14px; margin-bottom: 2px;">❓</div>
                            <div style="font-size: 10px; font-weight: 600;">Help</div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 15px;">
                    <h3 style="color: #708B75; font-size: 14px; margin: 0 0 10px 0; font-weight: 500; letter-spacing: 0.5px;">📈 Live Activity</h3>
                    <div id="live-activity" style="font-size: 10px; color: #6B7A6B; line-height: 1.4;">
                        @php
                            $recentThreads = \App\Models\Thread::with('board')->orderBy('created_at', 'desc')->take(8)->get();
                        @endphp
                        @foreach($recentThreads as $thread)
                        <div style="margin-bottom: 6px; padding: 4px; background: #FFFACD; border-left: 3px solid #9AB87A; border-radius: 2px;">
                            <a href="/{{ $thread->board->code }}/{{ $thread->id }}" style="color: #708B75; text-decoration: none; font-weight: 500;">{{ Str::limit($thread->title, 25) }}</a>
                            <div style="color: #6B7A6B; font-size: 9px; margin-top: 2px;">/{{ $thread->board->code }}/ • {{ $thread->created_at->diffForHumans() }} • PoW: {{ $thread->pow_difficulty ?? 'N/A' }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Floating Language Toggle -->
    <div style="position: fixed; bottom: 20px; left: 20px; z-index: 1000;">
        <button id="lang-toggle" style="
            background: #708B75;
            color: #FFFFEE;
            border: none;
            padding: 8px 12px;
            border-radius: 15px;
            font-size: 10px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(68, 75, 110, 0.3);
        " onclick="toggleLanguage()">
            🌐 EN/JP
        </button>
    </div>

    <script nonce="{{ app('csp_nonce') }}">
    // Language toggle functionality
    function toggleLanguage() {
        const elements = document.querySelectorAll('.fade-text[data-jp]');
        elements.forEach(el => {
            const en = el.getAttribute('data-en') || el.textContent;
            const jp = el.getAttribute('data-jp');

            if (el.textContent === en) {
                el.textContent = jp;
            } else {
                el.textContent = en;
            }
        });
    }
    </script>

    <style nonce="{{ app('csp_nonce') }}">
    @keyframes strobe {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.05); }
    }

    @keyframes mining-pulse {
        0%, 100% { 
            box-shadow: 0 0 0 rgba(154, 184, 122, 0);
            border-color: #708B75;
        }
        50% { 
            box-shadow: 0 0 12px rgba(154, 184, 122, 0.4);
            border-color: #9AB87A;
        }
    }

    @keyframes pow-indicator-glow {
        0%, 100% { 
            background: linear-gradient(to right, #708B75, #9AB87A);
            box-shadow: 0 0 0 rgba(154, 184, 122, 0);
        }
        50% { 
            background: linear-gradient(to right, #9AB87A, #708B75);
            box-shadow: 0 0 4px rgba(154, 184, 122, 0.6);
        }
    }

    .strobing-emoji {
        animation: strobe 3s infinite ease-in-out;
    }

    .fade-text {
        transition: opacity 0.5s ease-in-out;
    }

    /* 3D Board boxes */
    .board-box:hover {
        transform: translateY(-2px);
        box-shadow: 6px 6px 12px rgba(0,0,0,0.3), -3px -3px 8px rgba(255,255,255,0.15);
    }

    .board-box:active {
        transform: translateY(1px);
        box-shadow: 2px 2px 4px rgba(0,0,0,0.2), -1px -1px 3px rgba(255,255,255,0.1);
    }

    /* Quick action hover effects */
    a[href="/library"]:hover { background: #9AB87A !important; transform: scale(1.05); }
    a[href="/mining"]:hover { background: #708B75 !important; transform: scale(1.05); }
    a[href="/stats"]:hover { background: #B87333 !important; transform: scale(1.05); }
    a[href="/chat"]:hover { background: #9966CC !important; transform: scale(1.05); }
    a[href="/anon"]:hover { background: #5D4E75 !important; transform: scale(1.05); }
    a[href="/faq"]:hover { background: #6B7A6B !important; transform: scale(1.05); }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .board-box {
            padding: 10px 12px;
        }
    }
    </style>
    
    @if(session('download_key'))
    <script nonce="{{ app('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        const keyContent = atob('{{ session('download_key') }}');
        const filename = '{{ session('download_filename', 'Haichan.keys') }}';
        
        const blob = new Blob([keyContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
    </script>
    @endif
@endsection