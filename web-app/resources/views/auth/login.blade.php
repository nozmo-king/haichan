<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haichan - Proof of Work Authentication</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #0f1411, #1a1a1a);
            margin: 0;
            padding: 50px 20px;
            color: #e7dfb5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Animated background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 100px,
                rgba(231, 223, 181, 0.02) 100px,
                rgba(231, 223, 181, 0.02) 101px
            );
            animation: bgMove 20s linear infinite;
        }

        @keyframes bgMove {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(0); }
        }
        
        .auth-container {
            background: linear-gradient(135deg, #2d2d2d, #1a1a1a);
            border: 1px solid #444;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 49%, rgba(231, 223, 181, 0.03) 50%, transparent 51%);
            border-radius: 12px;
            pointer-events: none;
        }
        
        .auth-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #444;
            padding-bottom: 20px;
            position: relative;
        }
        
        .auth-header h1 {
            margin: 0 0 10px 0;
            color: #e7dfb5;
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 0 0 20px rgba(231, 223, 181, 0.3);
            letter-spacing: 2px;
        }

        .auth-header .subtitle {
            color: #ffd700;
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .auth-header .tagline {
            color: #888;
            font-size: 0.9rem;
            font-style: italic;
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
            padding: 15px;
            border: 1px solid #444;
            background: #1a1a1a;
            color: #e7dfb5;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            border-radius: 8px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ffd700;
            background: #0f1411;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #ffd700, #e6c200);
            color: #000;
            border: none;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #e6c200, #ffd700);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-login:disabled {
            background: #444;
            color: #888;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
            <h1>HAICHAN</h1>
            <div class="subtitle">⛏️ PROOF OF WORK FORUM ⛏️</div>
            <div class="tagline">Prove ownership of an allowed secp256k1 key</div>
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