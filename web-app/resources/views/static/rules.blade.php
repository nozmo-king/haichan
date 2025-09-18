@extends('layout')

@section('title', 'Rules - Haichan')

@section('content')
@include('components.navigation')

<div class="container" style="padding: 20px;">
    <div class="post-container" style="margin: 0 auto; max-width: 800px;">
        <div class="post-header">
            <h2>📜 Haichan Community Rules</h2>
        </div>
        
        <div class="post-content" style="padding: 20px; line-height: 1.6;">
            <h3>🌟 Core Principles</h3>
            <ol>
                <li><strong>No illegal content</strong> - Respect all applicable laws and regulations</li>
                <li><strong>No spam or flooding</strong> - Quality over quantity in all posts</li>
                <li><strong>Stay respectful</strong> - Engage constructively with other community members</li>
                <li><strong>Use proof-of-work responsibly</strong> - Mining is a privilege, not a right</li>
            </ol>

            <h3>⛏️ Mining & Proof-of-Work Rules</h3>
            <ul>
                <li><strong>Fair mining practices</strong> - No botting, scripting, or automated mining</li>
                <li><strong>21e8 detection rewards</strong> - Posts with SHA256 hashes starting with 21e8 receive 10x bump effects</li>
                <li><strong>Mining patterns:</strong>
                    <ul style="margin-left: 20px; list-style-type: disc;">
                        <li>🔹 21 - Basic (0.1 points)</li>
                        <li>⚡ 21e8 - Standard (1 point)</li>
                        <li>🔥 21e80 - Good (5 points)</li>
                        <li>💎 21e800 - Rare (25 points)</li>
                        <li>🌟 21e8000 - Epic (125 points)</li>
                        <li>💫 000021e8 - Legendary (625 points)</li>
                    </ul>
                </li>
                <li><strong>No duplicate proofs</strong> - Each hash can only be submitted once</li>
            </ul>

            <h3>📋 Board-Specific Guidelines</h3>
            <ul>
                <li><strong>/gen/ - General</strong> - Random topics and general discussion</li>
                <li><strong>/mov/ - Movies & TV</strong> - Entertainment media discussion</li>
                <li><strong>/etc/ - Et Cetera</strong> - Everything else that doesn't fit elsewhere</li>
                <li><strong>/biz/ - Business & Finance</strong> - Professional and financial topics</li>
            </ul>

            <h3>📸 Media & Content Rules</h3>
            <ul>
                <li><strong>Image limit:</strong> Maximum 10MB per image</li>
                <li><strong>Supported formats:</strong> JPG, PNG, GIF, WebP</li>
                <li><strong>No NSFW content</strong> without appropriate board designation</li>
                <li><strong>Original content preferred</strong> - Credit sources when possible</li>
            </ul>

            <h3>🔗 Posting Guidelines</h3>
            <ul>
                <li><strong>Quoting:</strong> Use >>PostID to reference other posts</li>
                <li><strong>Greentext:</strong> Lines starting with > appear in green</li>
                <li><strong>Thread bumping:</strong> Contribute meaningfully to discussions</li>
                <li><strong>Sage posts:</strong> Use sage when not contributing to thread topic</li>
            </ul>

            <h3>🚫 Prohibited Content</h3>
            <ul style="color: #d32f2f;">
                <li>Illegal material of any kind</li>
                <li>Doxxing or personal information sharing</li>
                <li>Coordinated raids or harassment</li>
                <li>Commercial spam or excessive advertising</li>
                <li>Malicious links or downloads</li>
            </ul>

            <h3>⚖️ Enforcement</h3>
            <p>Rule violations may result in post deletion, thread locks, or temporary restrictions. Severe or repeated violations may result in permanent bans. Appeals can be submitted through the contact system.</p>

            <h3>🔄 Updates</h3>
            <p>These rules are subject to change as the community evolves. Major updates will be announced site-wide.</p>

            <div style="margin-top: 30px; padding: 15px; background: rgba(112, 139, 117, 0.1); border-left: 4px solid #708B75; border-radius: 4px;">
                <p><strong>Remember:</strong> Haichan is built on proof-of-work principles. Every contribution matters, and quality engagement strengthens our community. Happy posting! 🌟</p>
            </div>
        </div>
    </div>
</div>
@endsection
