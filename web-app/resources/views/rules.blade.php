@extends('layout')

@section('title', 'Rules - Haichan')

@section('content')

<div style="max-width: 900px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: var(--content-bg); border: 3px solid var(--accent-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--accent-color); margin: 0 0 10px 0;">
            📜 HAICHAN RULES
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">
            Community guidelines and board rules
        </p>
    </div>

    <!-- Global Rules -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
            🌐 Global Rules
        </h2>
        
        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">1. No Illegal Content</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Do not post anything illegal under US law. This includes but is not limited to: child exploitation material, terrorism-related content, or direct threats of violence.
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">2. Respect the Board Topics</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Post content relevant to the board's designated topic. Off-topic posts may be moved or deleted.
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">3. No Spam</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Don't flood boards with repetitive posts, advertisements, or bot-generated content. Quality over quantity.
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">4. Use Appropriate Content Warnings</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                NSFW content is allowed in designated boards but must be properly tagged. Use spoilers for sensitive content.
            </p>
        </div>

        <div>
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">5. Respect Privacy</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Do not post personal information (doxxing) of others. This includes real names, addresses, phone numbers, or other identifying information.
            </p>
        </div>
    </div>

    <!-- Mining Rules -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 20px;">
        <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
            ⛏️ Mining Rules
        </h2>
        
        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">Fair Mining</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Mining should be done through legitimate means. Attempts to exploit or manipulate the mining system will result in point deductions or bans.
            </p>
        </div>

        <div>
            <h3 style="color: var(--accent-color); font-size: 16px; margin: 0 0 10px 0;">One Account Per Person</h3>
            <p style="color: var(--text-primary); margin: 0; line-height: 1.6;">
                Each person may only have one account. Creating multiple accounts to farm points is prohibited.
            </p>
        </div>
    </div>

    <!-- Consequences -->
    <div style="background: var(--content-bg); border: 2px solid #ff6b6b; border-radius: 8px; padding: 25px;">
        <h2 style="color: #ff6b6b; margin: 0 0 20px 0; font-size: 24px; border-bottom: 2px solid #ff6b6b; padding-bottom: 10px;">
            ⚠️ Consequences
        </h2>
        <p style="color: var(--text-primary); margin: 0 0 15px 0; line-height: 1.6;">
            Rule violations may result in:
        </p>
        <ul style="color: var(--text-primary); line-height: 1.8; margin: 0;">
            <li>Post deletion</li>
            <li>Temporary ban (hours to days)</li>
            <li>Permanent ban for severe violations</li>
            <li>Point deductions for mining abuse</li>
        </ul>
    </div>

    <!-- Footer Note -->
    <div style="margin-top: 30px; padding: 20px; background: rgba(154, 184, 122, 0.1); border: 1px solid var(--border-color); border-radius: 6px; text-align: center;">
        <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">
            Rules are enforced at moderator discretion. Use common sense and be respectful to others.
        </p>
        <p style="color: var(--text-secondary); font-size: 13px; margin: 10px 0 0 0;">
            Questions? Check the <a href="/faq" style="color: var(--accent-color); text-decoration: none; font-weight: bold;">FAQ</a> or contact an admin.
        </p>
    </div>

</div>

@endsection
