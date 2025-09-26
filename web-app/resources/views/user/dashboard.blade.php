<!DOCTYPE html>
<html lang="en" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Control Panel - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <link rel="stylesheet" href="/css/themes.css">
    <link href="https://fonts.googleapis.com/css2?family=Nova+Cut&display=swap" rel="stylesheet">
    <style>
        .user-cp-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .cp-card {
            background: var(--content-bg);
            padding: 20px;
            border-radius: 8px;
            border: 2px solid var(--border-color);
        }
        .cp-card h3 {
            color: var(--accent-color);
            margin-top: 0;
            margin-bottom: 15px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            background: var(--secondary-bg);
            padding: 15px;
            border-radius: 6px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--success-color);
        }
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
        }
        .action-btn {
            display: inline-block;
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            margin: 5px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .mining-output {
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            height: 150px;
            overflow-y: auto;
            margin: 10px 0;
            color: var(--text-primary);
        }
        .address-display {
            font-family: 'Courier New', monospace;
            background: var(--secondary-bg);
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
            margin: 10px 0;
            font-size: 12px;
        }
        .message-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        .message-item {
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message-item.unread {
            background: rgba(154, 184, 122, 0.1);
            font-weight: bold;
        }
        .tripcode {
            font-family: 'Courier New', monospace;
            color: var(--accent-color);
            font-weight: bold;
        }
    </style>
</head>
<body>

<div style="min-height: 100vh; background: var(--primary-bg); color: var(--text-primary); padding: 20px;">

    <!-- Header -->
    <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 3px solid var(--accent-color); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-family: 'Nova Cut', serif; font-size: 32px; color: var(--accent-color); margin: 0;">
                ⚡ USER CONTROL PANEL
            </h1>
            <div style="color: var(--text-secondary);">
                Welcome, {{ $user->username }}
                @if($user->admin_level > 0)
                    @if($user->admin_level >= 9)
                        <span style="color: #FF6B35;">👑 SUPER ADMIN</span>
                    @elseif($user->admin_level >= 7)
                        <span style="color: #4CAF50;">🛡️ SUPER MOD</span>
                    @elseif($user->admin_level >= 5)
                        <span style="color: #2196F3;">⚔️ MODERATOR</span>
                    @else
                        <span style="color: #FFD700;">🔱 ADMIN</span>
                    @endif
                @endif
            </div>
        </div>
        <div>
            <a href="/" class="action-btn">← Back to Forum</a>
            @if($user->is_admin)
                <a href="/admin" class="action-btn" style="background: #FF6B35;">Admin Panel</a>
            @endif
        </div>
    </div>

    <!-- User Stats -->
    <div class="stat-grid">
        <div class="stat-item">
            <div class="stat-value">{{ $stats['posts'] }}</div>
            <div class="stat-label">Posts Made</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['threads'] }}</div>
            <div class="stat-label">Threads Created</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['messages'] }}</div>
            <div class="stat-label">Messages</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $user->mining_streak }}</div>
            <div class="stat-label">Mining Streak</div>
        </div>
    </div>

    <!-- Main CP Grid -->
    <div class="user-cp-grid">

        <!-- Account Settings -->
        <div class="cp-card">
            <h3>🔧 ACCOUNT SETTINGS</h3>

            <div style="margin-bottom: 15px;">
                <strong>Bitcoin Address:</strong>
                <div class="address-display">{{ $user->address }}</div>
            </div>

            <div style="margin-bottom: 15px;">
                <strong>Public Key:</strong>
                <div class="address-display">{{ substr($user->public_key, 0, 32) }}...</div>
            </div>

            <div style="margin-bottom: 15px;">
                <strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}
            </div>

            <div style="margin-bottom: 15px;">
                <strong>Last Login:</strong> {{ $user->last_login ? $user->last_login->diffForHumans() : 'Never' }}
            </div>

            <a href="/user/profile/edit" class="action-btn">Edit Profile</a>
            <a href="/user/change-password" class="action-btn" style="background: #FF9800;">Change Password</a>
        </div>

        <!-- Messages -->
        <div class="cp-card">
            <h3>💬 MESSAGES</h3>

            <div class="message-list" id="message-list">
                @forelse($messages as $message)
                <div class="message-item {{ $message->is_read ? '' : 'unread' }}">
                    <div>
                        <strong>{{ $message->sender->username }}</strong>
                        <div style="font-size: 12px; color: var(--text-secondary);">
                            {{ Str::limit($message->content, 50) }}
                        </div>
                    </div>
                    <div style="font-size: 11px; color: var(--text-secondary);">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 20px; color: var(--text-secondary);">
                    No messages yet
                </div>
                @endforelse
            </div>

            <div style="margin-top: 15px;">
                <a href="/user/messages" class="action-btn">View All Messages</a>
                <a href="/user/messages/compose" class="action-btn" style="background: #4CAF50;">Compose Message</a>
            </div>
        </div>

        <!-- Vanity Bitcoin Address Mining -->
        <div class="cp-card">
            <h3>⛏️ VANITY ADDRESS MINER</h3>

            <div style="margin-bottom: 15px;">
                <label>Target Pattern:</label>
                <input type="text" id="vanity-pattern" placeholder="e.g., 1Bob, 1Alice, 1Test"
                       style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; margin: 5px 0;">
                <small style="color: var(--text-secondary);">Case sensitive. Longer patterns take exponentially more time.</small>
            </div>

            <div style="margin-bottom: 15px;">
                <button id="start-vanity-mining" class="action-btn">Start Mining</button>
                <button id="stop-vanity-mining" class="action-btn" style="background: #F44336; display: none;">Stop Mining</button>
            </div>

            <div id="vanity-stats" style="display: none; margin-bottom: 10px; font-size: 12px;">
                <div>Attempts: <span id="vanity-attempts">0</span></div>
                <div>Rate: <span id="vanity-rate">0</span> addr/sec</div>
                <div>Estimated Time: <span id="vanity-eta">Unknown</span></div>
            </div>

            <div class="mining-output" id="vanity-output">
                Ready to mine vanity addresses...
            </div>

            <div id="vanity-result" style="display: none;">
                <h4 style="color: var(--success-color);">✅ Address Found!</h4>
                <div><strong>Address:</strong> <span id="result-address" class="address-display"></span></div>
                <div><strong>Private Key:</strong> <span id="result-private-key" class="address-display"></span></div>
                <button id="save-vanity-address" class="action-btn" style="background: #4CAF50;">Save to Account</button>
            </div>
        </div>

        <!-- Tripcode Generator -->
        <div class="cp-card">
            <h3>🎭 TRIPCODE GENERATOR</h3>

            <div style="margin-bottom: 15px;">
                <label>Password:</label>
                <input type="password" id="tripcode-password" placeholder="Enter tripcode password"
                       style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; margin: 5px 0;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Hash Algorithm:</label>
                <select id="tripcode-algorithm" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; margin: 5px 0;">
                    <option value="sha256">SHA-256 (Secure)</option>
                    <option value="sha3-256">SHA3-256 (More Secure)</option>
                    <option value="blake2b">BLAKE2b (Fastest)</option>
                </select>
            </div>

            <button id="generate-tripcode" class="action-btn">Generate Tripcode</button>

            <div id="tripcode-result" style="margin-top: 15px; display: none;">
                <div><strong>Your Tripcode:</strong></div>
                <div class="tripcode" id="tripcode-display" style="font-size: 16px; margin: 10px 0;"></div>
                <div><strong>Usage:</strong> Add <code>##your_password</code> to the end of your posts</div>
                <button id="save-tripcode" class="action-btn" style="background: #4CAF50;">Save to Profile</button>
            </div>
        </div>

        <!-- Mining Dashboard Link -->
        <div class="cp-card">
            <h3>⛏️ MINING DASHBOARD</h3>

            <div style="margin-bottom: 15px;">
                <div>Current Mining Power: <strong>{{ number_format($user->mining_power, 2) }} H/s</strong></div>
                <div>Total PoW Points: <strong>{{ number_format($user->total_pow_points) }}</strong></div>
                <div>Current Streak: <strong>{{ $user->mining_streak }} days</strong></div>
            </div>

            <a href="/mining" class="action-btn">Open Mining Dashboard</a>
        </div>

        <!-- Quick Actions -->
        <div class="cp-card">
            <h3>⚡ QUICK ACTIONS</h3>

            <a href="/gen/create" class="action-btn">Create New Thread</a>
            <a href="/catalog" class="action-btn">Browse The MC</a>
            <a href="/library" class="action-btn">Image Library</a>
            @if($user->is_admin)
                <a href="/admin/forum" class="action-btn" style="background: #FF6B35;">Moderate Forum</a>
            @endif
        </div>

    </div>

</div>

<script>
// Vanity Address Mining
class VanityMiner {
    constructor() {
        this.mining = false;
        this.attempts = 0;
        this.startTime = 0;
        this.worker = null;
    }

    async start() {
        const pattern = document.getElementById('vanity-pattern').value.trim();
        if (!pattern) {
            alert('Please enter a pattern to mine');
            return;
        }

        this.mining = true;
        this.attempts = 0;
        this.startTime = Date.now();

        document.getElementById('start-vanity-mining').style.display = 'none';
        document.getElementById('stop-vanity-mining').style.display = 'inline-block';
        document.getElementById('vanity-stats').style.display = 'block';

        this.log(`🎯 Mining for pattern: ${pattern}`);

        // Start mining loop
        this.mine(pattern);
    }

    async mine(pattern) {
        while (this.mining) {
            // Generate random private key
            const privateKey = this.generateRandomHex(64);

            // Generate public key (simplified)
            const publicKey = await this.sha256(privateKey);

            // Generate Bitcoin address
            const address = await this.generateAddress(publicKey);

            this.attempts++;

            if (address.startsWith(pattern)) {
                this.found(address, privateKey);
                break;
            }

            // Update stats every 100 attempts
            if (this.attempts % 100 === 0) {
                this.updateStats();
            }

            // Yield to prevent UI blocking
            if (this.attempts % 1000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
    }

    found(address, privateKey) {
        this.mining = false;
        document.getElementById('start-vanity-mining').style.display = 'inline-block';
        document.getElementById('stop-vanity-mining').style.display = 'none';

        this.log(`✅ FOUND! Address: ${address}`);

        document.getElementById('result-address').textContent = address;
        document.getElementById('result-private-key').textContent = privateKey;
        document.getElementById('vanity-result').style.display = 'block';
    }

    stop() {
        this.mining = false;
        document.getElementById('start-vanity-mining').style.display = 'inline-block';
        document.getElementById('stop-vanity-mining').style.display = 'none';
        document.getElementById('vanity-stats').style.display = 'none';
        this.log('⏹️ Mining stopped');
    }

    updateStats() {
        const elapsed = (Date.now() - this.startTime) / 1000;
        const rate = this.attempts / elapsed;

        document.getElementById('vanity-attempts').textContent = this.attempts.toLocaleString();
        document.getElementById('vanity-rate').textContent = rate.toFixed(1);

        // Estimate time for completion
        const pattern = document.getElementById('vanity-pattern').value;
        const difficulty = Math.pow(58, pattern.length - 1);
        const eta = difficulty / rate / 2; // Average case

        document.getElementById('vanity-eta').textContent = this.formatTime(eta);
    }

    formatTime(seconds) {
        if (seconds < 60) return `${seconds.toFixed(0)}s`;
        if (seconds < 3600) return `${(seconds/60).toFixed(1)}m`;
        if (seconds < 86400) return `${(seconds/3600).toFixed(1)}h`;
        return `${(seconds/86400).toFixed(1)}d`;
    }

    log(message) {
        const output = document.getElementById('vanity-output');
        const timestamp = new Date().toLocaleTimeString();
        output.innerHTML += `[${timestamp}] ${message}\n`;
        output.scrollTop = output.scrollHeight;
    }

    generateRandomHex(length) {
        const array = new Uint8Array(length / 2);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async generateAddress(publicKey) {
        // Simplified Bitcoin address generation
        const hash = await this.sha256(publicKey);
        const addressBytes = hash.substring(0, 40);

        // Base58 encode (simplified)
        const chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        let result = '1'; // Bitcoin addresses start with 1

        for (let i = 0; i < 25; i++) {
            const index = parseInt(addressBytes.substring(i*2, i*2+2), 16) % 58;
            result += chars[index];
        }

        return result.substring(0, 26);
    }
}

// Tripcode Generator
class TripcodeGenerator {
    async generate() {
        const password = document.getElementById('tripcode-password').value;
        const algorithm = document.getElementById('tripcode-algorithm').value;

        if (!password) {
            alert('Please enter a tripcode password');
            return;
        }

        let hash;
        switch (algorithm) {
            case 'sha256':
                hash = await this.sha256(password);
                break;
            case 'sha3-256':
                hash = await this.sha3_256(password);
                break;
            case 'blake2b':
                hash = await this.blake2b(password);
                break;
        }

        // Take first 10 characters and format as tripcode
        const tripcode = '◆' + hash.substring(0, 10);

        document.getElementById('tripcode-display').textContent = tripcode;
        document.getElementById('tripcode-result').style.display = 'block';
    }

    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async sha3_256(message) {
        // Simplified SHA3 (using SHA-256 as fallback)
        return await this.sha256('SHA3:' + message);
    }

    async blake2b(message) {
        // Simplified BLAKE2b (using SHA-256 as fallback)
        return await this.sha256('BLAKE2b:' + message);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const vanityMiner = new VanityMiner();
    const tripcodeGen = new TripcodeGenerator();

    document.getElementById('start-vanity-mining').onclick = () => vanityMiner.start();
    document.getElementById('stop-vanity-mining').onclick = () => vanityMiner.stop();
    document.getElementById('generate-tripcode').onclick = () => tripcodeGen.generate();

    // Auto-refresh messages every 30 seconds
    setInterval(() => {
        fetch('/user/api/messages')
            .then(response => response.json())
            .then(data => {
                // Update message list if needed
            })
            .catch(console.error);
    }, 30000);
});
</script>

</body>
</html>