<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style>
        body {
            background: var(--background);
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            color: var(--text-primary);
        }
        
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background: var(--content-bg);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 30px;
        }
        
        h2 {
            color: var(--accent-color);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-primary);
        }
        
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: var(--secondary-bg);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .error {
            color: #ff4444;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--highlight-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .submit-btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }
        
        .status-message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }
        
        .status-message.mining {
            background: var(--warning-bg);
            color: var(--warning-text);
        }
        
        .status-message.success {
            background: var(--success-bg);
            color: var(--success-text);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            color: var(--text-primary);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>🚀 Create Your Account</h2>
        
        <form id="register-form" action="{{ route('auth.register.simple') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="friend_code">Friend Code</label>
                <input type="text" 
                       id="friend_code" 
                       name="friend_code" 
                       value="{{ old('friend_code', $friendCode ?? '') }}" 
                       placeholder="Enter your friend code (e.g., GENESIS2025)"
                       required>
                @error('friend_code')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="{{ old('username') }}" 
                       placeholder="Choose a unique username"
                       pattern="[a-zA-Z0-9_]{3,20}"
                       maxlength="20"
                       required>
                <small style="color: var(--text-secondary);">3-20 characters, letters, numbers, and underscores only</small>
                @error('username')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Choose a strong password"
                       minlength="8"
                       required>
                <small style="color: var(--text-secondary);">Minimum 8 characters</small>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Confirm your password"
                       minlength="8"
                       required>
            </div>
            
            <div id="status-message" class="status-message" style="display: none;"></div>
            
            <button type="submit" class="submit-btn" id="submit-btn">
                Create Account
            </button>
            
            @if ($errors->has('message'))
                <div class="error" style="margin-top: 15px;">{{ $errors->first('message') }}</div>
            @endif
        </form>
        
        <a href="/login" class="back-link">← Back to Login</a>
    </div>
    
    <script>
        const form = document.getElementById('register-form');
        const submitBtn = document.getElementById('submit-btn');
        const statusMessage = document.getElementById('status-message');
        
        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Account...';
            statusMessage.style.display = 'block';
            statusMessage.className = 'status-message mining';
            statusMessage.textContent = '⚙️ Generating your cryptographic keys...';
        });
        
        // Check username availability
        const usernameInput = document.getElementById('username');
        let checkTimeout;
        
        usernameInput.addEventListener('input', function() {
            clearTimeout(checkTimeout);
            const username = this.value;
            
            if (username.length >= 3) {
                checkTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch('/auth/check-username', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ username })
                        });
                        
                        const data = await response.json();
                        
                        const existingError = usernameInput.parentElement.querySelector('.error');
                        if (existingError) {
                            existingError.remove();
                        }
                        
                        if (!data.available) {
                            const error = document.createElement('div');
                            error.className = 'error';
                            error.textContent = 'Username already taken';
                            usernameInput.parentElement.appendChild(error);
                        }
                    } catch (error) {
                        console.error('Username check failed:', error);
                    }
                }, 500);
            }
        });
    </script>
</body>
</html>