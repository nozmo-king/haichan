@extends('layout')

@section('title', 'FAQ - Haichan')

@section('content')

<div style="max-width: 900px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: var(--content-bg); border: 3px solid var(--accent-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--accent-color); margin: 0 0 10px 0;">
            ❓ FREQUENTLY ASKED QUESTIONS
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">
            Everything you need to know about Haichan
        </p>
    </div>

    <!-- General Questions -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
            🌐 General
        </h2>
        
        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">What is Haichan?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Haichan is an imageboard with integrated cryptocurrency authentication and proof-of-work mining. Post anonymously or with your Bitcoin-based identity while earning points through mining.
            </p>
        </div>

        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">Do I need an account?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                No! You can browse and post anonymously. However, creating an account lets you track your points, gain levels, and use exclusive features.
            </p>
        </div>

        <div>
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">How do I register?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                You need an invite code to register. Invite codes are distributed through the community or can be earned through participation.
            </p>
        </div>
    </div>

    <!-- Mining Questions -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
            ⛏️ Mining
        </h2>
        
        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">What is mining?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Mining uses proof-of-work (SHA-256 hashing) to earn points. You can mine by hovering over posts or using the dedicated mining page.
            </p>
        </div>

        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">What are points used for?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Points will soon be spendable in the shop for perks like custom colors, badges, featured posts, and more. Keep mining to prepare!
            </p>
        </div>

        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">What are levels?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                You gain 1 level for every 1,000 points. Higher levels give you increased mining power (10% bonus per level).
            </p>
        </div>

        <div>
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">How do I mine faster?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Level up to gain mining power bonuses. Use invite codes with bonuses (Premium gives 1.5x, Genesis gives 2.0x). Keep your browser active while mining.
            </p>
        </div>
    </div>

    <!-- Posting Questions -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
            📝 Posting
        </h2>
        
        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">How do I post?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Navigate to a board, click "New Thread" to start a topic, or reply to existing threads. You can post anonymously or with your account.
            </p>
        </div>

        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">Can I upload images?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Yes! You can upload images when creating threads or replies. Supported formats include JPG, PNG, and GIF.
            </p>
        </div>

        <div>
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">Can I edit or delete my posts?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Anonymous posts cannot be edited or deleted. Account posts may have limited editing capabilities depending on board settings.
            </p>
        </div>
    </div>

    <!-- Technical Questions -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
            🔧 Technical
        </h2>
        
        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">What is proof-of-work?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Proof-of-work is a cryptographic puzzle (SHA-256 hashing) that your browser solves. It proves computational work was done, similar to Bitcoin mining but on a smaller scale.
            </p>
        </div>

        <div style="margin-bottom: 25px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">Is mining safe for my computer?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Yes! Mining happens in your browser using JavaScript and WebAssembly. It's lightweight and stops when you close the page or stop mining.
            </p>
        </div>

        <div>
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">Does it use my GPU?</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                No, it uses CPU only. The mining is designed to be lightweight and not impact your browsing experience.
            </p>
        </div>
    </div>

    <!-- Still Have Questions? -->
    <div style="margin-top: 30px; padding: 20px; background: rgba(154, 184, 122, 0.1); border: 1px solid var(--border-color); border-radius: 6px; text-align: center;">
        <h3 style="color: var(--accent-color); margin: 0 0 10px 0; font-size: 18px;">Still have questions?</h3>
        <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 15px 0;">
            Check the <a href="/rules" style="color: var(--accent-color); text-decoration: none; font-weight: bold;">Rules</a> page or ask in the community boards.
        </p>
        <a href="/boards" style="display: inline-block; padding: 10px 20px; background: var(--accent-color); color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
            📋 Go to Boards
        </a>
    </div>

</div>

@endsection
