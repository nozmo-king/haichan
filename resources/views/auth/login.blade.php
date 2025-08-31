<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Forum - Authentication</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background-color: #f0e0d6;
            margin: 0;
            padding: 50px 20px;
            color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .auth-container {
            background-color: #fff;
            border: 2px solid #ccc;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .auth-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        
        .auth-header h1 {
            margin: 0;
            color: #789922;
            font-size: 24px;
        }
        
        .auth-form {
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #789922;
            background-color: #fff;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #789922;
            color: white;
            border: none;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background-color: #5a7019;
        }
        
        .btn-login:disabled {
            background-color: #999;
            cursor: not-allowed;
        }
        
        .error {
            color: #dd0000;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .info-text {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
        
        .key-example {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            word-break: break-all;
        }
    </style>
    @vite('resources/js/app.js')
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Hai</h1>
            <p>Prove ownership of an allowed secp256k1 key</p>
        </div>
        
        <form id="auth-form" class="auth-form">
            @csrf
            
            <div class="form-group">
                <label for="private_key">Private Key (secp256k1):</label>
                <input 
                    type="password" 
                    id="private_key" 
                    name="private_key" 
                    required 
                    minlength="64" 
                    maxlength="64"
                    placeholder="Enter your 64-character hex private key..."
                    pattern="[a-fA-F0-9]{64}"
                    autocomplete="off"
                >
                <div id="auth-error" class="error" style="display: none;"></div>
            </div>
            
            <button type="button" id="auth-btn" class="btn-login">Authenticate</button>
        </form>
        
        <div class="info-text">
            <strong>How it works:</strong><br>
            • Your private key generates a secp256k1 public key<br>
            • Only pre-approved public keys are allowed access<br>
            • Authentication uses cryptographic signature verification<br>
            • Your private key never leaves your browser<br><br>
            
            <strong>Private key format:</strong>
            <div class="key-example">
                4585a3c70eba6f3d6880b59670174489d115d93a59ec9e8bf9ac3d1585719ce7
            </div>
            
            <strong>Note:</strong> Only users with allowlisted public keys can access this forum.
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authBtn = document.getElementById('auth-btn');
            const authError = document.getElementById('auth-error');
            const privateKeyInput = document.getElementById('private_key');
            
            authBtn.addEventListener('click', async function() {
                const privateKeyHex = privateKeyInput.value.trim();
                
                if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
                    showError('Private key must be 64 hexadecimal characters');
                    return;
                }
                
                try {
                    authBtn.disabled = true;
                    authBtn.textContent = 'Authenticating...';
                    hideError();
                    
                    // Check if auth module loaded
                    if (typeof window.authModule === 'undefined') {
                        throw new Error('Authentication module failed to load. Please refresh and try again.');
                    }
                    
                    const csrfToken = document.querySelector('input[name="_token"]').value;
                    
                    // Perform authentication
                    const authData = await window.authModule.authenticate(privateKeyHex, csrfToken);
                    
                    // Submit login form
                    window.authModule.submitLogin(authData, csrfToken);
                    
                } catch (error) {
                    console.error('Authentication error:', error);
                    showError(error.message || 'Authentication failed');
                    authBtn.disabled = false;
                    authBtn.textContent = 'Authenticate';
                }
            });
            
            function showError(message) {
                authError.textContent = message;
                authError.style.display = 'block';
            }
            
            function hideError() {
                authError.style.display = 'none';
            }
            
            // Test if auth module loaded correctly
            function checkAuthModule() {
                if (typeof window.authModule === 'undefined' && !window.authModuleLoaded) {
                    showError('Authentication module failed to load. Please refresh the page.');
                }
            }
            
            // Listen for module ready event
            document.addEventListener('authModuleReady', () => {
                console.log('Auth module ready!');
                hideError();
            });
            
            // Fallback timeout check
            setTimeout(checkAuthModule, 2000);
        });
    </script>
</body>
</html>