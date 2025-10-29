<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
</head>
<body>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--secondary-bg), var(--primary-bg));">
    <div style="background: var(--primary-bg); padding: 40px; border-radius: 12px; border: 3px solid var(--border-color); max-width: 600px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">

        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--text-primary); margin: 0 0 10px 0;">
                📝 HAICHAN REGISTRATION
            </h1>
            <p style="color: var(--text-secondary); font-size: 14px;">
                Join the exclusive 256-user Bitcoin forum
            </p>
        </div>

        <!-- User Slots Status -->
        <div style="background: var(--content-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <div style="color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">
                🎯 REMAINING SLOTS: {{ $remainingSlots }}/256
            </div>
            <div style="color: var(--text-secondary); font-size: 12px;">
                Registration {{ $remainingSlots > 0 ? 'OPEN' : 'CLOSED' }}
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
        <div style="background: #FFE6E6; border: 2px solid #FF6B6B; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            @foreach($errors->all() as $error)
                <div style="color: #D63031; font-size: 13px; margin: 5px 0;">• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        @if(session('success'))
        <div style="background: #E8F5E8; border: 2px solid #4CAF50; padding: 15px; margin-bottom: 20px; border-radius: 8px; color: #2E7D32; font-size: 13px;">
            {{ session('success') }}
        </div>
        @endif

        <!-- Registration Form -->
        <form method="POST" action="/register" id="register-form" style="margin-bottom: 30px;">
            @csrf

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🎟️ Friend Code
                </label>
                <input type="text" name="friend_code" id="friend_code" required
                       value="{{ $friendCode ?? '' }}" readonly
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--secondary-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    👤 Username
                </label>
                <input type="text" name="username" id="username" required minlength="3" maxlength="20"
                       placeholder="Choose your username..."
                       pattern="[a-zA-Z0-9_]+"
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">
                    3-20 characters, alphanumeric and underscore only
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🔒 Password
                </label>
                <input type="password" name="password" id="password" required minlength="8"
                       placeholder="Create a secure password..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">
                    Minimum 8 characters
                </div>
            </div>

            <!-- Bitcoin Key Fields (hidden, will be generated) -->
            <input type="hidden" name="private_key" id="private_key" required>
            <input type="hidden" name="public_key" id="public_key" required>
            <input type="hidden" name="address" id="address" required>

            <!-- Key Generation Section (now automatic) -->

            <!-- Generated Keys Display -->
            <div id="generated-keys" style="display: none; background: #E8F5E8; border: 2px solid #4CAF50; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
                <div style="color: #2E7D32; font-weight: bold; margin-bottom: 10px;">
                    ✅ Cryptographic Keys Generated
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Private Key:</strong>
                    <div id="display-private-key" style="font-family: 'Courier New', monospace; font-size: 11px; word-break: break-all; background: rgba(46, 125, 50, 0.1); padding: 5px; border-radius: 4px; margin-top: 2px;"></div>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Public Key:</strong>
                    <div id="display-public-key" style="font-family: 'Courier New', monospace; font-size: 11px; word-break: break-all; background: rgba(46, 125, 50, 0.1); padding: 5px; border-radius: 4px; margin-top: 2px;"></div>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Bitcoin Address:</strong>
                    <div id="display-address" style="font-family: 'Courier New', monospace; font-size: 11px; word-break: break-all; background: rgba(46, 125, 50, 0.1); padding: 5px; border-radius: 4px; margin-top: 2px;"></div>
                </div>
                <div style="text-align: center; margin: 15px 0;">
                    <button type="button" id="download-keys" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer;">
                        💾 Download Backup Keys
                    </button>
                </div>
                <div style="color: #2E7D32; font-size: 12px; margin-top: 10px;">
                    ⚠️ <strong>SAVE YOUR PRIVATE KEY!</strong> This is your only way to recover your account.
                </div>
            </div>

            <div style="background: #FFF3CD; border: 2px solid #FFC107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="color: #856404; font-size: 13px;">
                    🔒 <strong>Security Notice</strong><br>
                    • Generate your Bitcoin keys using the button above<br>
                    • Your private key will NEVER be stored on our servers<br>
                    • Save your private key - it's your only recovery method<br>
                    • We cannot recover lost keys
                </div>
            </div>

            <button type="submit" id="submit-btn" disabled style="width: 100%; background: #999; color: white; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: not-allowed; transition: all 0.3s ease;">
                🔒 Generate Keys First
            </button>
        </form>

        <!-- Simple Registration Link -->
        <div style="text-align: center; margin-bottom: 15px;">
            <a href="/auth/register" style="color: var(--text-secondary); text-decoration: none; font-size: 12px;">
                Want easier registration? Try simple registration →
            </a>
        </div>

        <!-- Back to Login -->
        <div style="text-align: center;">
            <a href="/auth/login" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
                ← Back to Login
            </a>
        </div>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const privateKeyInput = document.getElementById('private_key');
    const publicKeyInput = document.getElementById('public_key');
    const addressInput = document.getElementById('address');
    const generatedKeysDiv = document.getElementById('generated-keys');
    const displayPrivateKey = document.getElementById('display-private-key');
    const displayPublicKey = document.getElementById('display-public-key');
    const displayAddress = document.getElementById('display-address');
    const submitBtn = document.getElementById('submit-btn');
    let keysGenerated = false;

    async function generateKeys() {
        if (keysGenerated) return;

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        if (username.length < 3 || password.length < 8) {
            return;
        }

        keysGenerated = true;

        try {
            // Generate random private key using Web Crypto API
            const randomBytes = new Uint8Array(32);
            window.crypto.getRandomValues(randomBytes);
            const privateKey = Array.from(randomBytes).map(b => b.toString(16).padStart(2, '0')).join('');
            
            // Generate public key (simplified - just hash the private key)
            const publicKey = await sha256(privateKey);
            
            // Generate Bitcoin-like address
            const address = await generateAddress(publicKey);
            
            // Set hidden form fields
            privateKeyInput.value = privateKey;
            publicKeyInput.value = publicKey;
            addressInput.value = address;
            
            // Show generated keys
            displayPrivateKey.textContent = privateKey;
            displayPublicKey.textContent = publicKey;
            displayAddress.textContent = address;
            generatedKeysDiv.style.display = 'block';
            
            // Enable submit button
            submitBtn.disabled = false;
            submitBtn.style.background = 'linear-gradient(135deg, #4CAF50, #45a049)';
            submitBtn.style.cursor = 'pointer';
            submitBtn.textContent = '🚀 REGISTER FOR HAICHAN';
            
        } catch (error) {
            console.error('Key generation failed:', error);
            alert('Failed to generate keys. Please try again.');
            keysGenerated = false;
        }
    }

    usernameInput.addEventListener('input', generateKeys);
    passwordInput.addEventListener('input', generateKeys);

    // Download keys functionality
    document.getElementById('download-keys').addEventListener('click', function() {
        const friendCode = document.getElementById('friend_code').value;
        const username = usernameInput.value;
        const privateKey = privateKeyInput.value;
        const publicKey = publicKeyInput.value;
        const address = addressInput.value;
        
        const backupContent = `# HAICHAN BACKUP KEYS
# Generated: ${new Date().toISOString()}
# Username: ${username}
# Friend Code Used: ${friendCode}

PRIVATE_KEY=${privateKey}
PUBLIC_KEY=${publicKey}
ADDRESS=${address}
USERNAME=${username}

# IMPORTANT: Save this file securely!
# Use your private key for backup login
# Never share your private key with anyone`;

        const blob = new Blob([backupContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `haichan_${username}_backup.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    // Helper functions
    async function sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async function generateAddress(publicKey) {
        // Simple Bitcoin-like address generation
        const hash = await sha256(publicKey);
        return '1' + hash.substring(0, 33);
    }
});
</script>

</body>
</html>

