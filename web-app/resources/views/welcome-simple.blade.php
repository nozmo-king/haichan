@extends('layout')

@section('title', 'Haichan - Proof-of-Work Imageboard')

@section('content')
    <!-- Simple Header -->
    <div style="margin: 20px auto; max-width: 1200px;">
        
        <!-- Title Bar -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; padding: 20px; text-align: center;">
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: #3D315B; margin: 0;">
                <span style="color: #B87333;">📻</span> HAICHAN <span style="color: #CD5C5C;">⚡</span>
            </h1>
            <p style="color: #6B7A6B; font-size: 14px; margin: 5px 0 0 0;">
                Proof-of-Work Imageboard · {{ $userCount }}/{{ $userCap }} users
            </p>
        </div>

        <!-- Quick Actions -->
        <div style="background: #FFFACD; border: 2px solid #708B75; border-top: none; padding: 20px; text-align: center;">
            @if(session('bitcoin_auth_id'))
                <a href="/chat" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background: #708B75; color: #F5F5DC; text-decoration: none; border-radius: 4px;">💬 Chat</a>
                <a href="/library" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background: #9AB87A; color: #F5F5DC; text-decoration: none; border-radius: 4px;">🖼️ Image Library</a>
                <a href="/user/{{ session('bitcoin_auth_id') }}" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background: #CD5C5C; color: #F5F5DC; text-decoration: none; border-radius: 4px;">👤 Profile</a>
            @else
                <a href="/auth/login" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background: #708B75; color: #F5F5DC; text-decoration: none; border-radius: 4px;">🔐 Login</a>
                <a href="/auth/register" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background: #9AB87A; color: #F5F5DC; text-decoration: none; border-radius: 4px;">📝 Register</a>
            @endif
        </div>

        <!-- Boards Grid -->
        <div style="margin-top: 30px;">
            <h2 style="text-align: center; color: #3D315B; font-size: 20px; margin-bottom: 20px;">Boards</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                @foreach($boards as $board)
                <a href="/{{ $board->code }}" style="text-decoration: none; display: block; background: #F5F5DC; border: 2px solid #708B75; padding: 20px; text-align: center; transition: all 0.3s ease;">
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
                    <h3 style="color: #3D315B; margin: 0 0 5px 0; font-size: 18px;">/{{ $board->code }}/</h3>
                    <p style="color: #6B7A6B; font-size: 14px; margin: 0;">{{ $board->name }}</p>
                    <div style="font-size: 12px; color: #9AB87A; margin-top: 10px;">
                        {{ $board->threads()->count() }} threads · {{ $board->threads()->sum('reply_count') }} posts
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Footer Links -->
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="/catalog" style="color: #708B75; text-decoration: none; font-size: 14px; margin: 0 10px;">[The MC - All Threads]</a>
            <a href="/boards" style="color: #708B75; text-decoration: none; font-size: 14px; margin: 0 10px;">[Full Directory]</a>
            <a href="/stats" style="color: #708B75; text-decoration: none; font-size: 14px; margin: 0 10px;">[Network Stats]</a>
        </div>
    </div>

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
