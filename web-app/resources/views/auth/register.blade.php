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
        <form method="POST" action="/auth/register" style="margin-bottom: 30px;">
            @csrf

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🎟️ Friend Code
                </label>
                <input type="text" name="invite_code" required
                       placeholder="Enter your friend code..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🔑 Private Key
                </label>
                <input type="password" name="private_key" required
                       placeholder="Enter your 64-character private key..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
            </div>

            <input type="hidden" name="public_key" id="public_key">

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    📍 Bitcoin Address
                </label>
                <input type="text" name="address" required
                       placeholder="Enter your Bitcoin address..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🔒 Password
                </label>
                <input type="password" name="password" required minlength="8"
                       placeholder="Create a secure password..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
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

        <!-- Back to Login -->
        <div style="text-align: center;">
            <a href="/auth/login" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
                ← Back to Login
            </a>
        </div>

    </div>
</div>


<script>
// Auto-generate public key from private key
document.addEventListener('DOMContentLoaded', function() {
    const privateKeyInput = document.querySelector('input[name="private_key"]');
    const publicKeyInput = document.querySelector('input[name="public_key"]');
    const addressInput = document.querySelector('input[name="address"]');

    if (privateKeyInput) {
        privateKeyInput.addEventListener('input', function() {
            const privateKey = this.value;
            if (privateKey.length === 64) {
                // Simple hash to generate public key (not real ECDSA but works for our system)
                const publicKey = sha256(privateKey);
                publicKeyInput.value = publicKey;

                // Generate Bitcoin address from public key
                generateAddress(publicKey).then(address => {
                    addressInput.value = address;
                });
            }
        });
    }
});

// Simple SHA-256 implementation
async function sha256(message) {
    const msgBuffer = new TextEncoder().encode(message);
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

// Generate Bitcoin address (simplified version)
async function generateAddress(publicKey) {
    try {
        // Step 1: SHA256 of public key
        const pubKeyBytes = new Uint8Array(publicKey.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
        const sha256Hash = await crypto.subtle.digest('SHA-256', pubKeyBytes);

        // For simplicity, we'll just return a placeholder Bitcoin address format
        // In a real implementation, this would do full RIPEMD160 + Base58 encoding
        const hashArray = Array.from(new Uint8Array(sha256Hash));
        const hash = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

        // Simple address generation (placeholder)
        return '1' + hash.substring(0, 25) + hash.substring(25, 33);
    } catch (error) {
        return '';
    }
}
</script>

</body>
</html>