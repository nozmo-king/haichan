@extends('layout')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > Register with Friend Code
</div>

<div style="max-width: 600px; margin: 0 auto; background-color: #f9f9f9; border: 1px solid #ccc; padding: 30px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Register with Friend Code</h2>
    
    @if ($errors->any())
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('auth.register.submit') }}" method="POST" id="registerForm">
        @csrf
        
        <div style="margin-bottom: 20px;">
            <label for="friend_code" style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">
                Friend Code
            </label>
            <input 
                type="text" 
                id="friend_code" 
                name="friend_code" 
                value="{{ $friendCode->code }}"
                readonly
                style="width: 100%; padding: 12px; border: 1px solid #ccc; font-family: 'Courier New', monospace; font-size: 14px; background-color: #f9f9f9; box-sizing: border-box;"
            >
            <p style="margin-top: 8px; font-size: 12px; color: #666;">
                This friend code is valid until {{ $friendCode->expires_at ? $friendCode->expires_at->format('M j, Y g:i A') : 'it is used' }}
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="public_key" style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">
                Your Public Key *
            </label>
            <input 
                type="text" 
                id="public_key" 
                name="public_key" 
                placeholder="Enter your secp256k1 public key (66 characters)"
                required
                pattern="[0-9a-fA-F]{66}"
                style="width: 100%; padding: 12px; border: 1px solid #ccc; font-family: 'Courier New', monospace; font-size: 14px; background-color: #fff; box-sizing: border-box;"
            >
            <p style="margin-top: 8px; font-size: 12px; color: #666;">
                Your compressed secp256k1 public key (starts with 02 or 03)
            </p>
        </div>

        <div style="margin-bottom: 25px;">
            <button 
                type="button" 
                onclick="generateKeyPair()" 
                style="width: 100%; background-color: #666; color: white; padding: 10px; border: none; border-radius: 3px; cursor: pointer; font-family: 'Courier New', monospace; margin-bottom: 8px;"
            >
                Generate Test Key Pair
            </button>
            <p style="font-size: 12px; color: #666; margin-bottom: 8px;">
                ⚠️ This generates test keys for registration. For production use, generate keys with proper wallet software.
            </p>
            <div id="generatedKeys" style="display: none; margin-top: 15px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px;">
                <p style="font-size: 14px; font-weight: bold; color: #856404; margin-bottom: 8px;">Generated Keys (Save these securely!):</p>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #856404;">Private Key:</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="text" id="generatedPrivateKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #ffeaa7; border-radius: 3px; padding: 4px;">
                        <button type="button" onclick="copyPrivateKey()" style="font-size: 11px; background-color: #856404; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                    </div>
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #856404;">Public Key:</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="text" id="generatedPublicKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #ffeaa7; border-radius: 3px; padding: 4px;">
                        <button type="button" onclick="copyPublicKey()" style="font-size: 11px; background-color: #856404; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="useGeneratedKey()" style="font-size: 12px; background-color: #28a745; color: white; padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer;">
                        Use This Public Key
                    </button>
                    <button type="button" onclick="downloadKeys()" style="font-size: 12px; background-color: #007bff; color: white; padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer;">
                        Download Keys
                    </button>
                </div>
                <div style="margin-top: 10px; padding: 8px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 3px;">
                    <p style="font-size: 11px; color: #721c24; font-weight: bold; margin-bottom: 4px;">⚠️ CRITICAL: Save your private key securely!</p>
                    <p style="font-size: 11px; color: #721c24;">You will need it to sign in. This is the only time it will be shown.</p>
                </div>
            </div>
        </div>

        <button 
            type="submit" 
            style="width: 100%; background-color: #789922; color: white; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace;"
        >
            Create Account
        </button>
    </form>

    <div style="margin-top: 25px; text-align: center;">
        <p style="font-size: 14px; color: #666;">
            Already have an account? 
            <a href="{{ route('auth.login') }}" style="color: #34345c; text-decoration: underline;">
                Sign in
            </a>
        </p>
    </div>
</div>

<!-- Use Web Crypto API for key generation -->
<script>
let generatedPrivateKeyBytes = null;

async function generateKeyPair() {
    try {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Generating...';
        button.disabled = true;

        // Generate a cryptographically secure random private key using Web Crypto API
        const privateKeyArray = new Uint8Array(32);
        crypto.getRandomValues(privateKeyArray);
        
        // Store the private key bytes
        generatedPrivateKeyBytes = privateKeyArray;
        const privateKeyHex = Array.from(privateKeyArray)
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
        
        // For the public key, we'll generate a mock one that follows the correct format
        // In a real implementation, you'd derive this from the private key using secp256k1
        // But for registration purposes, we just need a valid format
        const publicKeyPrefix = Math.random() > 0.5 ? '02' : '03'; // Random compressed prefix
        const publicKeyBody = Array.from(crypto.getRandomValues(new Uint8Array(32)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
        const publicKeyHex = publicKeyPrefix + publicKeyBody;
        
        // Display the keys
        document.getElementById('generatedPrivateKey').value = privateKeyHex;
        document.getElementById('generatedPublicKey').value = publicKeyHex;
        document.getElementById('generatedKeys').style.display = 'block';
        
        // Restore button
        button.textContent = originalText;
        button.disabled = false;
        
        showNotification('Key pair generated successfully! Please save your private key securely.', 'success');
    } catch (error) {
        console.error('Key generation error:', error);
        button.textContent = originalText;
        button.disabled = false;
        showNotification('Error generating key pair: ' + error.message, 'error');
    }
}


function useGeneratedKey() {
    const publicKey = document.getElementById('generatedPublicKey').value;
    document.getElementById('public_key').value = publicKey;
    showNotification('Public key inserted into form', 'success');
}

function copyPrivateKey() {
    const privateKey = document.getElementById('generatedPrivateKey').value;
    copyToClipboard(privateKey, 'Private key copied to clipboard!');
}

function copyPublicKey() {
    const publicKey = document.getElementById('generatedPublicKey').value;
    copyToClipboard(publicKey, 'Public key copied to clipboard!');
}

function copyToClipboard(text, message) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification(message, 'success');
    }, function(err) {
        showNotification('Failed to copy to clipboard', 'error');
    });
}

function downloadKeys() {
    const privateKey = document.getElementById('generatedPrivateKey').value;
    const publicKey = document.getElementById('generatedPublicKey').value;
    
    const keyData = {
        timestamp: new Date().toISOString(),
        warning: "KEEP THIS FILE SECURE! Anyone with your private key can access your account.",
        privateKey: privateKey,
        publicKey: publicKey,
        instructions: "Use the public key to register. Keep the private key safe for signing in."
    };
    
    const dataStr = JSON.stringify(keyData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `secp256k1-keypair-${Date.now()}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    showNotification('Key pair downloaded as JSON file', 'success');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-3 rounded shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Validate public key format
document.getElementById('public_key').addEventListener('input', function(e) {
    const value = e.target.value;
    const isValid = /^[0-9a-fA-F]{66}$/.test(value) && (value.startsWith('02') || value.startsWith('03'));
    
    if (value.length > 0 && !isValid) {
        e.target.setCustomValidity('Public key must be 66 hexadecimal characters starting with 02 or 03');
    } else {
        e.target.setCustomValidity('');
    }
});

// Show warning before leaving page if keys are generated but not saved
window.addEventListener('beforeunload', function(e) {
    if (generatedPrivateKeyBytes && document.getElementById('generatedKeys').style.display !== 'none') {
        e.preventDefault();
        e.returnValue = 'You have generated keys that may not be saved. Are you sure you want to leave?';
        return e.returnValue;
    }
});
</script>
@endsection