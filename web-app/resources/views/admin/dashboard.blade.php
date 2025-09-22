<!DOCTYPE html>
<html lang="en" data-theme="cyberpunk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Control Panel - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
</head>
<body>

<div style="min-height: 100vh; background: var(--primary-bg); color: var(--text-primary); padding: 20px;">

    <!-- Header -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 3px solid var(--accent-color); margin-bottom: 30px;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--accent-color); margin: 0 0 10px 0; text-align: center;">
            🔐 HAICHAN ADMIN CONTROL PANEL
        </h1>
        <div style="text-align: center; color: var(--text-secondary);">
            @php
                $user = session('bitcoin_auth_user');
                $roleText = 'Admin';
                $roleColor = '#FFD700';
                if ($user && $user->admin_level == 9) {
                    $roleText = '👑 SUPER ADMIN';
                    $roleColor = '#FF6B35';
                } elseif ($user && $user->admin_level == 7) {
                    $roleText = '🛡️ SUPER MOD';
                    $roleColor = '#4CAF50';
                } elseif ($user && $user->admin_level >= 5) {
                    $roleText = '⚔️ MODERATOR';
                    $roleColor = '#2196F3';
                }
            @endphp
            <span style="color: {{ $roleColor }}; font-weight: bold;">{{ $roleText }} Level {{ $user->admin_level ?? 0 }}</span>
            <br>
            Bitcoin Address: {{ $user->address ?? '1FMhdLPGF1RsroTeqEKEoJi2H6zxf9CgDD' }}
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">

        <!-- User Stats -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">👥 USER MANAGEMENT</h3>
            <div style="font-size: 24px; font-weight: bold; color: var(--success-color);">{{ $totalUsers }}/256</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Total Users</div>
            <div style="margin-top: 10px;">
                <div style="color: var(--warning-color);">{{ $remainingSlots }} slots remaining</div>
            </div>
        </div>

        <!-- Mining Stats -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">⚡ MINING STATS</h3>
            <div style="font-size: 24px; font-weight: bold; color: var(--success-color);">{{ $totalProofs }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Total Proofs</div>
            <div style="margin-top: 10px;">
                <div style="color: var(--warning-color);">{{ $networkHashrate }} H/s</div>
            </div>
        </div>

        <!-- Forum Stats -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">📝 FORUM STATS</h3>
            <div style="font-size: 24px; font-weight: bold; color: var(--success-color);">{{ $totalThreads }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Total Threads</div>
            <div style="margin-top: 10px;">
                <div style="color: var(--warning-color);">{{ $totalPosts }} posts</div>
            </div>
        </div>

        <!-- Invite Codes -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">🎟️ INVITE CODES</h3>
            <div style="font-size: 24px; font-weight: bold; color: var(--success-color);">{{ $activeInvites }}</div>
            <div style="color: var(--text-secondary); font-size: 14px;">Active Codes</div>
            <div style="margin-top: 10px;">
                <div style="color: var(--warning-color);">{{ $totalUses }} uses available</div>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">

        <!-- User Management -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">👥 USER MANAGEMENT</h3>

            <div style="margin-bottom: 15px;">
                <a href="/admin/users" style="display: block; background: var(--accent-color); color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 10px;">
                    View All Users
                </a>
                <a href="/admin/users/banned" style="display: block; background: var(--highlight-color); color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 10px;">
                    Banned Users
                </a>
                <a href="/admin/users/admins" style="display: block; background: var(--success-color); color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center;">
                    Admin Users
                </a>
            </div>
        </div>

        <!-- Genesis Codes -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">🎟️ GENESIS CODES</h3>

            <form method="POST" action="/admin/genesis-codes" style="margin-bottom: 15px;">
                @csrf
                <input type="text" name="code" placeholder="New genesis code..."
                       style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg); color: var(--text-primary); margin-bottom: 10px; box-sizing: border-box;">
                <input type="number" name="uses" placeholder="Max uses" min="1" max="50" value="10"
                       style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg); color: var(--text-primary); margin-bottom: 10px; box-sizing: border-box;">
                <button type="submit" style="width: 100%; background: var(--accent-color); color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer;">
                    Create Genesis Code
                </button>
            </form>
        </div>

        <!-- System Actions -->
        <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color);">
            <h3 style="color: var(--accent-color); margin-top: 0;">🛠️ SYSTEM ACTIONS</h3>

            <div style="margin-bottom: 15px;">
                <a href="/admin/mining" style="display: block; background: var(--warning-color); color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 10px;">
                    Mining Dashboard
                </a>
                <a href="/admin/forum" style="display: block; background: var(--success-color); color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center; margin-bottom: 10px;">
                    Forum Moderation
                </a>
                <a href="/admin/logs" style="display: block; background: var(--highlight-color); color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center;">
                    System Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-top: 30px;">
        <h3 style="color: var(--accent-color); margin-top: 0;">📊 RECENT ACTIVITY</h3>
        <div id="recent-activity" style="max-height: 300px; overflow-y: auto;">
            <!-- Activity will be loaded here -->
            <div style="color: var(--text-secondary); text-align: center; padding: 20px;">
                Loading recent activity...
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="text-align: center; margin-top: 30px; padding: 20px;">
        <a href="/" style="color: var(--accent-color); text-decoration: none;">← Back to Haichan</a>
        <span style="color: var(--text-secondary); margin: 0 20px;">|</span>
        <a href="/auth/logout" style="color: var(--highlight-color); text-decoration: none;">Logout</a>
    </div>

</div>

<script>
// Load recent activity
async function loadRecentActivity() {
    try {
        const response = await fetch('/admin/api/activity');
        const activities = await response.json();

        const container = document.getElementById('recent-activity');
        container.innerHTML = activities.map(activity => `
            <div style="border-bottom: 1px solid var(--border-color); padding: 10px; display: flex; justify-content: space-between;">
                <span>${activity.description}</span>
                <span style="color: var(--text-secondary); font-size: 12px;">${activity.time}</span>
            </div>
        `).join('');
    } catch (error) {
        document.getElementById('recent-activity').innerHTML =
            '<div style="color: var(--highlight-color); text-align: center;">Failed to load activity</div>';
    }
}

// Load activity on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentActivity();
    setInterval(loadRecentActivity, 30000); // Refresh every 30 seconds
});
</script>

</body>
</html>