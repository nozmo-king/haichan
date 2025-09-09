@extends('layout')

@section('title', 'Haichan')

@section('content')
    <!-- Japanese Web Aesthetic Hero with proper Haichan colors -->
    <div style="margin: 60px auto; max-width: 680px; background: #F5F5DC; border: 2px solid #708B75;">
        
        <!-- Header with proper color scheme -->
        <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 40px 30px; border-bottom: 2px solid #708B75; position: relative;">
            <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px;">
                β版
            </div>
            
            <div style="text-align: center;">
                <h1 style="font-size: 28px; color: #3D315B; margin: 0 0 12px 0; font-weight: 300; letter-spacing: 2px;">
                    <span class="strobing-emoji" style="font-size: 24px; color: #B87333;">⛏</span>
                    <span class="fade-text" style="font-family: 'MS Gothic', monospace; margin: 0 8px;" data-en="Haichan" data-jp="ハイチャン">Haichan</span>
                    <span class="strobing-emoji" style="font-size: 24px; color: #CD5C5C;">⚡</span>
                </h1>
                
                <div style="width: 80px; height: 2px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 15px auto;"></div>
                
                <p class="fade-text" style="color: #708B75; font-size: 13px; line-height: 1.6; margin: 15px 0 0 0; font-weight: 400;" data-en="A proof-of-work imageboard" data-jp="ブラウザマイニング型匿名掲示板">
                    A proof-of-work imageboard
                </p>
            </div>
        </div>
        
        <!-- Content area -->
        <div style="padding: 35px 30px; background: #FFFFEE;">
            
            <!-- Feature cards with proper color scheme -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 40px; gap: 15px;">
                
                <div style="flex: 1; text-align: center; padding: 25px 15px; background: #F5F5DC; border: 1px solid #708B75; box-shadow: 0 2px 8px rgba(61, 49, 91, 0.1);">
                    <div style="font-size: 32px; margin-bottom: 12px; color: #708B75;">
                        <span class="strobing-emoji">⚒</span>
                    </div>
                    <h3 class="fade-text" style="font-size: 11px; color: #3D315B; margin: 8px 0 6px 0; font-weight: 600; letter-spacing: 1px;" data-en="MINING" data-jp="採掘">
                        MINING
                    </h3>
                    <p class="fade-text" style="font-size: 9px; color: #708B75; margin: 0; line-height: 1.4;" data-en="background computation" data-jp="バックグラウンド計算">
                        background computation
                    </p>
                </div>
                
                <div style="flex: 1; text-align: center; padding: 25px 15px; background: #F5F5DC; border: 1px solid #708B75; box-shadow: 0 2px 8px rgba(61, 49, 91, 0.1);">
                    <div style="font-size: 32px; margin-bottom: 12px; color: #9AB87A;">
                        <span class="strobing-emoji">🔐</span>
                    </div>
                    <h3 class="fade-text" style="font-size: 11px; color: #3D315B; margin: 8px 0 6px 0; font-weight: 600; letter-spacing: 1px;" data-en="SECURE" data-jp="暗号">
                        SECURE
                    </h3>
                    <p class="fade-text" style="font-size: 9px; color: #708B75; margin: 0; line-height: 1.4;" data-en="cryptographic identity" data-jp="暗号化アイデンティティ">
                        cryptographic identity
                    </p>
                </div>
                
                <div style="flex: 1; text-align: center; padding: 25px 15px; background: #F5F5DC; border: 1px solid #708B75; box-shadow: 0 2px 8px rgba(61, 49, 91, 0.1);">
                    <div style="font-size: 32px; margin-bottom: 12px; color: #CD5C5C;">
                        <span class="strobing-emoji">💎</span>
                    </div>
                    <h3 class="fade-text" style="font-size: 11px; color: #3D315B; margin: 8px 0 6px 0; font-weight: 600; letter-spacing: 1px;" data-en="COLLECTIBLE" data-jp="希少">
                        COLLECTIBLE
                    </h3>
                    <p class="fade-text" style="font-size: 9px; color: #708B75; margin: 0; line-height: 1.4;" data-en="rare hash patterns" data-jp="レアハッシュパターン">
                        rare hash patterns
                    </p>
                </div>
                
            </div>
            
            <!-- Action buttons with proper color scheme -->
            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #708B75;">
                <a href="/mining" style="display: inline-block; background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; padding: 12px 30px; text-decoration: none; margin: 6px 8px; font-size: 11px; font-weight: 500; letter-spacing: 0.5px; transition: all 0.2s; border: 1px solid #708B75;">
                    <span class="strobing-emoji">🎮</span> <span class="fade-text" data-en="Mining Dashboard" data-jp="マイニング">Mining Dashboard</span>
                </a>
                
                <a href="/gen" style="display: inline-block; background: linear-gradient(135deg, #3D315B, #444B6E); color: #FFFFEE; padding: 12px 25px; text-decoration: none; margin: 6px 8px; font-size: 11px; font-weight: 500; letter-spacing: 0.5px; transition: all 0.2s; border: 1px solid #3D315B;">
                    <span class="strobing-emoji">💬</span> /Gen/
                </a>
            </div>
            
        </div>
        
    </div>
    
    <!-- Fade transition script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-text');
            
            fadeElements.forEach(element => {
                const englishText = element.dataset.en;
                const japaneseText = element.dataset.jp;
                
                setInterval(() => {
                    element.style.opacity = '0';
                    element.style.transition = 'opacity 0.5s ease-in-out';
                    
                    setTimeout(() => {
                        if (element.textContent.trim() === englishText) {
                            element.textContent = japaneseText;
                        } else {
                            element.textContent = englishText;
                        }
                        element.style.opacity = '1';
                    }, 500);
                }, 3000);
            });
        });
    </script>
    
    <!-- Japanese How It Works Section with proper colors -->
    <div style="margin: 40px auto; max-width: 680px; background: #F5F5DC; border: 2px solid #708B75;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #FFFACD, #F5F5DC); padding: 20px 30px; border-bottom: 2px solid #708B75; text-align: center;">
            <h2 class="fade-text" style="font-size: 16px; color: #3D315B; margin: 0; font-weight: 400; letter-spacing: 1px;" data-en="🔬 System Overview ✨" data-jp="🔬 システム概要 ✨">
                🔬 System Overview ✨
            </h2>
            <p class="fade-text" style="font-size: 10px; color: #708B75; margin: 5px 0 0 0;" data-en="technical details" data-jp="技術詳細">technical details</p>
        </div>
        
        <!-- Content grid -->
        <div style="padding: 30px; background: #FFFFEE;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <!-- Left column -->
                <div>
                    <div style="margin-bottom: 25px;">
                        <h3 class="fade-text" style="font-size: 12px; color: #708B75; margin: 0 0 10px 0; font-weight: 600; letter-spacing: 0.5px;" data-en="⛏ Browser Mining" data-jp="⛏ ブラウザマイニング">
                            ⛏ Browser Mining
                        </h3>
                        <p class="fade-text" style="font-size: 10px; color: #3D315B; line-height: 1.6; margin: 0;" data-en="SHA-256 hashes computed in background<br><br>• IDLE (~100 H/s) - low impact<br>• HYPER (3K+ H/s) - turbo mining" data-jp="バックグラウンドでSHA-256ハッシュを計算<br><br>• IDLE (~100 H/s) 低負荷<br>• HYPER (3K+ H/s) 高速採掘">
                            SHA-256 hashes computed in background<br><br>• <span style="font-weight: 500; color: #708B75;">IDLE</span> (~100 H/s) - low impact<br>• <span style="font-weight: 500; color: #9AB87A;">HYPER</span> (3K+ H/s) - turbo mining
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="fade-text" style="font-size: 12px; color: #9AB87A; margin: 0 0 10px 0; font-weight: 600; letter-spacing: 0.5px;" data-en="🎲 Rarity System" data-jp="🎲 希少度システム">
                            🎲 Rarity System
                        </h3>
                        <p class="fade-text" style="font-size: 10px; color: #3D315B; line-height: 1.6; margin: 0;" data-en="Hunt for rare hash patterns<br><br>• 21e8 - COMMON (~1/65K)<br>• 21e800 - UNCOMMON (~1/16M)<br>• 000021e8 - LEGENDARY (~1/4B)" data-jp="レアハッシュパターンを探索<br><br>• 21e8 普通 (~1/65K)<br>• 21e800 珍しい (~1/16M)<br>• 000021e8 伝説 (~1/4B)">
                            Hunt for rare hash patterns<br><br>
                            • <code style="background: #F5F5DC; padding: 1px 3px; border: 1px solid #708B75;">21e8</code> <span style="color: #708B75;">COMMON</span> (~1/65K)<br>
                            • <code style="background: #F5F5DC; padding: 1px 3px; border: 1px solid #708B75;">21e800</code> <span style="color: #9AB87A;">UNCOMMON</span> (~1/16M)<br>
                            • <code style="background: #F5F5DC; padding: 1px 3px; border: 1px solid #708B75;">000021e8</code> <span style="color: #CD5C5C; font-weight: 500;">LEGENDARY</span> (~1/4B)
                        </p>
                    </div>
                </div>
                
                <!-- Right column -->
                <div>
                    <div style="margin-bottom: 25px;">
                        <h3 class="fade-text" style="font-size: 12px; color: #B87333; margin: 0 0 10px 0; font-weight: 600; letter-spacing: 0.5px;" data-en="🔑 Crypto Auth" data-jp="🔑 暗号認証">
                            🔑 Crypto Auth
                        </h3>
                        <p class="fade-text" style="font-size: 10px; color: #3D315B; line-height: 1.6; margin: 0;" data-en="Generate secp256k1 keypair, sign with private key<br><br>Mathematically verifiable but personally anonymous" data-jp="secp256k1キーペア生成、秘密鍵で署名<br><br>数学的に検証可能、個人的に匿名">
                            Generate secp256k1 keypair, sign with private key<br><br>
                            <span style="color: #708B75; font-weight: 500;">Mathematically verifiable</span> but <span style="color: #9AB87A; font-weight: 500;">personally anonymous</span>
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="fade-text" style="font-size: 12px; color: #CD5C5C; margin: 0 0 10px 0; font-weight: 600; letter-spacing: 0.5px;" data-en="🏆 Thread Battles" data-jp="🏆 スレッド競争">
                            🏆 Thread Battles
                        </h3>
                        <p class="fade-text" style="font-size: 10px; color: #3D315B; line-height: 1.6; margin: 0;" data-en="Every proof assigned to current thread<br><br>Threads ranked by computational contribution, not just content" data-jp="各プルーフは現在のスレッドに割り当て<br><br>コンテンツではなく計算貢献度でランキング">
                            Every proof assigned to current thread<br><br>
                            Threads ranked by <span style="color: #708B75; font-weight: 500;">computational contribution</span>, not just content
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
    
    <!-- Boards Section -->
    @if($boards->count() > 0)
    <div style="margin: 40px auto; max-width: 900px;">
        <h3 style="text-align: center; color: #444B6E; margin-bottom: 25px; font-size: 20px;">
            Board Catalog
        </h3>
        <div class="board-listing">
            <div class="boards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @foreach($boards as $board)
                <div class="board-card" 
                     data-board-code="{{ $board->code }}"
                     data-board-name="{{ $board->name }}"
                     style="padding: 20px; background: #FFFACD; border: 2px solid #708B75; border-radius: 10px; transition: all 0.3s; cursor: pointer;"
                     onmouseover="this.style.background='#F5F5DC'; this.style.transform='translateY(-2px)'"
                     onmouseout="this.style.background='#FFFACD'; this.style.transform='translateY(0)'">
                    <h3 style="margin-bottom: 10px;">
                        <a href="/{{ $board->code }}" style="color: #444B6E; text-decoration: none;">
                            @if($board->code === 'gen')
                                <span class="strobing-emoji">💬</span>
                            @elseif($board->code === 'film')
                                <span class="strobing-emoji">🎬</span>
                            @elseif($board->code === 'biz')
                                <span class="strobing-emoji">💼</span>
                            @elseif($board->code === 'lit')
                                <span class="strobing-emoji">📚</span>
                            @elseif($board->code === 'x')
                                <span class="strobing-emoji">👽</span>
                            @elseif($board->code === 'meta')
                                <span class="strobing-emoji">⚙️</span>
                            @elseif($board->code === 'mu')
                                <span class="strobing-emoji">🎵</span>
                            @elseif($board->code === 'tech')
                                <span class="strobing-emoji">💻</span>
                            @elseif($board->code === 'crypto')
                                <span class="strobing-emoji">₿</span>
                            @elseif($board->code === 'art')
                                <span class="strobing-emoji">🎨</span>
                            @elseif($board->code === 'sci')
                                <span class="strobing-emoji">🧪</span>
                            @elseif($board->code === 'pol')
                                <span class="strobing-emoji">🗳️</span>
                            @elseif($board->code === 'food')
                                <span class="strobing-emoji">🍜</span>
                            @elseif($board->code === 'fit')
                                <span class="strobing-emoji">💪</span>
                            @elseif($board->code === 'diy')
                                <span class="strobing-emoji">🔨</span>
                            @elseif($board->code === 'travel')
                                <span class="strobing-emoji">✈️</span>
                            @elseif($board->code === 'gaming')
                                <span class="strobing-emoji">🎮</span>
                            @elseif($board->code === 'anime')
                                <span class="strobing-emoji">🗾</span>
                            @elseif($board->code === 'news')
                                <span class="strobing-emoji">📰</span>
                            @elseif($board->code === 'philosophy')
                                <span class="strobing-emoji">🤔</span>
                            @else
                                <span class="strobing-emoji">⛏️</span>
                            @endif
                            /{{ $board->code }}/
                        </a>
                    </h3>
                    <p style="font-size: 14px; color: #708B75; margin-bottom: 15px;">{{ $board->description }}</p>
                    
                    <!-- Aesthetic mining stats for each board -->
                    <div style="border-top: 1px solid #708B75; padding-top: 10px; font-size: 11px; color: #9AB87A;">
                        <div style="display: flex; justify-content: space-between;">
                            <span><span class="strobing-emoji">⛏️</span> <span class="fade-text" data-en="Threads: {{ $board->threads->count() }}" data-jp="スレッド: {{ $board->threads->count() }}">Threads: <strong>{{ $board->threads->count() }}</strong></span></span>
                            <span><span class="strobing-emoji">💎</span> <span class="fade-text" data-en="Active: {{ $board->threads->where('created_at', '>', now()->subDays(7))->count() }}" data-jp="活動: {{ $board->threads->where('created_at', '>', now()->subDays(7))->count() }}">Active: <strong>{{ $board->threads->where('created_at', '>', now()->subDays(7))->count() }}</strong></span></span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Call to Action -->
    <div style="text-align: center; margin: 50px auto 20px; padding: 25px; background: #444B6E; color: #FFFFEE; border-radius: 15px; max-width: 700px;">
        <h3 style="margin-bottom: 15px; color: #9AB87A;">🚀 yooooooo 🚀</h3>
        <p style="margin-bottom: 20px; font-size: 16px;">
            Join the computational resistance. Every hash you mine is a vote for decentralized discourse.
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="/mining" style="background: #9AB87A; color: #444B6E; padding: 12px 20px; text-decoration: none; border-radius: 8px; font-weight: bold;">⛏️ START MINING NOW</a>
            <a href="/rules" style="background: transparent; color: #9AB87A; padding: 12px 20px; text-decoration: none; border: 2px solid #9AB87A; border-radius: 8px; font-weight: bold;">📜 READ THE RULES</a>
            <a href="/faq" style="background: transparent; color: #9AB87A; padding: 12px 20px; text-decoration: none; border: 2px solid #9AB87A; border-radius: 8px; font-weight: bold;">❓ FAQ</a>
        </div>
    </div>
    
    <!-- Footer Disclaimer -->
    <div style="text-align: center; margin: 30px auto; padding: 15px; background: rgba(112, 139, 117, 0.1); border: 1px dashed #708B75; max-width: 600px; font-size: 11px; line-height: 1.4; color: #666;">
        <strong>⚠️ WARNING:</strong> This platform is experimental. Mining will make your device warm and consume CPU resources. By participating, you're beta testing the future of decentralized discussion platforms. Use at your own discretion and prepare for an unconventional browsing experience. 🔥
    </div>
@endsection
