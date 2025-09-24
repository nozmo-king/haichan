@extends('layout')

@section('title', 'Anonymous Browsing - Haichan')

@section('content')
    <div style="margin: 60px auto; max-width: 900px; background: #2F2F2F; border: 2px solid #5C5C5C; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);">

        <!-- Header with garbage theme -->
        <div style="background: linear-gradient(135deg, #3E3E3E 0%, #2F2F2F 100%); padding: 40px 30px; border-bottom: 2px solid #666; position: relative;">
            <div style="position: absolute; top: 15px; right: 20px; background: #8B008B; color: white; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
                ANON MODE
            </div>

            <div style="text-align: center;">
                <h1 style="font-size: 32px; color: #8B008B; margin: 0 0 15px 0; font-weight: 300; letter-spacing: 2px;">
                    <span class="strobing-emoji-fast" style="font-size: 28px;">👀</span>
                    <span style="font-family: 'Nova Cut', serif; margin: 0 8px;">Anonymous Mode</span>
                    <span class="strobing-emoji-fast" style="font-size: 28px;">💀</span>
                </h1>

                <div style="width: 120px; height: 3px; background: linear-gradient(to right, #666, #999); margin: 20px auto;"></div>

                <p style="color: #999; font-size: 16px; line-height: 1.6; margin: 20px 0; font-weight: 500; max-width: 600px; margin-left: auto; margin-right: auto;">
                    <span class="strobing-emoji-fast">🫠</span> Browse and post anonymously. One post per day. IP logged and locked. <span class="strobing-emoji-fast">🫠</span>
                </p>
            </div>
        </div>

        <!-- Warning Notice -->
        <div style="background: #4A4A4A; padding: 20px; border-bottom: 2px solid #666;">
            <div style="text-align: center; color: #FF6B6B; font-size: 14px; font-weight: bold;">
                ⚠️ ANONYMOUS BROWSING ACTIVE ⚠️
            </div>
            <div style="text-align: center; color: #CCC; font-size: 12px; margin-top: 5px;">
                Your IP is logged. You can post once every 24 hours. All posts will have garbage theme.
            </div>
        </div>

        <!-- Board Directory -->
        <div style="padding: 40px; background: #1A1A1A;">
            <div style="background: #2F2F2F; border: 2px solid #555; border-radius: 8px; padding: 30px; margin-bottom: 40px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <h2 style="color: #8B008B; font-size: 20px; margin: 0; font-weight: 400; letter-spacing: 1px; font-family: 'Nova Cut', serif;">
                        <span class="strobing-emoji-fast">📋</span> Anonymous Board Access <span class="strobing-emoji-fast">🗑️</span>
                    </h2>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                    @foreach($boards as $board)
                    <a href="/{{ strtolower($board->code) }}?anon=1" style="display: block; text-decoration: none; background: #3A3A3A; border: 1px solid #666; color: #CCC; padding: 20px; border-radius: 5px; text-align: center; transition: all 0.2s ease;">
                        <div style="font-size: 28px; color: #777; margin-bottom: 8px;">
                            @switch($board->code)
                                @case('gen') 🗑️ @break
                                @case('tech') 💻 @break
                                @case('biz') 💸 @break
                                @case('film') 🎬 @break
                                @case('x') ❌ @break
                                @case('lit') 📚 @break
                                @case('meta') ⚙️ @break
                                @case('mu') 🎵 @break
                                @default 📌
                            @endswitch
                        </div>
                        <h3 style="color: #8B008B; margin: 8px 0 5px 0; font-size: 16px; font-weight: 600; letter-spacing: 0.5px;">/{{ $board->code }}/</h3>
                        <h4 style="color: #AAA; margin: 0 0 8px 0; font-size: 13px; font-weight: 400;">{{ $board->name }}</h4>
                        <p style="color: #777; font-size: 10px; line-height: 1.3; margin: 0;">{{ $board->description }}</p>
                    </a>
                    @endforeach
                </div>

                <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #555;">
                    <p style="color: #777; font-size: 12px; font-style: italic;">Remember: Anonymous posts are garbage-themed and your username will be <span style="color: #8B008B;">Anonymous</span></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .strobing-emoji-fast {
            animation: strobe 0.8s infinite ease-in-out;
        }

        @keyframes strobe {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
    </style>
@endsection