<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitcoin Address Upgrade - Enhanced Security</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style nonce="{{ app('csp_nonce') }}">
        body {
            background: var(--background);
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            color: var(--text-primary);
        }
        
        .upgrade-container {
            max-width: 800px;
            margin: 30px auto;
            background: var(--content-bg);
            border: 3px solid #28a745;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 24px rgba(40, 167, 69, 0.2);
        }
        
        h1 {
            color: #28a745;
            text-align: center;
            margin-bottom: 10px;
            font-size: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .upgrade-message {
            text-align: center;
            font-size: 18px;
            margin-bottom: 30px;
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .security-improvements {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .security-improvements h3 {
            color: #1e7e34;
            margin-top: 0;
            font-size: 20px;
        }
        
        .improvement-list {
            list-style: none;
            padding: 0;
        }
        
        .improvement-list li {
            padding: 8px 0;
            border-bottom: 1px solid #d4edda;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .improvement-list li:last-child {
            border-bottom: none;
        }
        
        .improvement-icon {
            font-size: 18px;
            color: #28a745;
            min-width: 24px;
        }
        
        .credentials-box {
            background: #000;
            color: #0f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 20px 0;
            overflow-x: auto;
            border: 1px solid #0f0;
        }
        
        .credential-line {
            margin: 8px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .credential-label {
            color: #0ff;
            font-weight: bold;
            min-width: 120px;
        }
        
        .credential-value {
            color: #0f0;
            word-break: break-all;
            flex: 1;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-2px);
        }
        
        .download-btn {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        
        .download-btn:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
        }
        
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }
        
        .warning-box h4 {
            color: #856404;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .continue-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }
        
        .continue-link a {
            color: #6c757d;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="upgrade-container">
        <h1>
            🔐 Security Upgrade Complete
        </h1>
        
        <div class="upgrade-message">
            <p><strong>Welcome back!</strong> We've enhanced your account security by upgrading you to industry-standard Bitcoin cryptography.</p>
        </div>

        <div class="security-improvements">
            <h3>🛡️ Security Improvements</h3>
            <ul class="improvement-list">
                <li>
                    <span class="improvement-icon">🔒</span>
                    <div>
                        <strong>Real Bitcoin Addresses:</strong> Your new address uses authentic secp256k1 elliptic curve cryptography, the same standard used by Bitcoin Core.
                    </div>
                </li>
                <li>
                    <span class="improvement-icon">⚡</span>
                    <div>
                        <strong>Enhanced Compatibility:</strong> Your address now works with standard Bitcoin tools and wallets.
                    </div>
                </li>
                <li>
                    <span class="improvement-icon">🔐</span>
                    <div>
                        <strong>Stronger Key Generation:</strong> Cryptographically secure random number generation with proper entropy.
                    </div>
                </li>
                <li>
                    <span class="improvement-icon">✅</span>
                    <div>
                        <strong>Same Login:</strong> Your username and password remain unchanged - only the underlying cryptography has been improved.
                    </div>
                </li>
            </ul>
        </div>

        <div class="warning-box">
            <h4>⚠️ New Backup Required</h4>
            <p>Since your Bitcoin address has been upgraded with new cryptographic keys, you'll need to download a fresh backup file. This ensures you have access to your new secure credentials.</p>
            <p><strong>Your old backup file is no longer sufficient</strong> - please download the new one below.</p>
        </div>

        <div class="credentials-box">
            <div class="credential-line">
                <span class="credential-label">USERNAME:</span>
                <span class="credential-value">{{ $user->username }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">NEW ADDRESS:</span>
                <span class="credential-value">{{ $credentials['address'] }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">PUBLIC KEY:</span>
                <span class="credential-value">{{ $credentials['public_key'] }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">STATUS:</span>
                <span class="credential-value">✅ UPGRADED TO REAL BITCOIN CRYPTOGRAPHY</span>
            </div>
        </div>

        <div class="action-buttons">
            <button 
                class="action-btn download-btn" 
                onclick="downloadBackup()" 
                id="download-btn"
            >
                📁 Download New Backup
            </button>
            
            <button 
                class="action-btn" 
                onclick="copyCredentials()"
            >
                📋 Copy to Clipboard
            </button>
        </div>

        <div class="continue-link">
            <p>
                <a href="/" id="continue-link" style="pointer-events: none; color: #ccc;">
                    Continue to Haichan (available after download)
                </a>
            </p>
        </div>
    </div>
    
    <textarea id="backup-content" style="display: none;">{{ $backupContent }}</textarea>
    
    <script nonce="{{ app('csp_nonce') }}">
        let hasDownloaded = false;
        
        // Auto-download backup file on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                downloadBackup();
                showDownloadSuccess();
            }, 2000);
        });
        
        function downloadBackup() {
            const content = document.getElementById('backup-content').value;
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'haichan_upgrade_{{ $user->username }}_{{ date("Y-m-d") }}.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            hasDownloaded = true;
            showDownloadSuccess();
        }
        
        function showDownloadSuccess() {
            const downloadBtn = document.getElementById('download-btn');
            const continueLink = document.getElementById('continue-link');
            
            if (hasDownloaded) {
                downloadBtn.innerHTML = '✅ Backup Downloaded';
                downloadBtn.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
                
                continueLink.style.pointerEvents = 'auto';
                continueLink.style.color = '#007bff';
                continueLink.innerHTML = '🚀 Continue to Haichan';
            }
        }
        
        function copyCredentials() {
            const content = document.getElementById('backup-content').value;
            navigator.clipboard.writeText(content).then(function() {
                showNotification('✅ Credentials copied to clipboard!', 'success');
            }).catch(function(err) {
                showNotification('❌ Failed to copy to clipboard', 'error');
            });
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 1000;
                font-family: 'Courier New', monospace;
                ${type === 'success' ? 'background: #28a745;' : 'background: #dc3545;'}
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 4000);
        }
    </script>
</body>
</html>