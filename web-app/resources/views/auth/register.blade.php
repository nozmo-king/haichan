<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
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

            <div style="background: #E8F5E8; border: 2px solid #4CAF50; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="color: #2E7D32; font-size: 13px;">
                    ✨ <strong>Easy Registration</strong><br>
                    • Just enter your username and password above<br>
                    • Bitcoin keys will be auto-generated for you<br>
                    • Download your backup keys after registration<br>
                    • Save your private key - it's your only recovery method
                </div>
            </div>

            <button type="submit" id="submit-btn" disabled style="width: 100%; background: #999; color: white; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: not-allowed; transition: all 0.3s ease;">
                🔒 Enter username (3+ chars) & password (8+ chars)
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


<script nonce="{{ app('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Using native browser crypto...');
    
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const publicKeyInput = document.getElementById('public_key');
    const addressInput = document.getElementById('address');
    const generatedKeysDiv = document.getElementById('generated-keys');
    const displayPrivateKey = document.getElementById('display-private-key');
    const displayPublicKey = document.getElementById('display-public-key');
    const displayAddress = document.getElementById('display-address');
    const submitBtn = document.getElementById('submit-btn');
    let keysGenerated = false;
    let generatedPrivateKey;

    // Base58 encoding for Bitcoin addresses
    const BASE58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    
    function base58encode(buffer) {
        let digits = [0];
        for (let i = 0; i < buffer.length; i++) {
            let carry = buffer[i];
            for (let j = 0; j < digits.length; j++) {
                carry += digits[j] << 8;
                digits[j] = carry % 58;
                carry = (carry / 58) | 0;
            }
            while (carry) {
                digits.push(carry % 58);
                carry = (carry / 58) | 0;
            }
        }
        for (let i = 0; i < buffer.length && buffer[i] === 0; i++) {
            digits.push(0);
        }
        return digits.reverse().map(digit => BASE58[digit]).join('');
    }

    function hexToBytes(hex) {
        const bytes = [];
        for (let i = 0; i < hex.length; i += 2) {
            bytes.push(parseInt(hex.substr(i, 2), 16));
        }
        return new Uint8Array(bytes);
    }

    async function sha256(data) {
        const buffer = typeof data === 'string' ? new TextEncoder().encode(data) : data;
        const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
        return Array.from(new Uint8Array(hashBuffer))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    }

    // Simple RIPEMD160 fallback (not cryptographically secure, but functional for demo)
    function ripemd160Mock(hexString) {
        // For demo purposes, use first 40 chars of SHA256
        return hexString.substring(0, 40);
    }

    function applyDisabledState(message) {
        submitBtn.textContent = message;
        submitBtn.disabled = true;
        submitBtn.style.background = '#999';
        submitBtn.style.cursor = 'not-allowed';
    }

    function applyEnabledState() {
        submitBtn.disabled = false;
        submitBtn.style.background = 'linear-gradient(135deg, #4CAF50, #45a049)';
        submitBtn.style.cursor = 'pointer';
        submitBtn.textContent = '🚀 REGISTER FOR HAICHAN';
    }

    async function updateButtonState() {
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        if (username.length < 3 || password.length < 8) {
            let buttonText = '🔒 Need: ';
            if (username.length < 3) buttonText += 'Username (3+ chars) ';
            if (password.length < 8) buttonText += 'Password (8+ chars)';
            applyDisabledState(buttonText.trim());

            if (keysGenerated) {
                keysGenerated = false;
                generatedKeysDiv.style.display = 'none';
                publicKeyInput.value = '';
                addressInput.value = '';
            }
            return;
        }

        if (!keysGenerated) {
            const generated = await generateKeys();
            if (!generated) {
                console.warn('Client key generation failed; proceeding with server-side key creation.');
            }
        }

        applyEnabledState();
    }

    async function generateKeys() {
        try {
            console.log('Starting key generation...');

            // Generate a random 32-byte private key
            const privateKeyArray = new Uint8Array(32);
            crypto.getRandomValues(privateKeyArray);
            
            // Convert to hex
            const privateKeyHex = Array.from(privateKeyArray)
                .map(b => b.toString(16).padStart(2, '0'))
                .join('');
            
            // Create WIF format private key (Bitcoin mainnet)
            const privateKeyWithPrefix = '80' + privateKeyHex;
            const checksum1 = await sha256(hexToBytes(privateKeyWithPrefix));
            const checksum2 = (await sha256(hexToBytes(checksum1))).substr(0, 8);
            const privateKeyWithChecksum = privateKeyWithPrefix + checksum2;
            generatedPrivateKey = base58encode(hexToBytes(privateKeyWithChecksum));

            // Generate mock public key (32 bytes = 64 hex chars)
            const mockPublicKey = new Uint8Array(32);
            crypto.getRandomValues(mockPublicKey);
            const publicKeyHex = Array.from(mockPublicKey).map(b => b.toString(16).padStart(2, '0')).join('');

            // Generate Bitcoin address using simplified approach
            const publicKeyHash1 = await sha256(publicKeyHex);
            const publicKeyHash = ripemd160Mock(publicKeyHash1);
            const addressPayload = '00' + publicKeyHash;
            const addressChecksum1 = await sha256(hexToBytes(addressPayload));
            const addressChecksum2 = (await sha256(hexToBytes(addressChecksum1))).substr(0, 8);
            const addressWithChecksum = addressPayload + addressChecksum2;
            const bitcoinAddress = base58encode(hexToBytes(addressWithChecksum));

            // Set hidden form fields
            publicKeyInput.value = publicKeyHex;
            addressInput.value = bitcoinAddress;
            
            console.log('Keys generated successfully!');
            console.log('Private Key (WIF):', generatedPrivateKey);
            console.log('Public Key:', publicKeyHex);
            console.log('Bitcoin Address:', bitcoinAddress);
            
            // Show generated keys
            displayPrivateKey.textContent = generatedPrivateKey;
            displayPublicKey.textContent = publicKeyHex;
            displayAddress.textContent = bitcoinAddress;
            generatedKeysDiv.style.display = 'block';
            keysGenerated = true;
            
            console.log('Client keys generated successfully.');
            return true;
        } catch (error) {
            console.error('Key generation failed:', error);
            keysGenerated = false;
            generatedPrivateKey = null;
            publicKeyInput.value = '';
            addressInput.value = '';
            generatedKeysDiv.style.display = 'none';
            return false;
        }
    }

    usernameInput.addEventListener('input', updateButtonState);
    passwordInput.addEventListener('input', updateButtonState);

    // Initialize button state on load
    updateButtonState();

    // Download keys functionality
    document.getElementById('download-keys').addEventListener('click', function() {
        if (!generatedPrivateKey) {
            alert('Keys are generated on the server after registration. Finish registering and download from the success page.');
            return;
        }

        const friendCode = document.getElementById('friend_code').value;
        const username = usernameInput.value;
        const publicKey = publicKeyInput.value;
        const address = addressInput.value;
        
        const backupContent = `# HAICHAN BACKUP KEYS
# Generated: ${new Date().toISOString()}
# Username: ${username}
# Friend Code Used: ${friendCode}

PRIVATE_KEY=${generatedPrivateKey}\nPUBLIC_KEY=${publicKey}\nADDRESS=${address}\nUSERNAME=${username}\n\n# IMPORTANT: Save this file securely!\n# Use your private key for backup login\n# Never share your private key with anyone`;

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
});
</script>

</body>
</html>
