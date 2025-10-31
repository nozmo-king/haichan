@extends('layout')

@section('title', 'Login - Haichan')

@section('content')
<div style="max-width: 500px; margin: 50px auto; background: var(--primary-bg); padding: 30px; border-radius: 12px; border: 2px solid var(--border-color); box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
    <h1 style="text-align: center; margin-bottom: 30px; color: var(--text-primary); font-family: 'Nova Cut', serif;">
        🔐 Login to Haichan
    </h1>

    @if ($errors->any())
        <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #c62828;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div style="background: #e8f5e8; color: #2e7d32; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #2e7d32;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Primary Login: Username/Password -->
    <form method="POST" action="{{ route('login') }}" style="margin-bottom: 30px;">
        @csrf
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; color: var(--text-primary); font-weight: 600; margin-bottom: 8px;">
                👤 Username or Bitcoin Address
            </label>
            <input 
                type="text" 
                name="login_identifier" 
                value="{{ old('login_identifier') }}" 
                required 
                placeholder="Enter your username or Bitcoin address"
                style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; color: var(--text-primary); font-weight: 600; margin-bottom: 8px;">
                🔒 Password
            </label>
            <input 
                type="password" 
                name="password" 
                required 
                placeholder="Enter your password"
                style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
        </div>

        <button type="submit" style="width: 100%; background: var(--accent-green); color: white; padding: 14px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
            🚀 Login
        </button>
    </form>

    <!-- Backup Login: Private Key -->
    <details style="margin-top: 30px; padding: 20px; background: var(--secondary-bg); border-radius: 8px; border: 1px solid var(--border-color);">
        <summary style="cursor: pointer; font-weight: bold; color: var(--text-primary); margin-bottom: 15px;">
            🔧 Emergency Backup Login (Private Key)
        </summary>
        
        <form method="POST" action="{{ url('/login-backup') }}">
            @csrf
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-primary); font-weight: 600; margin-bottom: 8px;">
                    🔑 Bitcoin Private Key
                </label>
                <textarea 
                    name="private_key" 
                    rows="3" 
                    placeholder="Enter your private key from backup file (any format)"
                    style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-size: 12px; font-family: monospace; box-sizing: border-box; resize: vertical;"></textarea>
                <small style="color: var(--text-secondary); font-size: 11px;">
                    Use the private key from your backup file. Works with both old and new key formats.
                </small>
            </div>

            <details style="margin-bottom: 15px; padding: 15px; background: var(--primary-bg); border-radius: 6px; border: 1px solid var(--border-color);">
                <summary style="cursor: pointer; font-weight: bold; color: var(--text-primary); margin-bottom: 10px;">
                    🔒 Set New Password (Optional)
                </summary>
                
                <div style="margin-bottom: 10px;">
                    <label style="display: block; color: var(--text-primary); font-weight: 600; margin-bottom: 5px;">
                        New Password
                    </label>
                    <input 
                        type="password" 
                        name="new_password" 
                        placeholder="Enter new password (optional)"
                        style="width: 100%; padding: 8px; border: 2px solid var(--border-color); border-radius: 4px; background: var(--primary-bg); color: var(--text-primary); font-size: 12px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; color: var(--text-primary); font-weight: 600; margin-bottom: 5px;">
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        placeholder="Confirm new password"
                        style="width: 100%; padding: 8px; border: 2px solid var(--border-color); border-radius: 4px; background: var(--primary-bg); color: var(--text-primary); font-size: 12px; box-sizing: border-box;">
                </div>
                
                <small style="color: var(--text-secondary); font-size: 10px;">
                    Set a new password to make future logins easier. Leave blank to keep current password.
                </small>
            </details>

            <button type="submit" style="width: 100%; background: #ff9800; color: white; padding: 12px; border: none; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer;">
                🔓 Emergency Login
            </button>
        </form>
    </details>

    <!-- Registration Link -->
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
        <p style="color: var(--text-secondary); margin-bottom: 10px;">Don't have an account yet?</p>
        <a href="{{ route('register') }}" style="color: var(--accent-green); text-decoration: none; font-weight: bold;">
            ✨ Register for Haichan
        </a>
    </div>
</div>
@endsection