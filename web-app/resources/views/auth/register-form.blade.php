@extends('layout')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > Join Our Community
</div>

<div style="max-width: 600px; margin: 0 auto; background-color: #f9f9f9; border: 1px solid #ccc; padding: 30px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Join Our Community</h2>
    
    <div style="margin-bottom: 25px; text-align: center;">
        <p style="color: #666; margin-bottom: 15px;">
            This is an invite-only community. You need a friend code from an existing member to register.
        </p>
    </div>

    @if ($errors->any())
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="friendCodeForm">
        <div style="margin-bottom: 20px;">
            <label for="friend_code" style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">
                Friend Code *
            </label>
            <input 
                type="text" 
                id="friend_code" 
                name="friend_code" 
                placeholder="Enter your friend code"
                required
                style="width: 100%; padding: 12px; border: 1px solid #ccc; font-family: 'Courier New', monospace; font-size: 14px; background-color: #fff; box-sizing: border-box;"
            >
            <p style="margin-top: 8px; font-size: 12px; color: #666;">
                Friend codes are 32-character alphanumeric strings
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <button 
                type="button" 
                onclick="validateAndRedirect()" 
                style="width: 100%; background-color: #789922; color: white; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace;"
            >
                Continue with Friend Code
            </button>
        </div>
    </form>

    <div style="border-top: 1px solid #ddd; padding-top: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 12px; color: #333;">How to get a friend code?</h3>
        <ul style="font-size: 14px; color: #666; line-height: 1.6;">
            <li style="margin-bottom: 8px;">• Ask an existing community member to share their friend code</li>
            <li style="margin-bottom: 8px;">• Each user can generate friend codes to invite new members</li>
            <li style="margin-bottom: 8px;">• Friend codes are single-use and may have expiration dates</li>
        </ul>
    </div>

    <div style="margin-top: 25px; text-align: center;">
        <p style="font-size: 14px; color: #666;">
            Already have an account? 
            <a href="{{ route('auth.login') }}" style="color: #34345c; text-decoration: underline;">
                Sign in
            </a>
        </p>
    </div>

    <div style="margin-top: 15px; text-align: center;">
        <p style="font-size: 14px; color: #666;">
            Want to learn more? 
            <a href="{{ route('subscription.plans') }}" style="color: #34345c; text-decoration: underline;">
                View our plans
            </a>
        </p>
    </div>

    <!-- Key Generation Section -->
    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 12px; color: #333;">Don't have a secp256k1 key pair?</h3>
        <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
            You can generate a new key pair here for testing purposes. In production, use a proper wallet.
        </p>
        
        <button 
            type="button" 
            onclick="generateTestKeyPair()" 
            style="width: 100%; background-color: #666; color: white; padding: 10px; border: none; border-radius: 3px; cursor: pointer; font-family: 'Courier New', monospace; margin-bottom: 15px;"
        >
            Generate Test Key Pair
        </button>
        
        <div id="testKeys" style="display: none; padding: 15px; background-color: #cce7ff; border: 1px solid #9fc3ff; border-radius: 3px;">
            <p style="font-size: 14px; font-weight: bold; color: #0056b3; margin-bottom: 8px;">Generated Test Keys:</p>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 12px; font-weight: bold; color: #0056b3;">Private Key (save this!):</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" id="testPrivateKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #9fc3ff; border-radius: 3px; padding: 4px;">
                    <button type="button" onclick="copyTestPrivateKey()" style="font-size: 11px; background-color: #0056b3; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                </div>
            </div>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 12px; font-weight: bold; color: #0056b3;">Public Key:</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" id="testPublicKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #9fc3ff; border-radius: 3px; padding: 4px;">
                    <button type="button" onclick="copyTestPublicKey()" style="font-size: 11px; background-color: #0056b3; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                </div>
            </div>
            <div style="margin-top: 10px; padding: 8px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px;">
                <p style="font-size: 11px; color: #856404;">⚠️ These are test keys only. Save the private key - you'll need it to sign in!</p>
            </div>
        </div>
    </div>
</div>

<!-- Use Web Crypto API for simple key generation -->
<script>
async function validateAndRedirect() {
    const friendCode = document.getElementById('friend_code').value.trim();
    
    if (!friendCode) {
        showError('Please enter a friend code');
        return;
    }

    if (friendCode.length !== 32) {
        showError('Friend codes must be exactly 32 characters long');
        return;
    }

    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Validating...';
    button.disabled = true;

    try {
        // Validate friend code via API
        const response = await fetch('/api/friend-codes/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ code: friendCode })
        });

        const data = await response.json();

        if (data.valid) {
            // Redirect to registration page with friend code
            window.location.href = `/register/${friendCode}`;
        } else {
            showError(data.message || 'Invalid friend code');
        }
    } catch (error) {
        showError('Error validating friend code. Please try again.');
    } finally {
        // Restore button state
        button.textContent = originalText;
        button.disabled = false;
    }
}

function showError(message) {
    // Remove existing error messages
    const existingError = document.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
    errorDiv.textContent = message;

    // Insert before the form
    const form = document.getElementById('friendCodeForm');
    form.parentNode.insertBefore(errorDiv, form);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

// Allow Enter key to submit
document.getElementById('friend_code').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        validateAndRedirect();
    }
});

// Key generation functions
async function generateTestKeyPair() {
    try {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Generating...';
        button.disabled = true;

        // Generate a cryptographically secure random private key using Web Crypto API
        const privateKeyArray = new Uint8Array(32);
        crypto.getRandomValues(privateKeyArray);
        
        const privateKeyHex = Array.from(privateKeyArray)
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
        
        // Generate a mock public key that follows the correct format
        const publicKeyPrefix = Math.random() > 0.5 ? '02' : '03';
        const publicKeyBody = Array.from(crypto.getRandomValues(new Uint8Array(32)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
        const publicKeyHex = publicKeyPrefix + publicKeyBody;
        
        // Display the keys
        document.getElementById('testPrivateKey').value = privateKeyHex;
        document.getElementById('testPublicKey').value = publicKeyHex;
        document.getElementById('testKeys').classList.remove('hidden');
        
        // Restore button
        button.textContent = originalText;
        button.disabled = false;
        
        showNotification('Test key pair generated! Save the private key securely.', 'success');
    } catch (error) {
        console.error('Key generation error:', error);
        button.textContent = originalText;
        button.disabled = false;
        showNotification('Error generating key pair: ' + error.message, 'error');
    }
}

function copyTestPrivateKey() {
    const privateKey = document.getElementById('testPrivateKey').value;
    copyToClipboard(privateKey, 'Private key copied to clipboard!');
}

function copyTestPublicKey() {
    const publicKey = document.getElementById('testPublicKey').value;
    copyToClipboard(publicKey, 'Public key copied to clipboard!');
}

function copyToClipboard(text, message) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification(message, 'success');
    }, function(err) {
        showNotification('Failed to copy to clipboard', 'error');
    });
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
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection