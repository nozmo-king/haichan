@extends('layout')

@section('title', 'Anonymous Access - Haichan')

@section('content')
<div style="margin: 60px auto; max-width: 900px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 40px 30px; border-bottom: 2px solid #708B75; position: relative; text-align: center;">
        <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
            ANON
        </div>

        <h1 style="font-size: 32px; color: #3D315B; margin: 0 0 15px 0; font-weight: 300; letter-spacing: 2px; font-family: 'Nova Cut', serif;">
            <span class="strobing-emoji" style="font-size: 28px; color: #B87333;">👤</span>
            <span>Anonymous Access</span>
            <span class="strobing-emoji" style="font-size: 28px; color: #CD5C5C;">⚡</span>
        </h1>

        <div style="width: 120px; height: 3px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 20px auto;"></div>

        <p style="color: #708B75; font-size: 16px; line-height: 1.6; margin: 20px 0; font-weight: 500; max-width: 600px; margin-left: auto; margin-right: auto;">
            Browse without registration. Anonymous posting requires stronger proof-of-work (21e800).
        </p>
    </div>

    <!-- Content -->
    <div style="padding: 40px; background: #FFFFEE;">
        <!-- Boards Directory -->
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
        </div>

        <!-- Anonymous Posting Info -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 8px; padding: 30px;">
            <div style="text-align: center; margin-bottom: 25px;">
                <h2 style="color: #3D315B; font-size: 20px; margin: 0; font-weight: 400; letter-spacing: 1px; font-family: 'Nova Cut', serif;">
                    <span class="strobing-emoji">⚡</span> Anonymous Posting <span class="strobing-emoji">🔐</span>
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-top: 20px;">
                <div style="text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                    <div style="font-size: 24px; color: #708B75; margin-bottom: 10px;">🔨</div>
                    <h4 style="color: #444B6E; margin: 8px 0; font-size: 13px; font-weight: 600;">Stronger Proof-of-Work</h4>
                    <p style="color: #708B75; font-size: 11px; line-height: 1.4; margin: 0;">Anonymous posts require 21e800 difficulty - 10x stronger than registered users (21e8).</p>
                </div>

                <div style="text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                    <div style="font-size: 24px; color: #9AB87A; margin-bottom: 10px;">👤</div>
                    <h4 style="color: #444B6E; margin: 8px 0; font-size: 13px; font-weight: 600;">Complete Anonymity</h4>
                    <p style="color: #708B75; font-size: 11px; line-height: 1.4; margin: 0;">No account needed. Your computational effort is your identity.</p>
                </div>

                <div style="text-align: center; padding: 20px; background: #FFFACD; border: 1px solid #9AB87A; border-radius: 5px;">
                    <div style="font-size: 24px; color: #CD5C5C; margin-bottom: 10px;">⏳</div>
                    <h4 style="color: #444B6E; margin: 8px 0; font-size: 13px; font-weight: 600;">Mining Time</h4>
                    <p style="color: #708B75; font-size: 11px; line-height: 1.4; margin: 0;">Expect 30-120 seconds of mining before you can post anonymously.</p>
                </div>
            </div>

            <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #708B75;">
                <a href="/register" style="color: #708B75; text-decoration: none; font-size: 12px; font-weight: 500;">[Want faster posting? Create an account]</a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes strobe {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.05); }
}

.strobing-emoji {
    animation: strobe 3s infinite ease-in-out;
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
</style>
@endsection