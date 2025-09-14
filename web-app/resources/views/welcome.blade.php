@extends('layout')

@section('title', 'Haichan')

@section('content')
    <!-- Japanese Web Aesthetic Hero with proper Haichan colors -->
    <div style="margin: 60px auto; max-width: 900px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">

        <!-- Header with proper color scheme -->
        <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 40px 30px; border-bottom: 2px solid #708B75; position: relative;">
            <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
                β版
            </div>

            <div style="text-align: center;">
                <h1 style="font-size: 32px; color: #3D315B; margin: 0 0 15px 0; font-weight: 300; letter-spacing: 2px;">
                    <span class="strobing-emoji" style="font-size: 28px; color: #B87333;">📻</span>
                    <span style="font-family: 'Nova Cut', serif; margin: 0 8px;">Haichan</span>
                    <span class="strobing-emoji" style="font-size: 28px; color: #CD5C5C;">⚡</span>
                </h1>

                <div style="width: 120px; height: 3px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 20px auto;"></div>

                <p style="color: #708B75; font-size: 16px; line-height: 1.6; margin: 20px 0; font-weight: 500; max-width: 600px; margin-left: auto; margin-right: auto;">
                    The ultimate anonymous imageboard powered by your browser's mining capabilities. Post, discuss, and mine computational proof-of-work - all in one seamless experience.
                </p>
            </div>
        </div>

        <!-- Main Content Area -->
        <div style="padding: 40px; background: #FFFFEE;">

            <!-- Site Explanation -->
            <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px; margin-bottom: 40px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <h2 style="color: #3D315B; font-size: 20px; margin: 0; font-weight: 400; letter-spacing: 1px; font-family: 'Nova Cut', serif;">
                        <span class="strobing-emoji">🎯</span> What is Haichan? <span class="strobing-emoji">📡</span>
                    </h2>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-top: 20px;">
                    <div style="text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                        <div style="font-size: 24px; color: #708B75; margin-bottom: 10px;">⛏️</div>
                        <h4 style="color: #444B6E; margin: 8px 0; font-size: 13px; font-weight: 600;">Browser Mining</h4>
                        <p style="color: #708B75; font-size: 11px; line-height: 1.4; margin: 0;">Your browser mines SHA-256 hashes while you browse, contributing to the network's proof-of-work system.</p>
                    </div>

                    <div style="text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                        <div style="font-size: 24px; color: #9AB87A; margin-bottom: 10px;">🔐</div>
                        <h4 style="color: #444B6E; margin: 8px 0; font-size: 13px; font-weight: 600;">Anonymous Posts</h4>
                        <p style="color: #708B75; font-size: 11px; line-height: 1.4; margin: 0;">Post anonymously without registration. Your identity is protected by cryptographic proof-of-work.</p>
                    </div>

                    <div style="text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                        <div style="font-size: 24px; color: #CD5C5C; margin-bottom: 10px;">🎨</div>
                        <h4 style="color: #444B6E; margin: 8px 0; font-size: 13px; font-weight: 600;">Image Library</h4>
                        <p style="color: #708B75; font-size: 11px; line-height: 1.4; margin: 0;">Upload and mine images with automatic dithering and ever-shifting arrangements.</p>
                    </div>
                </div>
            </div>

            <!-- All Boards Directory -->
            <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px; margin-bottom: 40px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <h2 style="color: #3D315B; font-size: 20px; margin: 0; font-weight: 400; letter-spacing: 1px; font-family: 'Nova Cut', serif;">
                        <span class="strobing-emoji">📋</span> Browse All Boards <span class="strobing-emoji">🚀</span>
                    </h2>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    @foreach($boards as $board)
                    <a href="/{{ $board->code }}" style="text-decoration: none; display: block; background: #FFFFEE; border: 1px solid #708B75; border-radius: 5px; padding: 20px; text-align: center; transition: all 0.2s ease;">
                        <div style="font-size: 32px; margin-bottom: 10px;">
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
                        </div>
                        <h3 style="color: #3D315B; margin: 8px 0 5px 0; font-size: 16px; font-weight: 600; letter-spacing: 0.5px;">/{{ $board->code }}/</h3>
                        <h4 style="color: #444B6E; margin: 0 0 8px 0; font-size: 13px; font-weight: 400;">{{ $board->name }}</h4>
                        <p style="color: #708B75; font-size: 10px; line-height: 1.3; margin: 0; height: 32px; display: flex; align-items: center; justify-content: center;">{{ $board->description }}</p>

                        <div style="margin-top: 12px; font-size: 9px; color: #9AB87A; display: flex; justify-content: space-between;">
                            <span>{{ $board->threads()->count() }} threads</span>
                            <span>{{ $board->threads()->sum('reply_count') }} posts</span>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #708B75;">
                    <a href="/boards" style="color: #708B75; text-decoration: none; font-size: 12px; font-weight: 500;">[View Full Board Directory]</a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <h2 style="color: #3D315B; font-size: 20px; margin: 0; font-weight: 400; letter-spacing: 1px; font-family: 'Nova Cut', serif;">
                        <span class="strobing-emoji">⚡</span> Quick Actions <span class="strobing-emoji">🎯</span>
                    </h2>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                    <a href="/library" style="display: block; text-decoration: none; background: #708B75; color: #FFFFEE; padding: 15px; border-radius: 5px; text-align: center; transition: all 0.2s ease;">
                        <div style="font-size: 20px; margin-bottom: 5px;">🖼️</div>
                        <div style="font-size: 12px; font-weight: 600;">Image Library</div>
                        <div style="font-size: 9px; opacity: 0.9;">Mine & explore images</div>
                    </a>

                    <a href="/mining" style="display: block; text-decoration: none; background: #9AB87A; color: #FFFFEE; padding: 15px; border-radius: 5px; text-align: center; transition: all 0.2s ease;">
                        <div style="font-size: 20px; margin-bottom: 5px;">⛏️</div>
                        <div style="font-size: 12px; font-weight: 600;">Mining Dashboard</div>
                        <div style="font-size: 9px; opacity: 0.9;">Monitor your mining</div>
                    </a>

                    <a href="/faq" style="display: block; text-decoration: none; background: #444B6E; color: #FFFFEE; padding: 15px; border-radius: 5px; text-align: center; transition: all 0.2s ease;">
                        <div style="font-size: 20px; margin-bottom: 5px;">❓</div>
                        <div style="font-size: 12px; font-weight: 600;">FAQ & Help</div>
                        <div style="font-size: 9px; opacity: 0.9;">How to use Haichan</div>
                    </a>
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

    <script>
    // AGGRESSIVE text sanitizer - KILL ALL PROBLEMATIC TEXT
    function sanitizeText() {
        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        let node;
        const badPatterns = [
            /join the computational resistance/gi,
            /computational resistance/gi,
            /decentralized discourse/gi,
            /every hash you mine is a vote/gi,
            /vote for decentralized/gi
        ];

        while (node = walker.nextNode()) {
            let changed = false;
            for (let pattern of badPatterns) {
                if (pattern.test(node.nodeValue)) {
                    node.nodeValue = node.nodeValue.replace(pattern, '');
                    changed = true;
                }
            }
            if (changed && node.nodeValue.trim() === '') {
                node.nodeValue = '';
            }
        }

        // Also check all elements for innerHTML
        document.querySelectorAll('*').forEach(el => {
            for (let pattern of badPatterns) {
                if (el.innerHTML && pattern.test(el.innerHTML)) {
                    el.innerHTML = el.innerHTML.replace(pattern, '');
                }
            }
        });
    }

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

    // Strobing emoji animation
    document.addEventListener('DOMContentLoaded', function() {
        // Clean any bad text on load
        sanitizeText();

        // Set up mutation observer to clean any dynamically added bad text
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    sanitizeText();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    });
    </script>

    <style>
    @keyframes strobe {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.05); }
    }

    .strobing-emoji {
        animation: strobe 3s infinite ease-in-out;
    }

    .fade-text {
        transition: opacity 0.5s ease-in-out;
    }

    /* Board card hover effects */
    a[href^="/"][href$="/"] {
        box-shadow: 0 2px 4px rgba(68, 75, 110, 0.1);
    }

    a[href^="/"][href$="/"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(68, 75, 110, 0.2);
        border-color: #444B6E;
    }

    /* Quick action hover effects */
    a[href="/library"]:hover { background: #9AB87A !important; }
    a[href="/mining"]:hover { background: #708B75 !important; }
    a[href="/faq"]:hover { background: #708B75 !important; }
    </style>
@endsection