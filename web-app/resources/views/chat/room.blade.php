@extends('layout')

@section('title', $room->name . ' - PoW Chat')

@section('content')
<div id="chat-container" style="margin: 20px auto; max-width: 1400px; height: 80vh; display: flex; gap: 20px; font-family: 'Courier New', monospace;">
    
    <!-- Left Sidebar - Room Info & Users -->
    <div id="chat-sidebar" style="width: 280px; background: var(--ib-panel); border: 2px solid var(--ib-border); border-radius: 8px; display: flex; flex-direction: column;">
        
        <!-- Room Header -->
        <div style="padding: 16px; background: var(--ib-accent); color: var(--ib-bg); border-radius: 6px 6px 0 0;">
            <h3 style="margin: 0 0 4px 0; font-size: 16px; font-weight: bold;">{{ $room->name }}</h3>
            <p style="margin: 0; font-size: 11px; opacity: 0.9;">{{ $room->description }}</p>
        </div>

        <!-- Room Stats -->
        <div style="padding: 12px; border-bottom: 1px solid var(--ib-border);">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 10px;">
                <div style="text-align: center; padding: 6px; background: var(--ib-bg); border-radius: 3px;">
                    <div style="color: var(--ib-accent); font-weight: bold;">{{ $stats['active_users'] }}</div>
                    <div style="color: var(--ib-text-muted);">Online</div>
                </div>
                <div style="text-align: center; padding: 6px; background: var(--ib-bg); border-radius: 3px;">
                    <div style="color: var(--ib-accent); font-weight: bold;">{{ number_format($stats['total_messages']) }}</div>
                    <div style="color: var(--ib-text-muted);">Messages</div>
                </div>
                <div style="text-align: center; padding: 6px; background: var(--ib-bg); border-radius: 3px;">
                    <div style="color: var(--ib-accent); font-weight: bold;">{{ $room->pow_difficulty }}</div>
                    <div style="color: var(--ib-text-muted);">Difficulty</div>
                </div>
                <div style="text-align: center; padding: 6px; background: var(--ib-bg); border-radius: 3px;">
                    <div style="color: var(--ib-accent); font-weight: bold;">{{ $room->message_rate_limit }}</div>
                    <div style="color: var(--ib-text-muted);">Rate/min</div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div style="flex: 1; overflow-y: auto;">
            <div style="padding: 8px 12px; background: var(--ib-bg-alt); font-size: 10px; font-weight: bold; color: var(--ib-text-muted);">
                ACTIVE USERS ({{ count($activeUsers) }})
            </div>
            <div id="user-list" style="padding: 8px;">
                @foreach($activeUsers as $user)
                <div class="user-item" style="display: flex; align-items: center; gap: 8px; padding: 4px 8px; margin: 2px 0; border-radius: 3px; font-size: 11px;">
                    <div style="width: 8px; height: 8px; background: #4CAF50; border-radius: 50%;"></div>
                    <div style="flex: 1; color: var(--ib-text);">
                        {{ $user->bitcoinUser?->getDisplayName() ?? 'Anonymous' }}
                    </div>
                    @if($user->pivot->total_pow_points > 0)
                    <div style="color: var(--ib-accent); font-size: 9px;">
                        {{ number_format($user->pivot->total_pow_points) }}⚡
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Leave Button -->
        <div style="padding: 12px; border-top: 1px solid var(--ib-border);">
            <button onclick="leaveRoom()" style="width: 100%; padding: 8px; background: #dc3545; color: white; border: none; border-radius: 3px; font-size: 11px; cursor: pointer;">
                🚪 Leave Room
            </button>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div id="chat-main" style="flex: 1; display: flex; flex-direction: column; background: var(--ib-panel); border: 2px solid var(--ib-border); border-radius: 8px; overflow: hidden;">
        
        <!-- Chat Messages -->
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 16px; background: var(--ib-bg); font-size: 12px; line-height: 1.4;">
            @foreach($messages as $message)
                @include('chat.partials.message', ['message' => $message])
            @endforeach
        </div>

        <!-- Chat Input -->
        <div id="chat-input-area" style="border-top: 2px solid var(--ib-border); background: var(--ib-panel);">
            
            <!-- Mining Status -->
            <div id="mining-status" style="padding: 8px 16px; background: var(--ib-bg-alt); border-bottom: 1px solid var(--ib-border); font-size: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div id="mining-indicator" style="width: 8px; height: 8px; background: #ccc; border-radius: 50%;"></div>
                    <div id="mining-text">Ready to mine message...</div>
                    <div style="flex: 1;"></div>
                    <div id="mining-stats" style="color: var(--ib-text-muted);"></div>
                </div>
                <div id="mining-progress" style="height: 3px; background: var(--ib-border); border-radius: 1px; margin-top: 4px; overflow: hidden; display: none;">
                    <div id="mining-progress-bar" style="height: 100%; background: var(--ib-accent); width: 0%; transition: width 0.3s ease;"></div>
                </div>
            </div>

            <!-- Input Form -->
            <form id="chat-form" style="display: flex; gap: 12px; padding: 16px;">
                <input type="text" id="message-input" placeholder="Type your message... (will auto-mine PoW)" 
                       style="flex: 1; padding: 10px; border: 1px solid var(--ib-border); border-radius: 4px; background: var(--ib-bg); color: var(--ib-text); font-family: inherit; font-size: 12px;"
                       maxlength="2000" autocomplete="off">
                
                <button type="submit" id="send-button" disabled 
                        style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: not-allowed; font-size: 12px; font-weight: bold;">
                    ⛏️ Mine & Send
                </button>
            </form>

            <!-- Hidden PoW Fields -->
            <input type="hidden" id="pow-nonce">
            <input type="hidden" id="pow-hash">
            <input type="hidden" id="pow-challenge-id">
        </div>
    </div>
</div>

<!-- Chat JavaScript -->
<script>
class HaichanChat {
    constructor(roomId, roomDifficulty) {
        this.roomId = roomId;
        this.roomDifficulty = roomDifficulty;
        this.isMining = false;
        this.currentMessage = '';
        this.miningStartTime = 0;
        this.hashCount = 0;
        this.lastMessageTime = 0;
        
        this.init();
    }

    init() {
        this.messageInput = document.getElementById('message-input');
        this.sendButton = document.getElementById('send-button');
        this.chatForm = document.getElementById('chat-form');
        this.messagesContainer = document.getElementById('chat-messages');
        this.miningIndicator = document.getElementById('mining-indicator');
        this.miningText = document.getElementById('mining-text');
        this.miningStats = document.getElementById('mining-stats');
        this.miningProgress = document.getElementById('mining-progress');
        this.miningProgressBar = document.getElementById('mining-progress-bar');

        this.setupEventListeners();
        this.startMessagePolling();
        
        console.log(`💬 Chat initialized for room ${this.roomId} with difficulty ${this.roomDifficulty}`);
    }

    setupEventListeners() {
        // Auto-mine on message input
        this.messageInput.addEventListener('input', () => {
            clearTimeout(this.miningTimeout);
            this.miningTimeout = setTimeout(() => {
                const message = this.messageInput.value.trim();
                if (message && message !== this.currentMessage) {
                    this.currentMessage = message;
                    this.startMining();
                }
            }, 500);
        });

        // Form submission
        this.chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        // Auto-scroll to bottom on new messages
        this.messagesContainer.addEventListener('DOMNodeInserted', () => {
            this.scrollToBottom();
        });
    }

    async startMining() {
        if (this.isMining || !this.currentMessage) return;

        this.isMining = true;
        this.miningStartTime = Date.now();
        this.hashCount = 0;
        
        // Generate challenge ID
        const challengeId = this.generateChallengeId();
        document.getElementById('pow-challenge-id').value = challengeId;
        
        // Update UI
        this.updateMiningStatus('mining', 'Mining proof of work...');
        this.miningProgress.style.display = 'block';
        
        console.log(`⛏️ Mining chat message: "${this.currentMessage}"`);
        
        await this.mineMessage(challengeId);
    }

    async mineMessage(challengeId) {
        let nonce = 0;
        const maxAttempts = 100000;
        
        while (this.isMining && nonce < maxAttempts) {
            const challengeData = `chat:${this.currentMessage}:${challengeId}:${nonce}`;
            const hash = await this.calculateHash(challengeData);
            this.hashCount++;
            
            // Update progress
            if (this.hashCount % 500 === 0) {
                this.updateMiningProgress();
                await new Promise(resolve => setTimeout(resolve, 1));
            }
            
            // Check if hash meets difficulty
            if (hash.startsWith(this.roomDifficulty.toLowerCase())) {
                // Found valid proof!
                document.getElementById('pow-nonce').value = nonce;
                document.getElementById('pow-hash').value = hash;
                
                this.updateMiningStatus('success', `Proof found: ${hash.substring(0, 12)}...`);
                this.miningProgress.style.display = 'none';
                this.enableSendButton();
                this.isMining = false;
                
                console.log(`💎 Chat proof found: ${hash}`);
                return;
            }
            
            nonce++;
        }
        
        // Mining failed
        this.updateMiningStatus('error', 'Mining timeout - try shorter message');
        this.miningProgress.style.display = 'none';
        this.isMining = false;
    }

    async calculateHash(data) {
        const encoder = new TextEncoder();
        const dataBuffer = encoder.encode(data);
        const hashBuffer = await crypto.subtle.digest('SHA-256', dataBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    generateChallengeId() {
        const array = new Uint8Array(16);
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    updateMiningStatus(type, text) {
        this.miningText.textContent = text;
        
        const colors = {
            ready: '#ccc',
            mining: '#007bff',
            success: '#28a745',
            error: '#dc3545'
        };
        
        this.miningIndicator.style.background = colors[type] || '#ccc';
        
        if (type === 'mining') {
            this.miningIndicator.style.animation = 'pulse 1s infinite';
        } else {
            this.miningIndicator.style.animation = 'none';
        }
    }

    updateMiningProgress() {
        const elapsed = (Date.now() - this.miningStartTime) / 1000;
        const hashrate = Math.floor(this.hashCount / elapsed);
        
        this.miningStats.textContent = `${hashrate.toLocaleString()} H/s • ${this.hashCount.toLocaleString()} hashes`;
        
        const progress = Math.min(90, (this.hashCount / 10000) * 100);
        this.miningProgressBar.style.width = `${progress}%`;
    }

    enableSendButton() {
        this.sendButton.disabled = false;
        this.sendButton.style.background = '#28a745';
        this.sendButton.style.cursor = 'pointer';
        this.sendButton.textContent = '🚀 Send Message';
    }

    disableSendButton() {
        this.sendButton.disabled = true;
        this.sendButton.style.background = '#6c757d';
        this.sendButton.style.cursor = 'not-allowed';
        this.sendButton.textContent = '⛏️ Mine & Send';
    }

    async sendMessage() {
        const message = this.messageInput.value.trim();
        const powHash = document.getElementById('pow-hash').value;
        const powNonce = document.getElementById('pow-nonce').value;
        const powChallengeId = document.getElementById('pow-challenge-id').value;

        if (!message || !powHash || !powNonce || !powChallengeId) {
            alert('Complete mining first!');
            return;
        }

        try {
            const response = await fetch(`/chat/{{ $room->id }}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: message,
                    pow_hash: powHash,
                    pow_nonce: parseInt(powNonce),
                    pow_challenge_id: powChallengeId
                })
            });

            const result = await response.json();

            if (result.success) {
                // Clear form
                this.messageInput.value = '';
                this.currentMessage = '';
                this.clearPowFields();
                this.disableSendButton();
                this.updateMiningStatus('ready', 'Ready to mine message...');
                
                // Show success notification with points
                this.showFloatingNotification(`+${result.message.pow_points} ⚡`, result.message.rarity_color);
                
                // Add message to chat immediately for better UX
                this.addMessageToChat(result.message);
                
                console.log('✅ Message sent successfully');
            } else {
                alert(result.error || 'Failed to send message');
            }

        } catch (error) {
            console.error('Send message error:', error);
            alert('Failed to send message. Please try again.');
        }
    }

    clearPowFields() {
        document.getElementById('pow-nonce').value = '';
        document.getElementById('pow-hash').value = '';
        document.getElementById('pow-challenge-id').value = '';
    }

    showFloatingNotification(text, color = '#28a745') {
        const notification = document.createElement('div');
        notification.textContent = text;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${color};
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            z-index: 10000;
            animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2s forwards;
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 2500);
    }

    addMessageToChat(messageData) {
        const messageElement = this.createMessageElement(messageData);
        this.messagesContainer.appendChild(messageElement);
        this.scrollToBottom();
    }

    createMessageElement(messageData) {
        const div = document.createElement('div');
        div.className = 'chat-message';
        div.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span style="font-weight: bold; color: ${messageData.rarity_color};">
                    ${messageData.username}
                </span>
                <span style="font-size: 10px; color: var(--ib-text-muted);">
                    ${messageData.created_at}
                </span>
                <span style="font-size: 9px; color: ${messageData.rarity_color}; font-weight: bold;">
                    ${messageData.rarity_level} (+${messageData.pow_points}⚡)
                </span>
                <span style="font-size: 8px; color: var(--ib-text-muted); font-family: monospace;">
                    ${messageData.hash_preview}...
                </span>
            </div>
            <div style="padding-left: 8px; border-left: 2px solid ${messageData.rarity_color}; margin-bottom: 12px;">
                ${messageData.message}
            </div>
        `;
        return div;
    }

    async startMessagePolling() {
        setInterval(async () => {
            try {
                const response = await fetch(`/chat/{{ $room->id }}/messages?since=${this.lastMessageTime}`);
                const result = await response.json();
                
                if (result.success && result.messages.length > 0) {
                    result.messages.forEach(message => {
                        if (message.created_at !== this.lastMessageTime) {
                            this.addMessageToChat(message);
                        }
                    });
                    
                    this.lastMessageTime = result.messages[result.messages.length - 1].created_at;
                }
            } catch (error) {
                console.warn('Message polling error:', error);
            }
        }, 3000); // Poll every 3 seconds
    }

    scrollToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }
}

// Global functions
function leaveRoom() {
    if (confirm('Leave this chat room?')) {
        window.location.href = '/chat';
    }
}

// Initialize chat when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.chat = new HaichanChat({{ $room->id }}, '{{ $room->pow_difficulty }}');
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .user-item:hover {
            background: var(--ib-bg-alt);
        }
    `;
    document.head.appendChild(style);
});
</script>

@endsection