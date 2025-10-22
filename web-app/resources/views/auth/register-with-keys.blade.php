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
            max-width: 600px;
            margin: 30px auto;
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
        
        .step {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--secondary-bg);
        }
        
        .step-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-weight: bold;
            color: var(--accent-color);
        }
        
        .step-number {
            background: var(--accent-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
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
            background: var(--background);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .key-display {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            margin: 10px 0;
        }
        
        .error {
            color: #ff4444;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .button {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .generate-btn {
            background: var(--accent-color);
            color: white;
            width: 100%;
        }
        
        .generate-btn:hover:not(:disabled) {
            background: var(--accent-color-hover);
            transform: scale(1.02);
        }
        
        .submit-btn {
            width: 100%;
            background: var(--highlight-color);
            color: white;
        }
        
        .submit-btn:hover:not(:disabled) {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 12px;
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
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>🚀 Create Your Account</h2>
        
        <form id="register-form" action="{{ route('auth.register.store') }}" method="POST">
            @csrf
            
            <!-- Step 1: Friend Code -->
            <div class="step">
                <div class="step-header">
                    <div class="step-number">1</div>
                    <div>Enter Friend Code</div>
                </div>
                
                <div class="form-group">
                    <input type="text" 
                           id="friend_code" 
                           name="friend_code" 
                           value="{{ old('friend_code', request()->get('code', '')) }}" 
                           placeholder="Enter your friend code (e.g., GENESIS2025)"
                           required>
                    @error('friend_code')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Step 2: Generate Keys -->
            <div class="step">
                <div class="step-header">
                    <div class="step-number">2</div>
                    <div>Generate Your Keys</div>
                </div>
                
                <button type="button" class="button generate-btn" onclick="generateKeys()">
                    🔑 Generate Cryptographic Keys
                </button>
                
                <div id="keys-display" class="hidden">
                    <div class="warning">
                        ⚠️ SAVE THESE KEYS! You won't see them again after registration.
                    </div>
                    
                    <div class="form-group">
                        <label>Your Address:</label>
                        <div class="key-display" id="address-display"></div>
                        <input type="hidden" id="address" name="address">
                    </div>
                    
                    <div class="form-group">
                        <label>Public Key:</label>
                        <div class="key-display" id="public-key-display"></div>
                        <input type="hidden" id="public_key" name="public_key">
                    </div>
                    
                    <div class="form-group">
                        <label>Private Key (SAVE THIS!):</label>
                        <div class="key-display" id="private-key-display" style="background: #300; color: #f00;"></div>
                        <input type="hidden" id="private_key" name="private_key">
                    </div>
                    
                    <button type="button" class="button" onclick="downloadKeys()" style="background: #28a745; color: white; width: 100%; margin-top: 10px;">
                        💾 Download Backup File
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Account Details -->
            <div class="step">
                <div class="step-header">
                    <div class="step-number">3</div>
                    <div>Choose Username & Password</div>
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
            </div>
            
            <button type="submit" class="button submit-btn" id="submit-btn" disabled>
                Create Account
            </button>
            
            @if ($errors->has('message'))
                <div class="error" style="margin-top: 15px;">{{ $errors->first('message') }}</div>
            @endif
        </form>
        
        <a href="/login" class="back-link">← Back to Login</a>
    </div>
    
    <script src="/js/ripemd160.js"></script>
    <script src="/js/bitcoin-address.js"></script>
    <script>
        let keysGenerated = false;
        let currentKeys = null;
        
        async function generateKeys() {
            // Generate entropy from multiple sources
            const entropy = [
                Date.now(),
                Math.random() * 1000000,
                window.crypto.getRandomValues(new Uint8Array(32)),
                performance.now(),
                navigator.userAgent,
                screen.width + screen.height,
                document.getElementById('friend_code').value,
                Math.random().toString(36).substring(2, 15)
            ].join(':');
            
            // Generate private key using SHA-256
            const encoder = new TextEncoder();
            const data = encoder.encode(entropy);
            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const privateKey = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            
            // Generate public key from private key
            const publicKey = await window.BitcoinAddress.getPublicKey(privateKey);
            
            // Generate proper Bitcoin address
            const address = await window.BitcoinAddress.generate(publicKey);
            
            // Store keys
            currentKeys = {
                privateKey: privateKey,
                publicKey: publicKey,
                address: address
            };
            
            // Display keys
            document.getElementById('address-display').textContent = address;
            document.getElementById('public-key-display').textContent = publicKey;
            document.getElementById('private-key-display').textContent = privateKey;
            
            // Set hidden inputs
            document.getElementById('address').value = address;
            document.getElementById('public_key').value = publicKey;
            document.getElementById('private_key').value = privateKey;
            
            // Show keys display
            document.getElementById('keys-display').classList.remove('hidden');
            
            // Enable submit button
            keysGenerated = true;
            checkFormReady();
            
            // Disable generate button
            document.querySelector('.generate-btn').disabled = true;
            document.querySelector('.generate-btn').textContent = '✅ Keys Generated';
        }
        
        function downloadKeys() {
            if (!currentKeys) return;
            
            const username = document.getElementById('username').value || 'user';
            const content = `# HAICHAN BACKUP KEYS
# Generated: ${new Date().toISOString()}
# Username: ${username}
# SAVE THIS FILE SECURELY!

PRIVATE_KEY=${currentKeys.privateKey}
PUBLIC_KEY=${currentKeys.publicKey}
ADDRESS=${currentKeys.address}

# How to use:
# - Regular login: Use username + password
# - Backup login: Use private key only (no password needed)
# - Keep this file safe - anyone with your private key can access your account`;
            
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `haichan_keys_${username}_${Date.now()}.txt`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }
        
        function checkFormReady() {
            const friendCode = document.getElementById('friend_code').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            const ready = keysGenerated && 
                         friendCode.length > 0 && 
                         username.length >= 3 && 
                         password.length >= 8;
            
            document.getElementById('submit-btn').disabled = !ready;
        }
        
        // Add event listeners
        document.getElementById('friend_code').addEventListener('input', checkFormReady);
        document.getElementById('username').addEventListener('input', checkFormReady);
        document.getElementById('password').addEventListener('input', checkFormReady);
        
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