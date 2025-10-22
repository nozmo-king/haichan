<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Haichan - Save Your Keys!</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style>
        body {
            background: var(--background);
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            color: var(--text-primary);
        }
        
        .success-container {
            max-width: 700px;
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
        }
        
        .welcome-message {
            text-align: center;
            font-size: 18px;
            margin-bottom: 30px;
            color: var(--text-primary);
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
            word-break: break-all;
        }
        
        .credential-label {
            color: #0f0;
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
        
        .credential-value {
            color: #0f0;
            font-family: 'Courier New', monospace;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        
        .warning-box h3 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .action-btn {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .download-btn {
            background: #28a745;
            color: white;
        }
        
        .download-btn:hover {
            background: #218838;
            transform: scale(1.05);
        }
        
        .copy-btn {
            background: #6c757d;
            color: white;
        }
        
        .copy-btn:hover {
            background: #5a6268;
            transform: scale(1.05);
        }
        
        .continue-btn {
            background: var(--highlight-color);
            color: white;
        }
        
        .continue-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .stats-box {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            padding: 20px;
            background: var(--secondary-bg);
            border-radius: 8px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 5px;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .blinking {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>🎉 Welcome to Haichan!</h1>
        <div class="welcome-message">
            Congratulations, <strong>{{ $user->username }}</strong>! You are user #{{ $user->id }}/256
        </div>
        
        <div class="warning-box blinking">
            <h3>⚠️ CRITICAL - SAVE YOUR KEYS NOW!</h3>
            <p>This is the ONLY time you'll see your private key. Save it immediately!</p>
        </div>
        
        <div class="credentials-box">
            <div class="credential-line">
                <span class="credential-label">USERNAME:</span>
                <span class="credential-value">{{ $user->username }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">USER ID:</span>
                <span class="credential-value">#{{ $user->id }}/256</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">ADDRESS:</span>
                <span class="credential-value">{{ $user->address }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">PUBLIC KEY:</span>
                <span class="credential-value">{{ $publicKey }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">PRIVATE KEY:</span>
                <span class="credential-value">{{ $privateKey }}</span>
            </div>
            <div class="credential-line">
                <span class="credential-label">YOUR INVITE CODE:</span>
                <span class="credential-value">{{ $user->invite_code }}</span>
            </div>
        </div>
        
        <div class="action-buttons">
            <button class="action-btn download-btn" onclick="downloadBackup()">
                <span>💾</span> Download haichan_backup.txt
            </button>
            <button class="action-btn copy-btn" onclick="copyCredentials()">
                <span>📋</span> Copy All to Clipboard
            </button>
        </div>
        
        <div class="stats-box">
            <div class="stat">
                <div class="stat-value">{{ $remainingSlots }}</div>
                <div class="stat-label">Slots Remaining</div>
            </div>
            <div class="stat">
                <div class="stat-value">5</div>
                <div class="stat-label">Friend Codes to Share</div>
            </div>
            <div class="stat">
                <div class="stat-value">1.0</div>
                <div class="stat-label">Mining Power</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="/" class="action-btn continue-btn">
                <span>🏠</span> Enter Haichan
            </a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: var(--secondary-bg); border-radius: 8px;">
            <h4 style="margin-bottom: 15px;">🔐 How to Use Your Keys:</h4>
            <ul style="line-height: 1.8; color: var(--text-secondary);">
                <li><strong>Regular Login:</strong> Use your username + password</li>
                <li><strong>Backup Login:</strong> Use your private key only (no password needed)</li>
                <li><strong>Keep Private Key Secret:</strong> Anyone with your private key can access your account</li>
                <li><strong>Share Invite Code:</strong> Give your invite code to friends to let them join</li>
            </ul>
        </div>
    </div>
    
    <textarea id="backup-content" style="display: none;">{{ $backupContent }}</textarea>
    
    <script>
        // Auto-download backup file on page load
        window.addEventListener('load', function() {
            setTimeout(downloadBackup, 1000);
        });
        
        function downloadBackup() {
            const content = document.getElementById('backup-content').value;
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'haichan_backup_{{ $user->username }}_{{ date("Y-m-d") }}.txt';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Show confirmation
            const btn = document.querySelector('.download-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>✅</span> Downloaded!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 3000);
        }
        
        function copyCredentials() {
            const content = document.getElementById('backup-content').value;
            navigator.clipboard.writeText(content).then(() => {
                const btn = document.querySelector('.copy-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span>✅</span> Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 3000);
            }).catch(err => {
                alert('Failed to copy. Please copy manually from the credentials box.');
            });
        }
    </script>
</body>
</html>