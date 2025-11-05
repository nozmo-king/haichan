/* HAICHAN PERSISTENT CHAT OVERLAY */
/* Slide-out chat panel accessible from any page */

class PersistentChat {
    constructor() {
        this.element = null;
        this.state = window.HaichanState;
        this.isVisible = false;
        this.messages = [];
        this.unreadCount = 0;
        this.isConnected = false;
        
        this.init();
    }
    
    init() {
        this.createChatOverlay();
        this.bindEvents();
        this.connectToGlobalState();
        this.setupWebSocketConnection();
        console.log('💬 Persistent chat overlay initialized');
    }
    
    createChatOverlay() {
        // Remove existing chat if present
        const existing = document.getElementById('haichan-persistent-chat');
        if (existing) existing.remove();
        
        // Create chat overlay element
        this.element = document.createElement('div');
        this.element.id = 'haichan-persistent-chat';
        this.element.className = 'persistent-chat-overlay';
        
        this.element.innerHTML = `
            <div class="chat-header">
                <div class="chat-title">
                    <span class="chat-icon">💬</span>
                    <span class="chat-title-text">Haichan Chat</span>
                    <span class="chat-status" id="chat-connection-status">Connecting...</span>
                </div>
                <div class="chat-controls">
                    <button class="chat-minimize" id="chat-minimize-btn" title="Minimize Chat">
                        ➖
                    </button>
                    <button class="chat-close" id="chat-close-btn" title="Close Chat">
                        ✖️
                    </button>
                </div>
            </div>
            
            <div class="chat-body">
                <div class="chat-messages" id="chat-messages-container">
                    <div class="chat-welcome-message">
                        <div class="welcome-icon">👋</div>
                        <div class="welcome-text">
                            <div class="welcome-title">Welcome to Haichan Chat!</div>
                            <div class="welcome-subtitle">Connect with other miners and discuss threads</div>
                        </div>
                    </div>
                </div>
                
                <div class="chat-input-area">
                    <div class="chat-user-info">
                        <input type="text" id="chat-nickname" placeholder="Enter nickname..." maxlength="20">
                        <button id="chat-set-nickname" title="Set Nickname">✏️</button>
                    </div>
                    <div class="chat-message-input">
                        <input type="text" id="chat-message-input" placeholder="Type a message..." maxlength="500">
                        <button id="chat-send-btn" title="Send Message">
                            <span id="chat-send-emoji">📨</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add styles
        this.addStyles();
        
        // Append to body
        document.body.appendChild(this.element);
        
        // Initially hidden
        this.element.classList.add('chat-hidden');
    }
    
    addStyles() {
        if (document.getElementById('persistent-chat-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'persistent-chat-styles';
        styles.textContent = `
            .persistent-chat-overlay {
                position: fixed;
                top: 0;
                right: 0;
                width: 350px;
                height: 100vh;
                background: var(--bg-primary);
                border-left: 2px solid var(--border-primary);
                z-index: 10001;
                display: flex;
                flex-direction: column;
                font-family: 'Berkeley Mono', 'JetBrains Mono', monospace;
                box-shadow: -4px 0 12px rgba(0,0,0,0.15);
                backdrop-filter: blur(16px);
                transform: translateX(100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .persistent-chat-overlay.chat-visible {
                transform: translateX(0);
            }
            
            .persistent-chat-overlay.chat-minimized {
                height: 48px;
                transform: translateX(calc(100% - 60px));
            }
            
            .chat-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                background: var(--bg-secondary);
                border-bottom: 1px solid var(--border-primary);
                min-height: 24px;
            }
            
            .chat-title {
                display: flex;
                align-items: center;
                gap: 8px;
                color: var(--text-primary);
                font-weight: 600;
                font-size: 14px;
            }
            
            .chat-icon {
                font-size: 16px;
                animation: chat-pulse 2s infinite;
            }
            
            @keyframes chat-pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }
            
            .chat-status {
                font-size: 11px;
                color: var(--text-secondary);
                margin-left: 8px;
            }
            
            .chat-status.connected {
                color: var(--accent-primary);
            }
            
            .chat-controls {
                display: flex;
                gap: 4px;
            }
            
            .chat-minimize,
            .chat-close {
                background: transparent;
                border: none;
                padding: 4px;
                cursor: pointer;
                border-radius: 3px;
                font-size: 12px;
                transition: all 0.2s ease;
                color: var(--text-secondary);
            }
            
            .chat-minimize:hover,
            .chat-close:hover {
                background: var(--accent-hover);
                color: var(--text-primary);
            }
            
            .chat-body {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            
            .chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 12px;
                scrollbar-width: thin;
                scrollbar-color: var(--border-primary) transparent;
            }
            
            .chat-messages::-webkit-scrollbar {
                width: 6px;
            }
            
            .chat-messages::-webkit-scrollbar-track {
                background: transparent;
            }
            
            .chat-messages::-webkit-scrollbar-thumb {
                background: var(--border-primary);
                border-radius: 3px;
            }
            
            .chat-welcome-message {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 16px;
                background: var(--bg-tertiary);
                border-radius: 8px;
                margin-bottom: 12px;
                border: 1px solid var(--border-subtle);
            }
            
            .welcome-icon {
                font-size: 24px;
                flex-shrink: 0;
            }
            
            .welcome-title {
                font-weight: 600;
                color: var(--text-primary);
                font-size: 14px;
                margin-bottom: 4px;
            }
            
            .welcome-subtitle {
                font-size: 12px;
                color: var(--text-secondary);
                line-height: 1.4;
            }
            
            .chat-message {
                margin-bottom: 12px;
                padding: 8px 12px;
                background: var(--bg-alt);
                border-radius: 8px;
                border-left: 3px solid var(--accent-primary);
                animation: message-slide-in 0.3s ease-out;
            }
            
            @keyframes message-slide-in {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .chat-message-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 4px;
            }
            
            .chat-message-author {
                font-weight: 600;
                color: var(--accent-primary);
                font-size: 12px;
            }
            
            .chat-message-time {
                font-size: 10px;
                color: var(--text-muted);
                font-family: monospace;
            }
            
            .chat-message-content {
                color: var(--text-primary);
                font-size: 13px;
                line-height: 1.4;
                word-wrap: break-word;
            }
            
            .chat-input-area {
                padding: 12px;
                background: var(--bg-secondary);
                border-top: 1px solid var(--border-primary);
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .chat-user-info {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            
            .chat-user-info input {
                flex: 1;
                padding: 6px 10px;
                border: 1px solid var(--border-subtle);
                border-radius: 4px;
                background: var(--bg-primary);
                color: var(--text-primary);
                font-size: 12px;
                font-family: inherit;
            }
            
            .chat-user-info button {
                padding: 6px 8px;
                background: var(--accent-primary);
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.2s ease;
            }
            
            .chat-user-info button:hover {
                background: var(--accent-secondary);
                transform: scale(1.05);
            }
            
            .chat-message-input {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            
            .chat-message-input input {
                flex: 1;
                padding: 8px 12px;
                border: 1px solid var(--border-subtle);
                border-radius: 6px;
                background: var(--bg-primary);
                color: var(--text-primary);
                font-size: 13px;
                font-family: inherit;
            }
            
            .chat-message-input input:focus {
                outline: none;
                border-color: var(--accent-primary);
                box-shadow: 0 0 0 2px var(--accent-hover);
            }
            
            .chat-message-input button {
                padding: 8px 12px;
                background: var(--accent-primary);
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.2s ease;
                min-width: 40px;
            }
            
            .chat-message-input button:hover {
                background: var(--accent-secondary);
                transform: translateY(-1px);
            }
            
            .chat-message-input button:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
            }
            
            /* Anonymous mode compatibility */
            .anonymous-mode .persistent-chat-overlay {
                filter: invert(1) hue-rotate(180deg);
            }
            
            /* Mobile responsive */
            @media (max-width: 768px) {
                .persistent-chat-overlay {
                    width: 100vw;
                    height: 50vh;
                    top: auto;
                    bottom: 48px;
                    right: 0;
                    transform: translateY(100%);
                }
                
                .persistent-chat-overlay.chat-visible {
                    transform: translateY(0);
                }
                
                .persistent-chat-overlay.chat-minimized {
                    height: 48px;
                    transform: translateY(calc(100% - 48px));
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    bindEvents() {
        // Close button
        this.element.querySelector('#chat-close-btn').addEventListener('click', () => {
            this.hide();
        });
        
        // Minimize button
        this.element.querySelector('#chat-minimize-btn').addEventListener('click', () => {
            this.toggleMinimize();
        });
        
        // Set nickname
        this.element.querySelector('#chat-set-nickname').addEventListener('click', () => {
            this.setNickname();
        });
        
        // Send message
        const sendBtn = this.element.querySelector('#chat-send-btn');
        const messageInput = this.element.querySelector('#chat-message-input');
        
        sendBtn.addEventListener('click', () => {
            this.sendMessage();
        });
        
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Nickname input
        this.element.querySelector('#chat-nickname').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.setNickname();
            }
        });
    }
    
    connectToGlobalState() {
        // Wait for global state to be available
        const waitForState = () => {
            if (window.HaichanState) {
                // Subscribe to chat state changes
                window.HaichanState.on('state:ui.chatVisible', (data) => {
                    if (data.newValue) {
                        this.show();
                    } else {
                        this.hide();
                    }
                });
                
                // Subscribe to chat messages
                window.HaichanState.on('state:chat.messages', (data) => {
                    this.updateMessages(data.newValue);
                });
                
                window.HaichanState.on('state:chat.unreadCount', (data) => {
                    this.updateUnreadCount(data.newValue);
                });
                
                // Initial state sync
                const chatVisible = window.HaichanState.getState('ui.chatVisible');
                const messages = window.HaichanState.getState('chat.messages');
                const unreadCount = window.HaichanState.getState('chat.unreadCount');
                
                if (chatVisible) this.show();
                if (messages) this.updateMessages(messages);
                if (unreadCount) this.updateUnreadCount(unreadCount);
                
                console.log('💬 Chat connected to global state');
            } else {
                setTimeout(waitForState, 100);
            }
        };
        waitForState();
    }
    
    setupWebSocketConnection() {
        // This would connect to your WebSocket chat server
        // For now, we'll simulate with a mock connection
        setTimeout(() => {
            this.isConnected = true;
            this.updateConnectionStatus('Connected');
            
            if (window.HaichanState) {
                window.HaichanState.setState('chat.isConnected', true);
            }
            
            // Add a welcome message
            this.addMessage('System', 'Connected to Haichan Chat! 🎉', 'system');
        }, 2000);
    }
    
    show() {
        this.isVisible = true;
        this.element.classList.add('chat-visible');
        this.element.classList.remove('chat-hidden');
        
        // Mark messages as read (global state is updated by HaichanGlobalState.toggleChat())
        // if (window.HaichanState) {
        //     window.HaichanState.markChatRead();
        // }
    }
    
    hide() {
        this.isVisible = false;
        this.element.classList.remove('chat-visible');
        this.element.classList.add('chat-hidden');
        
        // Global state is updated by HaichanGlobalState.toggleChat()
        // if (window.HaichanState) {
        //     window.HaichanState.setState('ui.chatVisible', false);
        // }
    }
    
    toggleMinimize() {
        this.element.classList.toggle('chat-minimized');
        const minimizeBtn = this.element.querySelector('#chat-minimize-btn');
        
        if (this.element.classList.contains('chat-minimized')) {
            minimizeBtn.textContent = '➕';
            minimizeBtn.title = 'Restore Chat';
        } else {
            minimizeBtn.textContent = '➖';
            minimizeBtn.title = 'Minimize Chat';
        }
    }
    
    setNickname() {
        const nicknameInput = this.element.querySelector('#chat-nickname');
        const nickname = nicknameInput.value.trim();
        
        if (nickname && nickname.length > 0) {
            localStorage.setItem('haichan_chat_nickname', nickname);
            this.addMessage('System', `Nickname set to: ${nickname}`, 'system');
            nicknameInput.disabled = true;
            this.element.querySelector('#chat-set-nickname').style.display = 'none';
        }
    }
    
    async sendMessage() {
        const messageInput = this.element.querySelector('#chat-message-input');
        const message = messageInput.value.trim();
        
        if (!message || message.length === 0) {
            return;
        }
        
        // Clear input immediately 
        messageInput.value = '';
        
        try {
            // Get PoW challenge for chat message
            const powResult = await this.mineMessage(message);
            if (!powResult.valid) {
                this.addSystemMessage('❌ Mining failed - message not sent');
                return;
            }
            
            // Send to server with PoW
            const response = await fetch('/chat/general', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'include',
                body: JSON.stringify({
                    message: message,
                    pow_nonce: powResult.nonce,
                    pow_hash: powResult.hash,
                    pow_challenge_id: powResult.challengeId
                })
            });
            
            if (!response.ok) {
                const error = await response.json();
                this.addSystemMessage(`❌ Error: ${error.error || 'Failed to send message'}`);
                return;
            }
            
            const result = await response.json();
            if (result.success) {
                // Message will appear via polling, no need to add locally
                this.addSystemMessage(`✓ Message sent (${powResult.hash.substring(0,8)}...)`);
            }
            
        } catch (error) {
            console.error('Chat send error:', error);
            this.addSystemMessage('❌ Network error - message not sent');
        }
    }
    
    async mineMessage(message) {
        // Simple PoW implementation for chat
        const target = '21e8'; // Chat difficulty
        let nonce = 0;
        let hash = '';
        let challengeId = 'chat_' + Date.now() + '_' + Math.random();
        
        this.addSystemMessage('⛏️ Mining message...');
        
        return new Promise((resolve) => {
            const startTime = Date.now();
            
            const mine = () => {
                for (let i = 0; i < 10000; i++) {
                    nonce++;
                    const data = message + nonce + challengeId;
                    hash = this.sha256(data);
                    
                    if (hash.startsWith(target)) {
                        const elapsed = Date.now() - startTime;
                        this.addSystemMessage(`⚡ Found solution in ${elapsed}ms (nonce: ${nonce})`);
                        resolve({
                            valid: true,
                            nonce: nonce.toString(),
                            hash: hash,
                            challengeId: challengeId
                        });
                        return;
                    }
                }
                
                // Continue mining in next tick
                setTimeout(mine, 0);
            };
            
            mine();
        });
    }
    
    sha256(text) {
        // Simple hash for demo - in production use crypto.subtle.digest
        let hash = 0;
        for (let i = 0; i < text.length; i++) {
            const char = text.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(16).padStart(8, '0');
    }
    
    addMessage(author, content, type = 'user') {
        const messagesContainer = this.element.querySelector('#chat-messages-container');
        
        // Remove welcome message if it exists
        const welcomeMessage = messagesContainer.querySelector('.chat-welcome-message');
        if (welcomeMessage) {
            welcomeMessage.remove();
        }
        
        const messageElement = document.createElement('div');
        messageElement.className = `chat-message chat-message-${type}`;
        
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        messageElement.innerHTML = `
            <div class="chat-message-header">
                <span class="chat-message-author">${author}</span>
                <span class="chat-message-time">${timestamp}</span>
            </div>
            <div class="chat-message-content">${this.escapeHtml(content)}</div>
        `;
        
        messagesContainer.appendChild(messageElement);
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Update global state
        if (window.HaichanState) {
            const message = { author, content, type, timestamp: Date.now() };
            window.HaichanState.addChatMessage(message);
        }
        
        // Limit to 50 messages
        const messages = messagesContainer.querySelectorAll('.chat-message');
        if (messages.length > 50) {
            messages[0].remove();
        }
    }
    
    updateMessages(messages) {
        // Update chat display with messages from global state
        // This would be used when loading persisted messages
    }
    
    updateUnreadCount(count) {
        this.unreadCount = count;
        // This would update any UI indicators
    }
    
    updateConnectionStatus(status) {
        const statusElement = this.element.querySelector('#chat-connection-status');
        if (statusElement) {
            statusElement.textContent = status;
            statusElement.classList.toggle('connected', status === 'Connected');
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.HaichanChat = new PersistentChat();
    });
} else {
    window.HaichanChat = new PersistentChat();
}

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PersistentChat;
}