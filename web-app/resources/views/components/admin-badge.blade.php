@php
    // Admin badge component for posts
    $badgeText = '';
    $badgeColor = '';
    $badgeIcon = '';

    if ($user && $user->admin_level == 9) {
        $badgeText = 'SUPER ADMIN';
        $badgeColor = '#FF6B35';
        $badgeIcon = '👑';
    } elseif ($user && $user->admin_level == 7) {
        $badgeText = 'SUPER MOD';
        $badgeColor = '#4CAF50';
        $badgeIcon = '🛡️';
    } elseif ($user && $user->admin_level >= 5) {
        $badgeText = 'MODERATOR';
        $badgeColor = '#2196F3';
        $badgeIcon = '⚔️';
    } elseif ($user && $user->admin_level >= 1) {
        $badgeText = 'ADMIN';
        $badgeColor = '#FFD700';
        $badgeIcon = '🔱';
    }
@endphp

@if($user && $user->is_admin && $badgeText)
<span style="
    background: {{ $badgeColor }};
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
    margin-left: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    animation: adminGlow 2s infinite;
">
    {{ $badgeIcon }} {{ $badgeText }}
</span>

<style>
@keyframes adminGlow {
    0%, 100% { box-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    50% { box-shadow: 0 2px 8px {{ $badgeColor }}80; }
}
</style>
@endif