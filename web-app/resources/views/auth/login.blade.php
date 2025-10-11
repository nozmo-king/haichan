<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <style>
        .glow-text {
            color: #9AB87A;
            text-shadow:
                0 0 5px #9AB87A,
                0 0 10px #9AB87A,
                0 0 15px #9AB87A,
                0 0 20px #9AB87A;
            animation: glow-pulse 2s ease-in-out infinite alternate;
        }

        @keyframes glow-pulse {
            from {
                text-shadow:
                    0 0 5px #9AB87A,
                    0 0 10px #9AB87A,
                    0 0 15px #9AB87A,
                    0 0 20px #9AB87A;
            }
            to {
                text-shadow:
                    0 0 2px #9AB87A,
                    0 0 5px #9AB87A,
                    0 0 8px #9AB87A,
                    0 0 12px #9AB87A;
            }
        }
    </style>
</head>
<body>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--secondary-bg), var(--primary-bg));">
    <div style="background: var(--primary-bg); padding: 40px; border-radius: 12px; border: 3px solid var(--border-color); max-width: 500px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">

        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--text-primary); margin: 0 0 10px 0;">
                🔐 HAICHAN
            </h1>
            <p style="color: var(--text-secondary); font-size: 14px;">
                If you want the love, you have to <span class="glow-text">log in</span>.
            </p>
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

        <!-- Login Form -->
        <form method="POST" action="/auth/login" style="margin-bottom: 30px;">
            @csrf

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    👤 Username or Bitcoin Address
                </label>
                <input type="text" name="login_identifier" required
                       placeholder="Enter your username or Bitcoin address..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    🔑 Password
                </label>
                <input type="password" name="password" required minlength="6"
                       placeholder="Enter your password..."
                       style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; box-sizing: border-box;">
            </div>

            <button type="submit" style="width: 100%; background: linear-gradient(135deg, var(--border-color), var(--accent-color)); color: var(--text-primary); border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                🚀 LOGIN TO HAICHAN
            </button>
        </form>

        <!-- Anonymous Access -->
        <div style="text-align: center; margin-bottom: 20px; padding: 20px; background: var(--content-bg); border: 1px solid var(--border-color); border-radius: 8px;">
            <h3 style="color: var(--text-primary); font-size: 16px; margin: 0 0 10px 0;">🕵️ Anonymous Access</h3>
            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 15px;">
                Browse boards without logging in (read-only)
            </p>
            <a href="/anon" style="display: inline-block; background: #6B7A6B; color: white; padding: 10px 20px; border-radius: 6px; font-size: 14px; text-decoration: none; transition: all 0.3s ease;">
                Enter Anonymously
            </a>
        </div>

        <!-- Backup Login -->
        <div style="text-align: center; margin-bottom: 20px;">
            <button onclick="toggleBackupLogin()" style="background: var(--secondary-bg); color: var(--text-secondary); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 6px; font-size: 12px; cursor: pointer;">
                🔑 Use Private Key (Backup Login)
            </button>
        </div>

        <!-- Backup Private Key Login Form -->
        <form id="backup-login-form" method="POST" action="/auth/login-backup" style="margin-bottom: 30px; display: none;">
            @csrf
            <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--accent-color);">
                <div style="color: var(--text-primary); font-weight: bold; margin-bottom: 15px; text-align: center;">
                    🆘 BACKUP LOGIN
                </div>
                <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 15px; text-align: center;">
                    Use this only if you lost access to your password
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                        🔑 Private Key
                    </label>
                    <input type="password" name="private_key"
                           placeholder="Enter your 64-character private key..."
                           style="width: 100%; padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--content-bg); color: var(--text-primary); font-size: 14px; font-family: 'Courier New', monospace; box-sizing: border-box;">
                </div>

                <button type="submit" style="width: 100%; background: var(--highlight-color); color: white; border: none; padding: 12px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer;">
                    🆘 BACKUP LOGIN
                </button>
            </div>
        </form>

        <!-- Registration & Friend Codes -->
        <div style="text-align: center; padding-top: 20px; border-top: 2px solid var(--border-color);">
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 15px;">
                Need access? Get a <strong>Friend Code</strong>!
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <a href="/auth/register" style="background: var(--content-bg); color: var(--text-primary); text-decoration: none; padding: 12px; border-radius: 6px; border: 2px solid var(--accent-color); font-size: 13px; font-weight: bold; transition: all 0.3s ease; text-align: center;">
                    📝 REGISTER
                </a>

                <button onclick="generateNewKeys()" style="background: var(--highlight-color); color: white; border: none; padding: 12px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                    🔧 GEN KEYS
                </button>
            </div>
            
            <!-- Anonymous Login Option -->
            <div style="margin-bottom: 20px;">
                <button onclick="loginAnonymously()" style="background: #333; color: white; border: none; width: 100%; padding: 14px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px;"
                        onmouseover="this.style.background='#000'; this.style.transform='scale(1.02)';"
                        onmouseout="this.style.background='#333'; this.style.transform='scale(1)';">
                    <span style="font-size: 18px;">👻</span> BROWSE ANONYMOUSLY
                </button>
                <div style="color: var(--text-secondary); font-size: 11px; margin-top: 8px; line-height: 1.4;">
                    • No account needed<br>
                    • Read-only access<br>
                    • Dark mode enabled<br>
                    • Can't post or mine
                </div>
            </div>

            <!-- Friend Code Section -->
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px; border: 2px solid var(--accent-color);">
                <div style="color: var(--text-primary); font-weight: bold; margin-bottom: 10px; font-size: 14px;">
                    🤝 FRIEND CODE SYSTEM
                </div>
                <div style="color: var(--text-secondary); font-size: 12px; line-height: 1.4; margin-bottom: 15px;">
                    • Each user gets 5 friend codes to share<br>
                    • Friend codes are required for registration<br>
                    • Invite friends and earn mining bonuses<br>
                    • Build your network, increase your power
                </div>
                <div id="friend-code-demo" style="background: var(--secondary-bg); padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 11px; color: var(--text-primary); text-align: center; cursor: pointer;" onclick="generateDemoCode()">
                    DEMO12345678
                </div>
                <div style="color: var(--text-secondary); font-size: 10px; margin-top: 5px;">
                    ↑ Click for a new demo code
                </div>
            </div>
        </div>

        <!-- User Status -->
        <div id="user-status" style="margin-top: 25px; padding: 15px; background: var(--content-bg); border-radius: 8px; text-align: center;">
            <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 8px;">HAICHAN STATUS</div>
            <div id="status-display" style="color: var(--text-primary); font-weight: bold;">Loading...</div>
        </div>
    </div>
</div>

<!-- Key Generation Modal -->
<div id="key-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: var(--primary-bg); padding: 30px; border-radius: 12px; border: 3px solid var(--border-color); max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: var(--text-primary); margin: 0 0 10px 0;">🔧 KEY GENERATION</h2>
            <p style="color: var(--text-secondary); font-size: 13px;">Your cryptographic credentials for Haichan</p>
        </div>

        <div id="generated-keys">
            <!-- Keys will be displayed here -->
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button onclick="closeKeyModal()" style="background: var(--border-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
async function generateNewKeys() {
    document.getElementById('key-modal').style.display = 'flex';
    document.getElementById('generated-keys').innerHTML = '<div style="text-align: center; color: var(--text-secondary);">🔄 Generating keys...</div>';

    try {
        const response = await fetch('/auth/generate-keys');
        const keys = await response.json();

        document.getElementById('generated-keys').innerHTML = `
            <div style="background: var(--content-bg); padding: 20px; border-radius: 8px;">
                <div style="color: #FF6B35; font-weight: bold; margin-bottom: 15px; text-align: center;">
                    ⚠️ SAVE THESE CREDENTIALS IMMEDIATELY ⚠️
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">Private Key (Keep Secret):</label>
                    <input type="text" value="${keys.private_key}" readonly onclick="this.select()"
                           style="width: 100%; padding: 10px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">Public Key:</label>
                    <input type="text" value="${keys.public_key}" readonly onclick="this.select()"
                           style="width: 100%; padding: 10px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 5px;">Address (Your Login ID):</label>
                    <input type="text" value="${keys.address}" readonly onclick="this.select()"
                           style="width: 100%; padding: 10px; background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; box-sizing: border-box;">
                </div>

                <div style="background: #FFF3CD; border: 2px solid #FFC107; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <div style="color: #856404; font-size: 13px;">
                        🔒 <strong>Login with Address + Password</strong><br>
                        • Use your ADDRESS to login normally<br>
                        • Create a PASSWORD during registration<br>
                        • PRIVATE KEY is your backup login method<br>
                        • Keep all credentials safe - we cannot recover lost accounts
                    </div>
                </div>
            </div>
        `;

    } catch (error) {
        document.getElementById('generated-keys').innerHTML = `<div style="color: #FF6B35; text-align: center;">❌ Error: ${error.message}</div>`;
    }
}

function closeKeyModal() {
    document.getElementById('key-modal').style.display = 'none';
}

function toggleBackupLogin() {
    const backupForm = document.getElementById('backup-login-form');
    const isVisible = backupForm.style.display === 'block';
    backupForm.style.display = isVisible ? 'none' : 'block';
}

function generateDemoCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = 'DEMO';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('friend-code-demo').textContent = code;
}

// Load user status
async function loadUserStatus() {
    try {
        const response = await fetch('/auth/invite-status');
        const status = await response.json();

        const slotsColor = status.remaining_slots > 50 ? 'var(--success-color)' :
                          status.remaining_slots > 10 ? 'var(--warning-color)' : 'var(--highlight-color)';

        document.getElementById('status-display').innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; font-size: 11px;">
                <div>
                    <div style="color: var(--accent-color); font-size: 14px; font-weight: bold;">${status.current_users}</div>
                    <div>USERS</div>
                </div>
                <div>
                    <div style="color: ${slotsColor}; font-size: 14px; font-weight: bold;">${status.remaining_slots}</div>
                    <div>SLOTS LEFT</div>
                </div>
                <div>
                    <div style="color: var(--text-primary); font-size: 14px; font-weight: bold;">256</div>
                    <div>MAX</div>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 10px; color: var(--text-secondary);">
                ${status.remaining_slots > 0 ? '✅ Registration OPEN' : '🚫 Registration FULL'}
            </div>
        `;
    } catch (error) {
        document.getElementById('status-display').textContent = 'Status unavailable';
    }
}

// Anonymous login function
function loginAnonymously() {
    // Set anonymous session flag and redirect
    sessionStorage.setItem('anonymous_mode', 'true');
    window.location.href = '/';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadUserStatus();
    setInterval(loadUserStatus, 30000); // Update every 30 seconds
});
</script>

</body>
</html>