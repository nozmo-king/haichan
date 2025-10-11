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
        <form method="POST" action="/auth/register-advanced" id="advanced-register-form" style="margin-bottom: 30px;">
            @csrf

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🎟️ Friend Code
                </label>
                <input type="text" name="invite_code" id="invite_code" required
                       placeholder="Enter your friend code..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🔒 Password
                </label>
                <input type="password" name="password" id="password" required minlength="8"
                       placeholder="Create a secure password..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
            </div>

            <!-- Auto-generated fields (hidden but will show info) -->
            <input type="hidden" name="private_key" id="private_key">
            <input type="hidden" name="public_key" id="public_key">
            <input type="hidden" name="address" id="address">

            <!-- Generated Keys Display -->
            <div id="generated-keys" style="display: none; background: #E8F5E8; border: 2px solid #4CAF50; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
                <div style="color: #2E7D32; font-weight: bold; margin-bottom: 10px;">
                    🔑 Generated Cryptographic Keys
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Private Key:</strong>
                    <div id="display-private-key" style="font-family: 'Courier New', monospace; font-size: 11px; word-break: break-all; background: rgba(46, 125, 50, 0.1); padding: 5px; border-radius: 4px; margin-top: 2px;"></div>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Bitcoin Address:</strong>
                    <div id="display-address" style="font-family: 'Courier New', monospace; font-size: 11px; word-break: break-all; background: rgba(46, 125, 50, 0.1); padding: 5px; border-radius: 4px; margin-top: 2px;"></div>
                </div>
                <div style="text-align: center; margin: 15px 0;">
                    <button type="button" id="download-keys" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; margin-right: 10px;">
                        💾 Download haichan.key
                    </button>
                    <button type="button" id="download-pgp" style="background: #2196F3; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer;">
                        🔐 Download haichan.pgp
                    </button>
                </div>
                <div style="color: #2E7D32; font-size: 12px; margin-top: 10px;">
                    ⚠️ <strong>SAVE YOUR KEY FILES!</strong> These are your backup login methods.
                </div>
            </div>

            <div style="background: #FFF3CD; border: 2px solid #FFC107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="color: #856404; font-size: 13px;">
                    🔒 <strong>Security Notice</strong><br>
                    • Your password will be salted and hashed with your cryptographic keys<br>
                    • Keep your private key safe - it's your backup login method<br>
                    • We cannot recover lost credentials
                </div>
            </div>

            <button type="submit" style="width: 100%; background: linear-gradient(135deg, var(--border-color), var(--accent-color)); color: var(--text-primary); border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                🚀 REGISTER FOR HAICHAN
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
// Auto-generate keys when user enters friend code and password
document.addEventListener('DOMContentLoaded', function() {
    const inviteCodeInput = document.getElementById('invite_code');
    const passwordInput = document.getElementById('password');
    const privateKeyInput = document.getElementById('private_key');
    const publicKeyInput = document.getElementById('public_key');
    const addressInput = document.getElementById('address');
    const generatedKeysDiv = document.getElementById('generated-keys');
    const displayPrivateKey = document.getElementById('display-private-key');
    const displayAddress = document.getElementById('display-address');

    async function generateKeys() {
        const inviteCode = inviteCodeInput.value.trim();
        const password = passwordInput.value.trim();
        
        if (inviteCode.length >= 8 && password.length >= 8) {
            try {
                // Generate deterministic private key from friend code + password
                const seed = inviteCode + password + 'haichan_salt_2024';
                const privateKey = await sha256(seed);
                
                // Generate public key from private key
                const publicKey = await sha256(privateKey);
                
                // Generate Bitcoin address
                const address = await generateAddress(publicKey);
                
                // Set hidden form fields
                privateKeyInput.value = privateKey;
                publicKeyInput.value = publicKey;
                addressInput.value = address;
                
                // Show generated keys
                displayPrivateKey.textContent = privateKey;
                displayAddress.textContent = address;
                generatedKeysDiv.style.display = 'block';
                
                // Generate downloadable key file
                generateKeyFile(privateKey, publicKey, address, inviteCode);
                
            } catch (error) {
                console.error('Key generation failed:', error);
                generatedKeysDiv.style.display = 'none';
            }
        } else {
            // Hide keys if inputs are incomplete
            generatedKeysDiv.style.display = 'none';
            privateKeyInput.value = '';
            publicKeyInput.value = '';
            addressInput.value = '';
        }
    }

    // Generate keys when user types
    inviteCodeInput.addEventListener('input', generateKeys);
    passwordInput.addEventListener('input', generateKeys);
    
    // Setup download buttons
    document.getElementById('download-keys').addEventListener('click', function() {
        const privateKey = privateKeyInput.value;
        const publicKey = publicKeyInput.value;
        const address = addressInput.value;
        const inviteCode = inviteCodeInput.value;
        if (privateKey) {
            downloadKeyFile(privateKey, publicKey, address, inviteCode);
        }
    });
    
    document.getElementById('download-pgp').addEventListener('click', function() {
        const privateKey = privateKeyInput.value;
        const publicKey = publicKeyInput.value;
        const address = addressInput.value;
        const inviteCode = inviteCodeInput.value;
        if (privateKey) {
            downloadPGPFile(privateKey, publicKey, address, inviteCode);
        }
    });
});

// Generate and auto-trigger download of key files
function generateKeyFile(privateKey, publicKey, address, inviteCode) {
    // Files will be available via download buttons - no auto-download to avoid popup blockers
    console.log('Key files ready for download');
}

// Download simple key file
function downloadKeyFile(privateKey, publicKey, address, inviteCode) {
    const timestamp = new Date().toISOString().split('T')[0];
    const keyFileContent = `# HAICHAN CRYPTOGRAPHIC KEYS
# Generated: ${new Date().toISOString()}
# Friend Code: ${inviteCode}
# 
# KEEP THIS FILE SECURE - IT'S YOUR BACKUP LOGIN METHOD
#

[HAICHAN_KEYS]
PRIVATE_KEY=${privateKey}
PUBLIC_KEY=${publicKey}
BITCOIN_ADDRESS=${address}
FRIEND_CODE=${inviteCode}
GENERATED_DATE=${timestamp}

[BACKUP_LOGIN_INFO]
# To login with this file, use the private key in the backup login form
# Or use the Bitcoin address + your password in the regular login form
# 
# Website: https://haichan.org
# Backup Login: Use PRIVATE_KEY field above
# Regular Login: Use BITCOIN_ADDRESS + your password
`;

    const blob = new Blob([keyFileContent], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'haichan.key';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Download PGP-style key file
function downloadPGPFile(privateKey, publicKey, address, inviteCode) {
    const timestamp = new Date().toISOString();
    const pgpContent = `-----BEGIN HAICHAN PRIVATE KEY-----
Version: Haichan Cryptographic System v1.0
Comment: Generated for friend code ${inviteCode}
Comment: Date ${timestamp}

${btoa(privateKey).match(/.{1,64}/g).join('\n')}
-----END HAICHAN PRIVATE KEY-----

-----BEGIN HAICHAN PUBLIC KEY-----
Version: Haichan Cryptographic System v1.0
Comment: Bitcoin Address ${address}

${btoa(publicKey).match(/.{1,64}/g).join('\n')}
-----END HAICHAN PUBLIC KEY-----

-----BEGIN HAICHAN IDENTITY-----
FRIEND_CODE: ${inviteCode}
BITCOIN_ADDRESS: ${address}
GENERATED: ${timestamp}
BACKUP_LOGIN: Use private key above in backup login form
REGULAR_LOGIN: Use Bitcoin address + password in regular login
-----END HAICHAN IDENTITY-----
`;

    const blob = new Blob([pgpContent], { type: 'application/pgp-keys' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'haichan.pgp';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// SHA-256 implementation that matches PHP hash('sha256', $privateKey)
async function sha256(message) {
    const msgBuffer = new TextEncoder().encode(message);
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

// Generate Bitcoin address using proper Bitcoin algorithm (matching backend)
async function generateAddress(publicKey) {
    try {
        // Always use backend API for consistent address generation
        const response = await fetch('/auth/generate-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                public_key: publicKey
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            return data.address;
        } else {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to generate address');
        }
    } catch (error) {
        console.error('Address generation error:', error);
        throw error; // Re-throw to handle in calling code
    }
}
</script>

</body>
</html>