@extends('layout')

@section('title', 'Edit Profile - Haichan')

@section('content')
<div style="min-height: 100vh; background: var(--primary-bg); color: var(--text-primary); padding: 20px;">

    <!-- Header -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 3px solid var(--accent-color); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--accent-color); margin: 0;">
                ✏️ EDIT PROFILE
            </h1>
            <div style="color: var(--text-secondary);">
                Customize your public profile information
            </div>
        </div>
        <div>
            <a href="/user/dashboard" style="background: var(--accent-color); color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; margin: 5px; font-size: 14px;">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div style="background: #E8F5E8; border: 2px solid #4CAF50; padding: 15px; margin-bottom: 20px; border-radius: 8px; color: #2E7D32; font-size: 14px;">
        {{ session('success') }}
    </div>
    @endif

    <!-- Error Messages -->
    @if($errors->any())
    <div style="background: #FFE6E6; border: 2px solid #FF6B6B; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
        @foreach($errors->all() as $error)
            <div style="color: #D63031; font-size: 13px; margin: 5px 0;">• {{ $error }}</div>
        @endforeach
    </div>
    @endif

    <!-- Profile Form -->
    <div style="background: var(--content-bg); padding: 30px; border-radius: 12px; border: 2px solid var(--border-color); max-width: 800px; margin: 0 auto;">
        
        <form method="POST" action="/user/profile/update">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                
                <!-- Left Column -->
                <div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            👤 Display Name
                        </label>
                        <input type="text" name="display_name" value="{{ old('display_name', $user->display_name) }}"
                               placeholder="Your display name (optional)"
                               style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                        <small style="color: var(--text-secondary); font-size: 12px;">Leave blank to use username: {{ $user->username }}</small>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            📍 Location
                        </label>
                        <input type="text" name="location" value="{{ old('location', $user->location) }}"
                               placeholder="Your location"
                               style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            🌐 Website
                        </label>
                        <input type="url" name="website" value="{{ old('website', $user->website) }}"
                               placeholder="https://your-website.com"
                               style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            📧 Email
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="your@email.com"
                               style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                        <label style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                            <input type="checkbox" name="show_email" value="1" {{ old('show_email', $user->show_email) ? 'checked' : '' }}>
                            <span style="font-size: 12px; color: var(--text-secondary);">Make email public on profile</span>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            🌍 Timezone
                        </label>
                        <select name="timezone" style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                            @php
                            $timezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Asia/Shanghai', 'Australia/Sydney'];
                            @endphp
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            📝 Bio
                        </label>
                        <textarea name="bio" rows="4" placeholder="Tell us about yourself..."
                                  style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box; resize: vertical;">{{ old('bio', $user->bio) }}</textarea>
                        <small style="color: var(--text-secondary); font-size: 12px;">Max 1000 characters</small>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                            ✍️ Signature
                        </label>
                        <textarea name="signature" rows="3" placeholder="Your forum signature..."
                                  style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box; resize: vertical;">{{ old('signature', $user->signature) }}</textarea>
                        <small style="color: var(--text-secondary); font-size: 12px;">Max 500 characters. Shown at the bottom of your posts.</small>
                    </div>

                    <!-- Social Links -->
                    <div style="background: var(--secondary-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">🔗 Social Links</h3>
                        
                        @php
                        $socialLinks = old('social_links', $user->social_links ? json_decode($user->social_links, true) : []);
                        @endphp

                        <div style="margin-bottom: 10px;">
                            <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">
                                🐦 Twitter
                            </label>
                            <input type="text" name="social_twitter" value="{{ old('social_twitter', $socialLinks['twitter'] ?? '') }}"
                                   placeholder="@username or https://twitter.com/username"
                                   style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--primary-bg); color: var(--text-primary); font-size: 12px; box-sizing: border-box;">
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">
                                🐙 GitHub
                            </label>
                            <input type="text" name="social_github" value="{{ old('social_github', $socialLinks['github'] ?? '') }}"
                                   placeholder="username or https://github.com/username"
                                   style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--primary-bg); color: var(--text-primary); font-size: 12px; box-sizing: border-box;">
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">
                                💬 Discord
                            </label>
                            <input type="text" name="social_discord" value="{{ old('social_discord', $socialLinks['discord'] ?? '') }}"
                                   placeholder="username#1234"
                                   style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--primary-bg); color: var(--text-primary); font-size: 12px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">🔒 Privacy Settings</h3>
                
                <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;">
                    <input type="checkbox" name="profile_public" value="1" {{ old('profile_public', $user->profile_public) ? 'checked' : '' }}>
                    <span style="color: var(--text-primary);">Make profile public (visible to all users)</span>
                </label>
                
                <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 5px;">
                    If unchecked, only your username and admin badges will be visible to other users.
                </small>
            </div>

            <!-- Current Account Info -->
            <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">🔐 Account Information</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 12px;">
                    <div>
                        <strong>Username:</strong> {{ $user->username }}<br>
                        <strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}<br>
                        <strong>User ID:</strong> #{{ $user->id }}/256
                    </div>
                    <div>
                        <strong>Last Login:</strong> {{ $user->last_login ? $user->last_login->diffForHumans() : 'Never' }}<br>
                        <strong>Mining Streak:</strong> {{ $user->mining_streak }} days<br>
                        <strong>Total PoW Points:</strong> {{ number_format($user->total_pow_points) }}
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="text-align: center;">
                <button type="submit" style="background: linear-gradient(135deg, var(--accent-color), #5A7B5F); color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                    💾 UPDATE PROFILE
                </button>
            </div>
        </form>
    </div>

</div>
@endsection