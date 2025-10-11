<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--secondary-bg), var(--primary-bg));">
    <div style="background: var(--primary-bg); padding: 40px; border-radius: 12px; border: 3px solid var(--border-color); max-width: 800px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--text-primary); margin: 0 0 10px 0;">
                HAICHAN REGISTRATION
            </h1>
            <p style="color: var(--text-secondary); font-size: 14px;">
                Generate your Bitcoin keys locally and join the network
            </p>
        </div>

        @if($errors->any())
        <div style="background: #FFE6E6; border: 2px solid #FF6B6B; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            @foreach($errors->all() as $error)
                <div style="color: #D63031; font-size: 13px; margin: 5px 0;">• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="/auth/register" id="register-form" enctype="multipart/form-data">
            @csrf
            
            <!-- Generated Bitcoin Credentials -->
            <div id="bitcoin-creds" style="background: var(--content-bg); padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none;">
                <h3 style="color: var(--text-primary); margin-bottom: 15px;">Generated Bitcoin Credentials</h3>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Private Key</label>
                    <input type="text" id="private_key" name="private_key" readonly
                           style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg); color: var(--text-primary); font-family: monospace; font-size: 12px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Public Key</label>
                    <input type="text" id="public_key" name="public_key" readonly
                           style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg); color: var(--text-primary); font-family: monospace; font-size: 12px;">
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Bitcoin Address</label>
                    <input type="text" id="address" name="address" readonly
                           style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg); color: var(--text-primary); font-family: monospace; font-size: 12px;">
                </div>
            </div>

            <!-- Generate Keys Button -->
            <div style="text-align: center; margin-bottom: 20px;">
                <button type="button" id="generate-keys-btn" 
                        style="background: linear-gradient(135deg, #FF9800, #F57C00); color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
                    Generate Bitcoin Keys Locally
                </button>
            </div>

            <!-- Friend Code Input -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Friend Code (Required)
                </label>
                <input type="text" name="friend_code" required
                       placeholder="Enter friend code from existing member..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px;">
            </div>

            <!-- Username -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Username (Ornamental/Changeable)
                </label>
                <input type="text" name="username" required
                       placeholder="Choose your display name..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px;">
            </div>

            <!-- Password -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Password
                </label>
                <input type="password" name="password" required minlength="8"
                       placeholder="Create a secure password..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px;">
            </div>

            <!-- Avatar Upload -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Avatar Image (Optional)
                </label>
                <input type="file" name="avatar" accept="image/*"
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px;">
            </div>

            <!-- SSH Key -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    SSH Public Key (Optional)
                </label>
                <textarea name="ssh_key" rows="4"
                          placeholder="ssh-rsa AAAAB3NzaC1yc2E..."
                          style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: monospace;"></textarea>
            </div>

            <button type="submit" id="submit-btn" disabled
                    style="width: 100%; background: #ccc; color: #666; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: not-allowed; transition: all 0.3s ease;">
                Complete Registration
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/auth/login" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
                ← Back to Login
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-keys-btn');
    const bitcoinCredsDiv = document.getElementById('bitcoin-creds');
    const privateKeyInput = document.getElementById('private_key');
    const publicKeyInput = document.getElementById('public_key');
    const addressInput = document.getElementById('address');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('register-form');
    
    let generatedKeys = null;
    
    // Generate keys when button is clicked
    generateBtn.addEventListener('click', async function() {
        try {
            // Generate random private key
            const privateKeyBytes = new Uint8Array(32);
            crypto.getRandomValues(privateKeyBytes);
            const privateKey = Array.from(privateKeyBytes)
                .map(b => b.toString(16).padStart(2, '0'))
                .join('');
            
            // Generate public key from private key (simplified for demo)
            const publicKey = await sha256(privateKey);
            
            // Generate Bitcoin address from public key
            const address = 'bc1' + publicKey.substring(0, 39); // Simplified bech32 format
            
            // Store generated keys
            generatedKeys = {
                private_key: privateKey,
                public_key: publicKey,
                address: address
            };
            
            // Display in form
            privateKeyInput.value = privateKey;
            publicKeyInput.value = publicKey;
            addressInput.value = address;
            
            // Show credentials div
            bitcoinCredsDiv.style.display = 'block';
            
            // Enable submit button
            submitBtn.disabled = false;
            submitBtn.style.background = 'linear-gradient(135deg, var(--border-color), var(--accent-color))';
            submitBtn.style.color = 'var(--text-primary)';
            submitBtn.style.cursor = 'pointer';
            
            // Hide generate button
            generateBtn.style.display = 'none';
            
        } catch (error) {
            console.error('Key generation failed:', error);
            alert('Failed to generate keys. Please try again.');
        }
    });
    
    // Handle form submission
    form.addEventListener('submit', async function(e) {
        if (!generatedKeys) {
            e.preventDefault();
            alert('Please generate Bitcoin keys first.');
            return;
        }
        
        // Form will submit normally, server will handle the rest
        // After successful registration, server should generate and trigger download of Haichan.keys file
    });
    
    // SHA-256 helper function
    async function sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
});
</script>

</body>
</html>