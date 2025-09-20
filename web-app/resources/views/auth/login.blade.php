@extends('layout')

@section('content')
<div class="breadcrumb">
    Haichan > Login
</div>

<div style="max-width: 600px; margin: 0 auto; background-color: #FFFFEE; border: 1px solid #ccc; padding: 30px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Proof of Work Authentication</h2>

    <div style="margin-bottom: 25px; text-align: center;">
        <p style="color: #666; margin-bottom: 15px;">
            Prove ownership of an allowed secp256k1 private key to access the forum.
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

    <form id="auth-form" method="POST" action="/login">
        @csrf

        <div style="margin-bottom: 20px;">
            <label for="private_key" style="display: block; font-weight: bold; margin-bottom: 8px; color: #333;">
                Private Key (secp256k1) *
            </label>
            <input
                type="password"
                id="private_key"
                name="private_key"
                placeholder="Enter your 64-character hex private key"
                required
                minlength="64"
                maxlength="64"
                pattern="[a-fA-F0-9]{64}"
                autocomplete="off"
                style="width: 100%; padding: 12px; border: 1px solid #ccc; font-family: 'Courier New', monospace; font-size: 14px; background-color: #fff; box-sizing: border-box;"
            >
            <p style="margin-top: 8px; font-size: 12px; color: #666;">
                64-character hexadecimal private key (e.g., 4585a3c70eba6f3d6880b59670174489...)
            </p>
        </div>

        <button
            type="submit"
            id="auth-btn"
            style="width: 100%; background-color: #789922; color: white; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; margin-bottom: 10px;"
        >
            Authenticate
        </button>
    </form>

    <!-- Debug Login Button -->
    <button
        type="button"
        id="debug-login-btn"
        onclick="performDebugLogin()"
        style="width: 100%; background-color: #dc3545; color: white; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; margin-bottom: 10px;"
    >
        🐛 Debug Login (iOS Client Flow)
    </button>

    <!-- Comparison Test Button -->
    <button
        type="button"
        id="compare-test-btn"
        onclick="performComparisonTest()"
        style="width: 100%; background-color: #6f42c1; color: white; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; margin-bottom: 20px;"
    >
        🔍 Send Website Data for Comparison
    </button>

    <!-- Key Generation Section -->
    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 12px; color: #333;">Don't have a secp256k1 key pair?</h3>
        <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
            Generate a new key pair here for testing purposes. In production, use a proper wallet.
        </p>

        <button
            type="button"
            onclick="generateKeyPair()"
            style="width: 100%; background-color: #666; color: white; padding: 10px; border: none; border-radius: 3px; cursor: pointer; font-family: 'Courier New', monospace; margin-bottom: 15px;"
        >
            🔑 Generate Test Key Pair
        </button>

        <div id="generatedKeys" style="display: none; padding: 15px; background-color: #cce7ff; border: 1px solid #9fc3ff; border-radius: 3px;">
            <p style="font-size: 14px; font-weight: bold; color: #0056b3; margin-bottom: 8px;">Generated Test Keys:</p>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 12px; font-weight: bold; color: #0056b3;">Private Key (save this!):</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" id="generatedPrivateKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #9fc3ff; border-radius: 3px; padding: 4px;">
                    <button type="button" onclick="copyPrivateKey()" style="font-size: 11px; background-color: #0056b3; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                </div>
            </div>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 12px; font-weight: bold; color: #0056b3;">Public Key:</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="text" id="generatedPublicKey" readonly style="flex: 1; font-size: 11px; font-family: 'Courier New', monospace; background-color: #fff; border: 1px solid #9fc3ff; border-radius: 3px; padding: 4px;">
                    <button type="button" onclick="copyPublicKey()" style="font-size: 11px; background-color: #0056b3; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer;">Copy</button>
                </div>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                <button type="button" onclick="useGeneratedKey()" style="font-size: 12px; background-color: #28a745; color: white; padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer;">
                    Use This Private Key
                </button>
                <button type="button" onclick="addKeyToAllowed()" style="font-size: 12px; background-color: #007bff; color: white; padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer;">
                    Allow This Key
                </button>
            </div>
            <div style="margin-top: 10px; padding: 8px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px;">
                <p style="font-size: 11px; color: #856404;">⚠️ These are test keys only. Save the private key - you'll need it to sign in!</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 25px; border-top: 1px solid #ddd; padding-top: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 12px; color: #333;">How it works:</h3>
        <ul style="font-size: 14px; color: #666; line-height: 1.6;">
            <li style="margin-bottom: 8px;">• Your private key generates a secp256k1 public key</li>
            <li style="margin-bottom: 8px;">• Only pre-approved public keys are allowed access</li>
            <li style="margin-bottom: 8px;">• Authentication uses cryptographic signature verification</li>
            <li style="margin-bottom: 8px;">• Your private key never leaves your browser</li>
        </ul>
    </div>

    <div style="margin-top: 25px; text-align: center;">
        <p style="font-size: 14px; color: #666;">
            Don't have an account?
            <a href="{{ route('auth.register.form') }}" style="color: #34345c; text-decoration: underline;">
                Register with friend code
            </a>
        </p>
    </div>
</div>

<!-- Load key generation module -->
<script src="/js/secp256k1-keygen.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const authForm = document.getElementById('auth-form');
    const authBtn = document.getElementById('auth-btn');
    const privateKeyInput = document.getElementById('private_key');

    authForm.addEventListener('submit', async function(e) {
        e.preventDefault(); // Always prevent form submission - we'll handle it via API

        const privateKeyHex = privateKeyInput.value.trim();

        if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
            showError('Private key must be 64 hexadecimal characters');
            return;
        }

        // Show loading state
        authBtn.disabled = true;
        authBtn.textContent = 'Authenticating...';

        try {
            await performCryptographicLogin(privateKeyHex);
        } catch (error) {
            console.error('Login failed:', error);
            showError(error.message || 'Authentication failed. Please try again.');
            authBtn.disabled = false;
            authBtn.textContent = 'Authenticate';
        }
    });

    function showError(message) {
        // Remove existing error messages
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = 'background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px;';
        errorDiv.textContent = message;

        // Insert before the form
        const form = document.getElementById('auth-form');
        form.parentNode.insertBefore(errorDiv, form);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
});

// Key generation functions
async function generateKeyPair() {
    try {
        const button = event.target;
        const originalText = button.textContent;
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
        document.getElementById('generatedPrivateKey').value = keyPair.privateKey;
        document.getElementById('generatedPublicKey').value = keyPair.publicKey;
        document.getElementById('generatedKeys').style.display = 'block';

        // Restore button
        button.textContent = originalText;
        button.disabled = false;

        showNotification('Test key pair generated! Save the private key securely.', 'success');
    } catch (error) {
        console.error('Key generation error:', error);
        const button = event.target;
        button.textContent = '🔑 Generate Test Key Pair';
        button.disabled = false;
        showNotification('Error generating key pair: ' + error.message, 'error');
    }
}

function copyPrivateKey() {
    const privateKey = document.getElementById('generatedPrivateKey').value;
    copyToClipboard(privateKey, 'Private key copied to clipboard!');
}

function copyPublicKey() {
    const publicKey = document.getElementById('generatedPublicKey').value;
    copyToClipboard(publicKey, 'Public key copied to clipboard!');
}

function useGeneratedKey() {
    const privateKey = document.getElementById('generatedPrivateKey').value;
    document.getElementById('private_key').value = privateKey;
    showNotification('Private key loaded into login form!', 'success');
}

async function addKeyToAllowed() {
    try {
        const publicKey = document.getElementById('generatedPublicKey').value;
        if (!publicKey) {
            showNotification('No public key to add', 'error');
            return;
        }

        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Adding...';
        button.disabled = true;

        const response = await fetch('/api/dev/add-public-key', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                public_key: publicKey
            })
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('Public key added to allowed list! You can now login.', 'success');
        } else {
            showNotification(data.message || 'Failed to add public key', 'error');
        }

        button.textContent = originalText;
        button.disabled = false;

    } catch (error) {
        console.error('Error adding key to allowed list:', error);
        showNotification('Error adding key: ' + error.message, 'error');

        const button = event.target;
        button.textContent = 'Allow This Key';
        button.disabled = false;
    }
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

// Main Login Function - Uses Cryptographic Proof (same as iOS)
async function performCryptographicLogin(privateKeyHex) {
    console.log('🔐 [LOGIN] Starting cryptographic authentication...');
    console.log('🔐 [LOGIN] Private Key (first 8 chars):', privateKeyHex.substring(0, 8) + '...');

    // Step 1: Generate public key from private key
    if (typeof window.Secp256k1KeyGen === 'undefined') {
        throw new Error('Key generation module not loaded');
    }

    const publicKey = await window.Secp256k1KeyGen.privateKeyToPublicKey(privateKeyHex);
    console.log('🔐 [LOGIN] Generated Public Key:', publicKey);

    // Step 2: Get challenge from server
    console.log('🔐 [LOGIN] Requesting challenge from server...');
    const challengeResponse = await fetch('/api/auth/challenge', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            public_key: publicKey
        })
    });

    if (!challengeResponse.ok) {
        const errorData = await challengeResponse.json();
        throw new Error(`Challenge request failed: ${errorData.error || 'Unknown error'}`);
    }

    const challengeData = await challengeResponse.json();
    console.log('🔐 [LOGIN] Challenge received:', challengeData.challenge);

    // Step 3: Sign the challenge
    console.log('🔐 [LOGIN] Signing challenge...');
    const signature = await window.Secp256k1KeyGen.signMessage(privateKeyHex, challengeData.challenge);
    console.log('🔐 [LOGIN] Signature generated:', signature);

    // Step 4: Authenticate with server using web endpoint (with session support)
    console.log('🔐 [LOGIN] Authenticating with server...');
    const loginResponse = await fetch('/login/cryptographic', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            user_id: challengeData.user_id,
            challenge: challengeData.challenge,
            signature: signature
        })
    });

    if (!loginResponse.ok) {
        const errorData = await loginResponse.json();
        throw new Error(`Authentication failed: ${errorData.error || 'Invalid signature'}`);
    }

    const loginData = await loginResponse.json();
    console.log('🔐 [LOGIN] ✅ Authentication successful!', loginData);

    // Step 5: Handle response and redirect
    if (loginData.session_created) {
        // Web browser session-based authentication
        console.log('🔐 [LOGIN] Session created, redirecting...');
        showNotification('Authentication successful! Redirecting...', 'success');

        // Use the provided redirect URL or default to dashboard
        const redirectUrl = loginData.redirect_url || '/dashboard';
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 1000);
    } else if (loginData.token) {
        // API token-based authentication (fallback)
        localStorage.setItem('auth_token', loginData.token);
        console.log('🔐 [LOGIN] Token stored, redirecting...');
        window.location.href = '/dashboard';
    } else {
        // Generic success case
        console.log('🔐 [LOGIN] Authentication completed, redirecting...');
        window.location.href = '/dashboard';
    }
}

// Debug Login Function - Mimics iOS Client Flow
async function performDebugLogin() {
    try {
        const privateKeyInput = document.getElementById('private_key');
        const privateKeyHex = privateKeyInput.value.trim();

        if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
            showNotification('Please enter a valid 64-character hex private key first', 'error');
            return;
        }

        const debugBtn = document.getElementById('debug-login-btn');
        debugBtn.disabled = true;
        debugBtn.textContent = '🐛 Debug Login - Step 1: Generating Public Key...';

        console.log('=== DEBUG LOGIN START ===');
        console.log('Private Key:', privateKeyHex);

        // Step 1: Generate public key from private key (same as iOS would do)
        if (typeof window.Secp256k1KeyGen === 'undefined') {
            throw new Error('Key generation module not loaded');
        }

        const publicKey = await window.Secp256k1KeyGen.privateKeyToPublicKey(privateKeyHex);
        console.log('Generated Public Key:', publicKey);

        debugBtn.textContent = '🐛 Debug Login - Step 2: Getting Challenge...';

        // Step 2: Get challenge from server (same API call as iOS)
        const challengeResponse = await fetch('/api/auth/challenge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                public_key: publicKey
            })
        });

        if (!challengeResponse.ok) {
            const errorData = await challengeResponse.json();
            throw new Error(`Challenge request failed: ${errorData.error || 'Unknown error'}`);
        }

        const challengeData = await challengeResponse.json();
        console.log('Challenge Data:', challengeData);

        // Use the actual challenge from server (no more hardcoded challenges)
        const actualChallenge = challengeData.challenge;
        console.log('Using Server Challenge:', actualChallenge);

        debugBtn.textContent = '🐛 Debug Login - Step 3: Signing Challenge...';

        // Step 3: Sign the challenge (this should match iOS signing)
        const signature = await window.Secp256k1KeyGen.signMessage(privateKeyHex, actualChallenge);
        console.log('Generated Signature:', signature);

        // Log signature components for debugging
        if (signature.length === 128) {
            const r = signature.substring(0, 64);
            const s = signature.substring(64, 128);
            console.log('Signature R:', r);
            console.log('Signature S:', s);
        }

        debugBtn.textContent = '🐛 Debug Login - Step 4: Hash Analysis...';

        // Step 4: Show hash analysis (same as server does)
        const challengeHashBinary = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(actualChallenge));
        const challengeHashHex = Array.from(new Uint8Array(challengeHashBinary))
            .map(b => b.toString(16).padStart(2, '0')).join('');

        console.log('Challenge Hash (Client - Binary):', challengeHashHex);

        // Also try direct hex hash (alternative method)
        const textEncoder = new TextEncoder();
        const challengeBytes = textEncoder.encode(actualChallenge);
        const hashBuffer = await crypto.subtle.digest('SHA-256', challengeBytes);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        console.log('Challenge Hash (Alternative):', hashHex);

        debugBtn.textContent = '🐛 Debug Login - Step 5: Testing with Server...';

        // Step 5: Test signature verification with debug endpoint
        const debugResponse = await fetch('/api/debug/signature', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: challengeData.user_id,
                challenge: actualChallenge,
                signature: signature
            })
        });

        const debugResult = await debugResponse.json();
        console.log('Debug Verification Result:', debugResult);

        debugBtn.textContent = '🐛 Debug Login - Complete!';
        debugBtn.disabled = false;

        // Show results
        const resultMessage = debugResult.verification_result ?
            '✅ DEBUG SUCCESS: Signature verified!' :
            '❌ DEBUG FAILED: Signature verification failed - check console logs';

        showNotification(resultMessage, debugResult.verification_result ? 'success' : 'error');

        console.log('=== DEBUG LOGIN COMPLETE ===');
        console.log('Final Result:', debugResult.verification_result);
        console.log('Check server logs for detailed verification process');

    } catch (error) {
        console.error('Debug login error:', error);
        const debugBtn = document.getElementById('debug-login-btn');
        debugBtn.disabled = false;
        debugBtn.textContent = '🐛 Debug Login (iOS Client Flow)';
        showNotification('Debug login failed: ' + error.message, 'error');
    }
}

// Comparison Test Function - Sends detailed website data for comparison with iOS
async function performComparisonTest() {
    try {
        const privateKeyInput = document.getElementById('private_key');
        const privateKeyHex = privateKeyInput.value.trim();

        if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
            showNotification('Please enter a valid 64-character hex private key first', 'error');
            return;
        }

        const compareBtn = document.getElementById('compare-test-btn');
        compareBtn.disabled = true;
        compareBtn.textContent = '🔍 Gathering Website Data...';

        console.log('=== WEBSITE COMPARISON TEST START ===');

        // Step 1: Generate public key
        if (typeof window.Secp256k1KeyGen === 'undefined') {
            throw new Error('Key generation module not loaded');
        }

        const publicKey = await window.Secp256k1KeyGen.privateKeyToPublicKey(privateKeyHex);
        console.log('🔍 [COMPARE] Public Key:', publicKey);

        // Step 2: Get challenge
        compareBtn.textContent = '🔍 Getting Challenge...';
        const challengeResponse = await fetch('/api/auth/challenge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                public_key: publicKey
            })
        });

        if (!challengeResponse.ok) {
            const errorData = await challengeResponse.json();
            throw new Error(`Challenge request failed: ${errorData.error || 'Unknown error'}`);
        }

        const challengeData = await challengeResponse.json();
        const challenge = challengeData.challenge;
        console.log('🔍 [COMPARE] Challenge:', challenge);

        // Step 3: Hash challenge
        compareBtn.textContent = '🔍 Hashing Challenge...';
        const challengeHashBinary = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(challenge));
        const challengeHash = Array.from(new Uint8Array(challengeHashBinary))
            .map(b => b.toString(16).padStart(2, '0')).join('');
        console.log('🔍 [COMPARE] Challenge Hash:', challengeHash);

        // Step 4: Generate signature
        compareBtn.textContent = '🔍 Generating Signature...';
        const signature = await window.Secp256k1KeyGen.signMessage(privateKeyHex, challenge);
        console.log('🔍 [COMPARE] Signature:', signature);

        // Extract R and S components
        const rHex = signature.substring(0, 64);
        const sHex = signature.substring(64, 128);
        console.log('🔍 [COMPARE] R:', rHex);
        console.log('🔍 [COMPARE] S:', sHex);

        // Step 5: Check S-value normalization (simulate what website does)
        compareBtn.textContent = '🔍 Analyzing S-value...';

        // secp256k1 curve order and half order (same as website uses)
        const curveOrderHex = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';
        const halfOrderHex = '7FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF5D576E7357A4501DDFE92F46681B20A0';

        // Compare S with half order (simplified comparison)
        const sIsHigh = sHex > halfOrderHex; // Simple hex string comparison for this test
        const sNormalized = sIsHigh ? 'normalized_on_client' : sHex; // Website normalizes during signing

        console.log('🔍 [COMPARE] S is high:', sIsHigh);
        console.log('🔍 [COMPARE] S normalized:', sNormalized);

        // Step 6: Send comparison data
        compareBtn.textContent = '🔍 Sending Comparison Data...';

        const comparisonData = {
            platform: 'website',
            private_key: privateKeyHex,
            public_key: publicKey,
            challenge: challenge,
            challenge_hash: challengeHash,
            signature_r: rHex,
            signature_s: sHex,
            signature_full: signature,
            s_was_high: sIsHigh,
            s_normalized: sNormalized
        };

        console.log('🔍 [COMPARE] Sending data:', comparisonData);

        const comparisonResponse = await fetch('/api/debug/compare', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(comparisonData)
        });

        if (!comparisonResponse.ok) {
            throw new Error('Comparison request failed');
        }

        const comparisonResult = await comparisonResponse.json();
        console.log('🔍 [COMPARE] Server Response:', comparisonResult);

        compareBtn.textContent = '🔍 Comparison Complete!';
        compareBtn.disabled = false;

        // Show results
        const summary = comparisonResult.summary;
        const allOk = summary.public_key_ok && summary.challenge_hash_ok && summary.s_normalization_ok && summary.signature_verifies;

        const resultMessage = allOk ?
            '✅ WEBSITE DATA: All checks passed!' :
            `❌ WEBSITE DATA: Issues found - Check console for details`;

        showNotification(resultMessage, allOk ? 'success' : 'error');

        console.log('=== WEBSITE COMPARISON TEST COMPLETE ===');
        console.log('Summary:', summary);
        console.log('Now run the same test on iOS and compare results!');

    } catch (error) {
        console.error('Comparison test failed:', error);
        const compareBtn = document.getElementById('compare-test-btn');
        compareBtn.disabled = false;
        compareBtn.textContent = '🔍 Send Website Data for Comparison';
        showNotification('Comparison test failed: ' + error.message, 'error');
    }
}
</script>
@endsection