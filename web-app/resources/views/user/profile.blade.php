@extends('layout')

@section('title', $user->display_name ?: $user->username . ' - Haichan')

@section('content')
<div style="min-height: 100vh; background: var(--primary-bg); color: var(--text-primary); padding: 20px;">

    <!-- Header -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 3px solid var(--accent-color); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--accent-color); margin: 0; display: flex; align-items: center; gap: 10px;">
                👤 {{ $user->display_name ?: $user->username }}
                @if($user->admin_level > 0)
                    @if($user->admin_level >= 9)
                        <span style="color: #FF6B35; font-size: 20px;">👑</span>
                    @elseif($user->admin_level >= 7)
                        <span style="color: #4CAF50; font-size: 20px;">🛡️</span>
                    @elseif($user->admin_level >= 5)
                        <span style="color: #2196F3; font-size: 20px;">⚔️</span>
                    @else
                        <span style="color: #FFD700; font-size: 20px;">🔱</span>
                    @endif
                @endif
                @if($user->tripcode)
                    <span style="font-family: 'Courier New', monospace; color: var(--accent-color); font-size: 16px; font-weight: bold;">{{ $user->tripcode }}</span>
                @endif
            </h1>
            <div style="color: var(--text-secondary); margin-top: 5px;">
                @{{ $user->username }} • User #{{ $user->id }}/256 • Member since {{ $user->created_at->format('M Y') }}
            </div>
        </div>
        <div>
            <a href="/" style="background: var(--accent-color); color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-size: 14px;">← Back to Forum</a>
            @if(session('bitcoin_auth_id') == $user->id)
                <a href="/user/profile/edit" style="background: #4CAF50; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; margin-left: 10px; font-size: 14px;">✏️ Edit Profile</a>
            @endif
        </div>
    </div>

    <!-- Check if profile is public or if it's the user's own profile -->
    @if($user->profile_public || session('bitcoin_auth_id') == $user->id)
    
    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 20px;">
        
        <!-- Main Profile Content -->
        <div>
            
            <!-- Bio Section -->
            @if($user->bio)
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0;">📝 About</h3>
                <div style="line-height: 1.6; white-space: pre-line;">{{ $user->bio }}</div>
            </div>
            @endif

            <!-- User Stats -->
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0;">📊 Forum Statistics</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div style="text-align: center; background: var(--secondary-bg); padding: 15px; border-radius: 6px;">
                        <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">{{ $stats['posts'] }}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">Posts Made</div>
                    </div>
                    <div style="text-align: center; background: var(--secondary-bg); padding: 15px; border-radius: 6px;">
                        <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">{{ $stats['threads'] }}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">Threads Created</div>
                    </div>
                    <div style="text-align: center; background: var(--secondary-bg); padding: 15px; border-radius: 6px;">
                        <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">{{ $user->mining_streak }}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">Mining Streak</div>
                    </div>
                    <div style="text-align: center; background: var(--secondary-bg); padding: 15px; border-radius: 6px;">
                        <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">{{ number_format($user->total_pow_points) }}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">PoW Points</div>
                    </div>
                </div>
            </div>

            <!-- Signature -->
            @if($user->signature)
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0;">✍️ Signature</h3>
                <div style="font-style: italic; color: var(--text-secondary); line-height: 1.4; white-space: pre-line;">{{ $user->signature }}</div>
            </div>
            @endif

        </div>

        <!-- Sidebar -->
        <div>
            
            <!-- Contact Information -->
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0;">📬 Contact Info</h3>
                
                @if($user->location)
                <div style="margin-bottom: 10px;">
                    <strong>📍 Location:</strong><br>
                    <span style="color: var(--text-secondary);">{{ $user->location }}</span>
                </div>
                @endif

                @if($user->website)
                <div style="margin-bottom: 10px;">
                    <strong>🌐 Website:</strong><br>
                    <a href="{{ $user->website }}" target="_blank" style="color: var(--accent-color); word-break: break-all;">{{ $user->website }}</a>
                </div>
                @endif

                @if($user->show_email && $user->email)
                <div style="margin-bottom: 10px;">
                    <strong>📧 Email:</strong><br>
                    <a href="mailto:{{ $user->email }}" style="color: var(--accent-color);">{{ $user->email }}</a>
                </div>
                @endif

                @if($user->timezone)
                <div style="margin-bottom: 10px;">
                    <strong>🌍 Timezone:</strong><br>
                    <span style="color: var(--text-secondary);">{{ $user->timezone }}</span>
                </div>
                @endif
            </div>

            <!-- Social Links -->
            @if($user->social_links)
            @php
            $socialLinks = json_decode($user->social_links, true);
            @endphp
            @if(!empty($socialLinks))
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0;">🔗 Social Links</h3>
                
                @if(isset($socialLinks['twitter']) && $socialLinks['twitter'])
                <div style="margin-bottom: 8px;">
                    <a href="{{ str_starts_with($socialLinks['twitter'], 'http') ? $socialLinks['twitter'] : 'https://twitter.com/' . ltrim($socialLinks['twitter'], '@') }}" 
                       target="_blank" 
                       style="color: #1DA1F2; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                        🐦 Twitter
                    </a>
                </div>
                @endif

                @if(isset($socialLinks['github']) && $socialLinks['github'])
                <div style="margin-bottom: 8px;">
                    <a href="{{ str_starts_with($socialLinks['github'], 'http') ? $socialLinks['github'] : 'https://github.com/' . $socialLinks['github'] }}" 
                       target="_blank" 
                       style="color: #24292e; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                        🐙 GitHub
                    </a>
                </div>
                @endif

                @if(isset($socialLinks['discord']) && $socialLinks['discord'])
                <div style="margin-bottom: 8px;">
                    <span style="color: #7289DA; display: flex; align-items: center; gap: 5px;">
                        💬 {{ $socialLinks['discord'] }}
                    </span>
                </div>
                @endif
            </div>
            @endif
            @endif

            <!-- Account Details -->
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--border-color); margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0;">🔐 Account Details</h3>
                
                <div style="font-size: 12px; line-height: 1.6;">
                    <div><strong>User ID:</strong> #{{ $user->id }}/256</div>
                    <div><strong>Username:</strong> {{ $user->username }}</div>
                    <div><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</div>
                    <div><strong>Last Login:</strong> {{ $user->last_login ? $user->last_login->diffForHumans() : 'Never' }}</div>
                    @if($user->admin_level > 0)
                    <div><strong>Role:</strong> 
                        @if($user->admin_level >= 9)
                            <span style="color: #FF6B35;">Super Admin</span>
                        @elseif($user->admin_level >= 7)
                            <span style="color: #4CAF50;">Super Moderator</span>
                        @elseif($user->admin_level >= 5)
                            <span style="color: #2196F3;">Moderator</span>
                        @else
                            <span style="color: #FFD700;">Admin</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Private Profile Message -->
    <div style="background: var(--content-bg); padding: 40px; border-radius: 12px; border: 2px solid var(--border-color); text-align: center;">
        <div style="font-size: 64px; margin-bottom: 20px;">🔒</div>
        <h2 style="color: var(--accent-color); margin: 0 0 15px 0;">Private Profile</h2>
        <p style="color: var(--text-secondary); font-size: 16px;">
            This user has set their profile to private. Only basic information is visible.
        </p>
        
        <!-- Basic Info Only -->
        <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; margin: 20px auto; max-width: 400px;">
            <div style="font-size: 14px; line-height: 1.8;">
                <div><strong>Username:</strong> {{ $user->username }}</div>
                <div><strong>User ID:</strong> #{{ $user->id }}/256</div>
                <div><strong>Joined:</strong> {{ $user->created_at->format('M Y') }}</div>
                @if($user->admin_level > 0)
                <div><strong>Role:</strong> 
                    @if($user->admin_level >= 9)
                        <span style="color: #FF6B35;">Super Admin</span>
                    @elseif($user->admin_level >= 7)
                        <span style="color: #4CAF50;">Super Moderator</span>
                    @elseif($user->admin_level >= 5)
                        <span style="color: #2196F3;">Moderator</span>
                    @else
                        <span style="color: #FFD700;">Admin</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
@endsection