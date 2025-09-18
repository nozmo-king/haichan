@extends('layout')

@section('title', 'FAQ - Haichan')

@section('content')
<!-- Japanese Web Aesthetic Container with Homepage Style -->
<div style="margin: 60px auto 40px auto; max-width: 900px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header with proper color scheme -->
    <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 25px 40px; border-bottom: 2px solid #708B75; position: relative; text-align: center;">
        <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 4px 12px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px; border-radius: 3px;">
            β版
        </div>

        <h1 style="font-size: 28px; color: #3D315B; margin: 0 0 12px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            <span style="font-size: 26px; color: #B87333;">❓</span>
            Frequently Asked Questions
            <span style="font-size: 26px; color: #CD5C5C;">📚</span>
        </h1>

        <div style="width: 80px; height: 2px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 15px auto;"></div>

        <p style="color: #708B75; font-size: 13px; line-height: 1.6; margin: 15px 0 0 0; font-weight: 400;">Understanding Haichan's computational proof system</p>

        <!-- Navigation breadcrumb with proper spacing -->
        <div style="margin-top: 20px; font-size: 11px; color: #444B6E;">
            <a href="{{ route('boards.index') }}" style="color: #708B75; text-decoration: none; margin-right: 10px;">[Boards]</a>
            <a href="/" style="color: #708B75; text-decoration: none; margin-right: 10px;">[Home]</a>
            <span style="color: #9AB87A;">[FAQ]</span>
        </div>
    </div>

    <!-- Content area with proper spacing -->
    <div style="padding: 40px; background: #FFFFEE;">

        <div style="color: #3D315B; font-size: 13px; line-height: 1.8;">
            
            <h3>⛏️ Mining & Proof-of-Work</h3>
            
            <h4>Q: What is proof-of-work?</h4>
            <p>Proof-of-work is a cryptographic system where you "mine" SHA256 hashes to find specific patterns. This prevents spam, ensures quality contributions, and helps rank threads by effort invested. Every post, thread, and interaction can be enhanced through mining.</p>
            
            <h4>Q: How do I start mining?</h4>
            <p>Visit the <a href="/mining">⛏️ Mining dashboard</a> to begin. Choose your intensity level (IDLE, ACTIVE, or HYPER) and start finding hash patterns. The rarer the pattern, the more points you earn!</p>
            
            <h4>Q: What are the different hash patterns?</h4>
            <p>Hash patterns represent different difficulty levels:</p>
            <ul>
                <li><strong>🔹 21</strong> - Basic pattern (0.1 points)</li>
                <li><strong>⚡ 21e8</strong> - Standard pattern (1 point) + 10x bump for posts</li>
                <li><strong>🔥 21e80</strong> - Good pattern (5 points)</li>
                <li><strong>💎 21e800</strong> - Rare pattern (25 points)</li>
                <li><strong>🌟 21e8000</strong> - Epic pattern (125 points)</li>
                <li><strong>💫 000021e8</strong> - Legendary pattern (625 points)</li>
            </ul>
            
            <h4>Q: What is the 21e8 bump system?</h4>
            <p>Posts whose SHA256 hash starts with "21e8" automatically receive a 10x bump multiplier! This is calculated in real-time and displayed with a 🔥 indicator. It's a way to reward computational luck and effort.</p>

            <h3>📋 Forum Basics</h3>
            
            <h4>Q: How do I quote posts?</h4>
            <p>Use >>PostID format where PostID is the post number, or simply click on post numbers to automatically quote them in your reply.</p>
            
            <h4>Q: What is greentext?</h4>
            <p>Lines starting with > appear in green text. This is traditionally used for quoting, storytelling, or creating narrative formats in posts.</p>
            
            <h4>Q: How do threads get bumped?</h4>
            <p>Threads move up the board when they receive new replies. The bump system is enhanced by proof-of-work mining - posts with better hash patterns give larger bumps to their threads.</p>
            
            <h4>Q: What does "sage" mean?</h4>
            <p>Sage is a way to reply to a thread without bumping it to the top. Use it when your post doesn't contribute new discussion to the main topic.</p>

            <h3>🎨 Themes & Interface</h3>
            
            <h4>Q: How do I change themes?</h4>
            <p>Use the theme switcher in the navigation bar. Choose between:</p>
            <ul>
                <li><strong>Business (ビジネス)</strong> - Professional, corporate aesthetic</li>
                <li><strong>Pleasure (楽しみ)</strong> - Neon, cyberpunk aesthetic</li>
            </ul>
            
            <h4>Q: What are the different boards?</h4>
            <p>Each board has its own focus:</p>
            <ul>
                <li><strong>/gen/</strong> - General discussion and random topics</li>
                <li><strong>/mov/</strong> - Movies, TV shows, and entertainment</li>
                <li><strong>/etc/</strong> - Everything else that doesn't fit elsewhere</li>
                <li><strong>/biz/</strong> - Business, economics, and financial discussion</li>
            </ul>

            <h3>📸 Media & Files</h3>
            
            <h4>Q: What image formats are supported?</h4>
            <p>JPG, PNG, GIF, and WebP formats up to 10MB per image. Images are automatically optimized for web viewing.</p>
            
            <h4>Q: Can I upload multiple images?</h4>
            <p>Currently, one image per post is supported. Choose your image wisely to maximize impact!</p>

            <h3>🔧 Technical Questions</h3>
            
            <h4>Q: How is the hash calculated for posts?</h4>
            <p>Post hashes are calculated using SHA256 on the post content, timestamp, and other metadata. The hash preview appears below each post in monospace font.</p>
            
            <h4>Q: Why is my proof-of-work not being accepted?</h4>
            <p>Common issues include:</p>
            <ul>
                <li>Hash pattern doesn't match the submitted pattern</li>
                <li>Duplicate proof (hash already exists)</li>
                <li>Incorrect data format (should be global:haichan:timestamp:nonce)</li>
                <li>Calculation error in SHA256 hashing</li>
            </ul>
            
            <h4>Q: What's the global hashrate?</h4>
            <p>The global hashrate is estimated from recent proof submissions across all users. It's displayed on the homepage and represents the collective computational power of the Haichan community.</p>

            <h3>🔒 Security & Privacy</h3>
            
            <h4>Q: Is my data secure?</h4>
            <p>Haichan uses industry-standard security practices. However, remember that this is a public forum - don't post personal information you wouldn't want to be public.</p>
            
            <h4>Q: Can I delete my posts?</h4>
            <p>Post deletion depends on board rules and community guidelines. Generally, posts become part of the permanent discussion record once submitted.</p>

            <h3>🚀 Advanced Features</h3>
            
            <h4>Q: What are mining sessions?</h4>
            <p>Mining sessions track your continuous proof-of-work activity. Longer sessions may unlock additional features or recognition in the future.</p>
            
            <h4>Q: How do thread bump scores work?</h4>
            <p>Thread bump scores accumulate based on the quality of hash patterns in replies. Higher scores mean more prominent positioning and greater thread visibility.</p>

            <div style="margin-top: 30px; padding: 15px; background: rgba(112, 139, 117, 0.1); border-left: 4px solid #708B75; border-radius: 4px;">
                <p><strong>Still have questions?</strong> Join the discussion on /gen/ or check the latest announcements. The Haichan community is always here to help newcomers learn the ropes! ⛏️</p>
            </div>
        </div>
    </div>
</div>
@endsection
