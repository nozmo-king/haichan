@extends('layout')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('boards.index') }}">Boards</a> > Join Our Community
</div>

<div style="max-width: 600px; margin: 0 auto; background-color: #FFFFEE; border: 1px solid #ccc; padding: 30px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Join Our Community</h2>
    
    <div style="margin-bottom: 25px; text-align: center;">
        <p style="color: #2E7D32; margin-bottom: 15px; font-weight: bold; font-size: 16px;">
            ✅ Ready to register! Default friend code is pre-filled.
        </p>
        <p style="color: #666; font-size: 13px;">
            (This is an invite-only community. A friend code is required to register.)
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
                value="GENESIS2025"
                required
                style="width: 100%; padding: 12px; border: 1px solid #ccc; font-family: 'Courier New', monospace; font-size: 14px; background-color: #fff; box-sizing: border-box;"
            >
            <p id="friend-code-hint" style="margin-top: 8px; font-size: 12px; color: #2E7D32; font-weight: bold;">
                ✅ Default code "GENESIS2025" pre-filled for easy registration
            </p>
        </div>

        <div style="margin-bottom: 20px;">
            <button 
                type="button" 
                id="validate-friend-code-btn"
                style="width: 100%; background-color: #4CAF50; color: white; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; font-size: 16px; text-transform: uppercase; letter-spacing: 1px;"
            >
                🚀 Continue to Registration
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
            <a href="{{ route('login') }}" style="color: #34345c; text-decoration: underline;">
                Sign in
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
            id="generate-keys-btn"
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
                    <button type="button" id="copy-private-key-btn" style="font-size: 11px; background-color: #0056b3; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                </div>
            </div>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 12px; font-weight: bold; color: #0056b3;">Public Key:</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" id="testPublicKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #9fc3ff; border-radius: 3px; padding: 4px;">
                    <button type="button" id="copy-public-key-btn" style="font-size: 11px; background-color: #0056b3; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                </div>
            </div>
            <div style="margin-top: 10px; padding: 8px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px;">
                <p style="font-size: 11px; color: #856404;">⚠️ These are test keys only. Save the private key - you'll need it to sign in!</p>
            </div>
        </div>
    </div>
</div>

<!-- Load key generation module -->
<script src="/js/secp256k1-keygen.js" nonce="{{ app('csp_nonce') }}"></script>

<!-- Use Web Crypto API for simple key generation -->
<script nonce="{{ app('csp_nonce') }}">
async function validateAndRedirect(event) {
    if (event) {
        event.preventDefault();
    }

    const friendCode = document.getElementById('friend_code').value.trim();

    if (!friendCode) {
        showError('Please enter a friend code');
        return;
    }

    const button = document.getElementById('validate-friend-code-btn');
    if (!button) {
        showError('Validation button not found');
        return;
    }

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
        console.error('Friend code validation failed:', error);
        showError('Error validating friend code. Please try again.');
    } finally {
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
        validateAndRedirect(e);
    }
});

// Key generation functions
async function generateTestKeyPair() {
    const button = document.getElementById('generate-keys-btn');
    if (!button) {
        showNotification('Key generator unavailable.', 'error');
        return;
    }

    const originalText = button.textContent;

    try {
        button.textContent = 'Generating...';
        button.disabled = true;

        // Check if the key generation module is available
        if (typeof window.Secp256k1KeyGen === 'undefined') {
            throw new Error('Key generation module not loaded. Please refresh the page.');
        }

        // Generate the keypair using our module
        const keyPair = await window.Secp256k1KeyGen.generateKeyPair();

        // Validate the generated keys
        if (!window.Secp256k1KeyGen.isValidPrivateKey(keyPair.privateKey)) {
            throw new Error('Generated invalid private key');
        }

        if (!window.Secp256k1KeyGen.isValidPublicKey(keyPair.publicKey)) {
            throw new Error('Generated invalid public key');
        }

        // Display the keys
        document.getElementById('testPrivateKey').value = keyPair.privateKey;
        document.getElementById('testPublicKey').value = keyPair.publicKey;
        document.getElementById('testKeys').style.display = 'block';

        // Restore button
        button.textContent = originalText;
        button.disabled = false;

        showNotification('Test key pair generated! Save the private key securely.', 'success');
    } catch (error) {
        console.error('Key generation error:', error);
        showNotification('Error generating key pair: ' + error.message, 'error');
    } finally {
        button.textContent = originalText;
        button.disabled = false;
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

// Set up event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Friend code validation button
    const validateBtn = document.getElementById('validate-friend-code-btn');
    if (validateBtn) {
        validateBtn.addEventListener('click', validateAndRedirect);
    }
    
    // Key generation button
    const generateBtn = document.getElementById('generate-keys-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generateTestKeyPair);
    }
    
    // Copy buttons
    const copyPrivateBtn = document.getElementById('copy-private-key-btn');
    if (copyPrivateBtn) {
        copyPrivateBtn.addEventListener('click', copyTestPrivateKey);
    }
    
    const copyPublicBtn = document.getElementById('copy-public-key-btn');
    if (copyPublicBtn) {
        copyPublicBtn.addEventListener('click', copyTestPublicKey);
    }
    
    // Friend code hint hover effect
    const hint = document.getElementById('friend-code-hint');
    if (hint) {
        hint.addEventListener('mouseenter', function() {
            this.style.filter = 'blur(0px)';
        });
        hint.addEventListener('mouseleave', function() {
            this.style.filter = 'blur(4px)';
        });
    }
});
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
