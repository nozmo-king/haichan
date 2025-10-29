/* HAICHAN PERSISTENT TOOLBAR */
/* Fixed bottom toolbar with mining stats, chat access, and user controls */

class PersistentToolbar {
    constructor() {
        this.element = null;
        this.state = window.HaichanState;
        this.miningDisplay = null;
        this.chatButton = null;
        this.anonymousToggle = null;
        this.usernameElement = null;
        this.diamondElement = null;
        this.recentThreadsBtn = null;
        this.userTotalPow = 0;
        
        // Initialize with safe defaults if HaichanState is not available
        if (!this.state) {
            console.warn('HaichanState not found, creating minimal state manager');
            this.state = {
                getState: (path) => {
                    if (path === 'mining') return { isActive: false, hashrate: 0, totalHashes: 0 };
                    if (path === 'chat') return { unreadCount: 0 };
                    if (path === 'ui') return { chatVisible: false, anonymousMode: false, miniDashVisible: false };
                    return {};
                },
                setState: (path, value) => {},
                on: (event, handler) => {},
                toggleChat: () => {},
                toggleMiniDash: () => {},
                toggleAnonymousMode: () => {}
            };
        }
        
        this.init();
    }
    
    init() {
        this.createToolbar();
        this.bindEvents();
        this.startUpdates();
        console.log('🔧 Persistent toolbar initialized');
    }
    
    createToolbar() {
        // Remove existing toolbar if present
        const existing = document.getElementById('haichan-persistent-toolbar');
        if (existing) existing.remove();
        
        // Create toolbar element
        this.element = document.createElement('div');
        this.element.id = 'haichan-persistent-toolbar';
        this.element.className = 'persistent-toolbar';
        
        this.element.innerHTML = `
            <div class="toolbar-section toolbar-left">
                <div class="user-display">
                    <a href="#" class="username-glow username-link" id="toolbar-username" title="View Profile">Loading...</a>
                    <div class="diamond-color" id="toolbar-diamond" title="Personal 21e8 Achievement"></div>
                    <button class="toolbar-btn edit-profile" title="Edit Profile">✏️</button>
                </div>
            </div>
            
            <div class="toolbar-section toolbar-center">
                <div class="mining-display">
                    <span class="mining-status">⛏️</span>
                    <span class="mining-hashrate">0 H/s</span>
                    <span class="mining-total">0 total PoW</span>
                </div>
            </div>
            
            <div class="toolbar-section toolbar-right">
                <button class="toolbar-btn recent-threads-toggle" title="Recent Threads">
                    📋 <span class="recent-count">0</span>
                </button>
                <button class="toolbar-btn chat-toggle" title="Toggle Chat">
                    💬 <span class="chat-badge">0</span>
                </button>
                <button class="toolbar-btn mini-dash-toggle" title="Toggle Mining Dashboard">
                    📊
                </button>
                <button class="toolbar-btn anonymous-toggle" title="Toggle Anonymous Mode">
                    🎭
                </button>
                <button class="toolbar-btn logout-btn" title="Logout">
                    🚪
                </button>
            </div>
        `;
        
        // Cache button references
        this.miningDisplay = this.element.querySelector('.mining-display');
        this.chatButton = this.element.querySelector('.chat-toggle');
        this.anonymousToggle = this.element.querySelector('.anonymous-toggle');
        this.usernameElement = this.element.querySelector('#toolbar-username');
        this.diamondElement = this.element.querySelector('#toolbar-diamond');
        this.recentThreadsBtn = this.element.querySelector('.recent-threads-toggle');
        
        // Add styles
        this.addStyles();
        
        // Append to body
        document.body.appendChild(this.element);
        
        // Start periodic hashrate updates
        this.startHashrateTracking();
    }
    
    addStyles() {
        if (document.getElementById('persistent-toolbar-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'persistent-toolbar-styles';
        styles.textContent = `
            .persistent-toolbar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 48px;
                background: var(--bg-secondary);
                border-top: 2px solid var(--border-primary);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 var(--space-md);
                z-index: 1000;
                font-family: 'Berkeley Mono', 'JetBrains Mono', monospace;
                font-size: 12px;
                box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
                backdrop-filter: blur(8px);
            }
            
            .toolbar-section {
                display: flex;
                align-items: center;
                gap: var(--space-sm);
            }
            
            .toolbar-left {
                flex: 1;
            }
            
            .toolbar-center {
                flex: 0 0 auto;
            }
            
            .toolbar-right {
                flex: 1;
                justify-content: flex-end;
            }
            
            .user-display {
                display: flex;
                align-items: center;
                gap: var(--space-sm);
            }
            
            .username-glow {
                color: #9AB87A;
                font-weight: 700;
                text-shadow:
                    0 0 5px #9AB87A,
                    0 0 10px #9AB87A,
                    0 0 15px #9AB87A,
                    0 0 20px #9AB87A;
                animation: glow-pulse 2s ease-in-out infinite alternate;
                font-size: 14px;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .username-link:hover {
                text-shadow:
                    0 0 8px #9AB87A,
                    0 0 16px #9AB87A,
                    0 0 24px #9AB87A,
                    0 0 32px #9AB87A !important;
                transform: scale(1.05);
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
            
            .admin-glow {
                color: #00ff00 !important;
                text-shadow:
                    0 0 10px #00ff00,
                    0 0 20px #00ff00,
                    0 0 30px #00ff00,
                    0 0 40px #00ff00 !important;
                animation: admin-glow-pulse 1.5s ease-in-out infinite alternate !important;
            }
            
            .mod-glow {
                color: #ff6b35 !important;
                text-shadow:
                    0 0 10px #ff6b35,
                    0 0 20px #ff6b35,
                    0 0 30px #ff6b35 !important;
                animation: mod-glow-pulse 1.8s ease-in-out infinite alternate !important;
            }
            
            @keyframes admin-glow-pulse {
                from {
                    text-shadow:
                        0 0 10px #00ff00,
                        0 0 20px #00ff00,
                        0 0 30px #00ff00,
                        0 0 40px #00ff00;
                }
                to {
                    text-shadow:
                        0 0 5px #00ff00,
                        0 0 10px #00ff00,
                        0 0 15px #00ff00,
                        0 0 20px #00ff00;
                }
            }
            
            @keyframes mod-glow-pulse {
                from {
                    text-shadow:
                        0 0 10px #ff6b35,
                        0 0 20px #ff6b35,
                        0 0 30px #ff6b35;
                }
                to {
                    text-shadow:
                        0 0 5px #ff6b35,
                        0 0 10px #ff6b35,
                        0 0 15px #ff6b35;
                }
            }
            
            .diamond-color {
                width: 16px;
                height: 16px;
                background: linear-gradient(45deg, #FFD700, #FFA500);
                border: 1px solid #B8860B;
                border-radius: 2px;
                position: relative;
                box-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
            }
            
            .diamond-color.level-21e8 {
                background: linear-gradient(45deg, #FFD700, #FFA500);
                box-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
            }
            
            .diamond-color.level-21e80 {
                background: linear-gradient(45deg, #FF4500, #DC143C);
                box-shadow: 0 0 8px rgba(255, 69, 0, 0.6);
            }
            
            .diamond-color.level-21e800 {
                background: linear-gradient(45deg, #9400D3, #4B0082);
                box-shadow: 0 0 8px rgba(148, 0, 211, 0.6);
            }
            
            .diamond-color.level-21e8000 {
                background: linear-gradient(45deg, #00CED1, #00BFFF);
                box-shadow: 0 0 8px rgba(0, 206, 209, 0.6);
            }
            
            .diamond-color.level-21e80000 {
                background: linear-gradient(45deg, #FF1493, #FF69B4);
                box-shadow: 0 0 8px rgba(255, 20, 147, 0.8);
                animation: diamond-ultimate 3s ease-in-out infinite;
            }
            
            @keyframes diamond-ultimate {
                0%, 100% { transform: scale(1) rotate(0deg); }
                50% { transform: scale(1.2) rotate(90deg); }
            }

            .mining-display {
                display: flex;
                align-items: center;
                gap: var(--space-xs);
                color: var(--text-mining);
                font-weight: 600;
            }
            
            .mining-status {
                animation: mining-pulse 2s infinite;
            }
            
            @keyframes mining-pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            
            .site-stats {
                display: flex;
                align-items: center;
                gap: var(--space-sm);
                color: var(--text-secondary);
                font-size: 11px;
            }
            
            .toolbar-btn {
                background: transparent;
                border: 1px solid var(--border-subtle);
                border-radius: 4px;
                padding: var(--space-xs) var(--space-sm);
                color: var(--text-secondary);
                font-size: 14px;
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
                min-width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .toolbar-btn:hover {
                background: var(--accent-hover);
                border-color: var(--border-accent);
                color: var(--text-primary);
            }
            
            .toolbar-btn.active {
                background: var(--accent-primary);
                color: white;
                border-color: var(--accent-primary);
            }
            
            .chat-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: var(--text-error);
                color: white;
                border-radius: 50%;
                width: 16px;
                height: 16px;
                font-size: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            
            .chat-badge.visible {
                opacity: 1;
            }
            
            .recent-count {
                position: absolute;
                top: -4px;
                right: -4px;
                background: var(--accent-primary);
                color: white;
                border-radius: 50%;
                width: 16px;
                height: 16px;
                font-size: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }

            .update-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: #FFD700;
                color: #1a1a1a;
                border-radius: 50%;
                width: 16px;
                height: 16px;
                font-size: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }
            
            /* Add bottom padding to body to account for toolbar */
            body {
                padding-bottom: 48px;
            }
            
            /* Anonymous mode styling */
            .anonymous-mode .persistent-toolbar {
                filter: invert(1) hue-rotate(180deg);
            }
            
            /* Hide on very small screens */
            @media (max-height: 400px) {
                .persistent-toolbar {
                    display: none;
                }
                body {
                    padding-bottom: 0;
                }
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    bindEvents() {
        // Username click to view profile
        const usernameLink = this.element.querySelector('#toolbar-username');
        if (usernameLink) {
            usernameLink.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.currentUserId) {
                    window.location.href = `/user/${this.currentUserId}`;
                } else {
                    window.location.href = '/user/profile';
                }
            });
        }
        
        // Edit profile button
        this.element.querySelector('.edit-profile').addEventListener('click', () => {
            window.location.href = '/user/profile/edit';
        });
        
        // Recent threads toggle
        this.recentThreadsBtn.addEventListener('click', () => {
            this.toggleRecentThreads();
        });
        
        // Chat toggle
        this.chatButton.addEventListener('click', () => {
            this.state.toggleChat();
        });
        
        // Mini dashboard toggle
        this.element.querySelector('.mini-dash-toggle').addEventListener('click', () => {
            this.state.toggleMiniDash();
        });
        
        // Anonymous mode toggle
        this.anonymousToggle.addEventListener('click', () => {
            this.state.toggleAnonymousMode();
        });
        
        // Logout button
        this.element.querySelector('.logout-btn').addEventListener('click', () => {
            this.logout();
        });
        
        // State listeners
        this.state.on('state:change', (data) => {
            if (data.path.startsWith('mining.') || 
                data.path.startsWith('chat.') || 
                data.path.startsWith('ui.')) {
                this.updateDisplay();
            }
        });
    }
    
    startUpdates() {
        // Load user data immediately
        this.loadUserData();
        
        // Update display immediately
        this.updateDisplay();
        
        // Set up periodic updates
        setInterval(() => {
            this.updateHashrateDisplay();
        }, 1000);
        
        // Load user data every 30 seconds
        setInterval(() => {
            this.loadUserData();
        }, 30000);
    }
    
    updateDisplay() {
        const mining = this.state.getState('mining') || {};
        const chat = this.state.getState('chat') || {};
        const ui = this.state.getState('ui') || {};
        
        // Update mining display
        const hashrateEl = this.element.querySelector('.mining-hashrate');
        const totalEl = this.element.querySelector('.mining-total');
        const statusEl = this.element.querySelector('.mining-status');
        
        if (hashrateEl && statusEl) {
            if (mining.isActive) {
                hashrateEl.textContent = `${mining.hashrate || 0} H/s`;
                statusEl.textContent = '⛏️';
                statusEl.style.animation = 'mining-pulse 2s infinite';
            } else {
                hashrateEl.textContent = '0 H/s';
                statusEl.textContent = '⏸️';
                statusEl.style.animation = 'none';
            }
        }
        
        if (totalEl) {
            // Check if we have user-specific total PoW, otherwise use mining total
            if (this.userTotalPow !== undefined) {
                totalEl.textContent = `${this.userTotalPow} total PoW`;
            } else {
                totalEl.textContent = `${mining.totalHashes || 0} total PoW`;
            }
        }
        
        // Update chat badge
        const badge = this.element.querySelector('.chat-badge');
        if (badge) {
            badge.textContent = chat.unreadCount || 0;
            badge.classList.toggle('visible', (chat.unreadCount || 0) > 0);
        }
        
        // Update button states
        if (this.chatButton) {
            this.chatButton.classList.toggle('active', ui.chatVisible);
        }
        if (this.anonymousToggle) {
            this.anonymousToggle.classList.toggle('active', ui.anonymousMode);
        }
        
        const miniDashToggle = this.element.querySelector('.mini-dash-toggle');
        if (miniDashToggle) {
            miniDashToggle.classList.toggle('active', ui.miniDashVisible);
        }
    }
    
    updateHashrateDisplay() {
        // Track real mining activity from various sources
        let totalHashrate = 0;
        let totalHashes = 0;
        let isActive = false;
        
        // Check for mining activity from different systems
        if (window.currentMiner && window.currentMiner.hashCount) {
            totalHashes += window.currentMiner.hashCount;
            isActive = true;
        }
        
        if (window.simplePow) {
            if (window.simplePow.currentHashrate) {
                totalHashrate += window.simplePow.currentHashrate;
            }
            if (window.simplePow.totalHashes) {
                totalHashes += window.simplePow.totalHashes;
            }
        }
        
        // Check for any active mining workers
        if (window.miningWorkers && window.miningWorkers.length > 0) {
            window.miningWorkers.forEach(worker => {
                if (worker.hashrate) totalHashrate += worker.hashrate;
                if (worker.totalHashes) totalHashes += worker.totalHashes;
                if (worker.isRunning) isActive = true;
            });
        }
        
        // Update global state
        this.state.setState('mining.hashrate', Math.round(totalHashrate));
        this.state.setState('mining.totalHashes', totalHashes);
        this.state.setState('mining.isActive', isActive);
        
        // Force UI update
        this.updateDisplay();
    }
    
    startHashrateTracking() {
        // Update hashrate display every 2 seconds
        setInterval(() => {
            this.updateHashrateDisplay();
        }, 2000);
        
        // Initial update
        this.updateHashrateDisplay();
    }
    
    show() {
        this.element.style.display = 'flex';
        this.state.setState('ui.toolbarVisible', true);
    }
    
    hide() {
        this.element.style.display = 'none';
        this.state.setState('ui.toolbarVisible', false);
    }
    
    async loadUserData() {
        try {
            const response = await fetch('/api/user/toolbar-data', {
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('User data loaded:', data);
                this.updateUserDisplay(data);
            } else {
                console.error('Failed to load user data:', response.status);
                // Hide username element if not authenticated
                if (this.usernameElement) {
                    this.usernameElement.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to load user data for toolbar:', error);
            // Hide username element on error
            if (this.usernameElement) {
                this.usernameElement.style.display = 'none';
            }
        }
    }
    
    updateUserDisplay(userData) {
        // Store user ID for profile link
        if (userData.user_id) {
            this.currentUserId = userData.user_id;
        }
        
        // Update username with glow effect
        if (userData.username && this.usernameElement) {
            this.usernameElement.textContent = userData.display_name || userData.username;
            
            // Add admin/mod glow effects
            if (userData.is_admin) {
                this.usernameElement.className = 'username-glow username-link admin-glow';
            } else if (userData.is_moderator) {
                this.usernameElement.className = 'username-glow username-link mod-glow';
            } else {
                this.usernameElement.className = 'username-glow username-link';
            }
        }
        
        // Update 21e8 diamond color based on achievement level
        if (this.diamondElement) {
            if (userData.personal_21e8_level) {
                this.diamondElement.className = `diamond-color level-${userData.personal_21e8_level}`;
                this.diamondElement.title = `Personal 21e8 Achievement: ${userData.personal_21e8_level}`;
            } else {
                this.diamondElement.className = 'diamond-color';
                this.diamondElement.title = 'No Personal 21e8 Achievement yet';
            }
        }
        
        // Update recent threads count
        if (userData.recent_threads_count !== undefined) {
            const recentCountEl = this.element.querySelector('.recent-count');
            if (recentCountEl) {
                recentCountEl.textContent = userData.recent_threads_count;
            }
        }
        
        // Update total PoW points
        if (userData.total_pow_points !== undefined) {
            this.userTotalPow = userData.total_pow_points;
            const totalEl = this.element.querySelector('.mining-total');
            if (totalEl) {
                totalEl.textContent = `${userData.total_pow_points} total PoW`;
            }
        }
    }
    
    toggleRecentThreads() {
        // Create or toggle recent threads modal
        let modal = document.getElementById('recent-threads-modal');
        
        if (modal) {
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
            return;
        }
        
        // Create modal
        modal = document.createElement('div');
        modal.id = 'recent-threads-modal';
        modal.className = 'toolbar-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Recent Threads</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body" id="recent-threads-content">
                    Loading...
                </div>
            </div>
        `;
        
        // Add modal styles
        const modalStyles = document.createElement('style');
        modalStyles.innerHTML = `
            .toolbar-modal {
                position: fixed;
                bottom: 60px;
                right: 20px;
                background: var(--bg-primary);
                border: 2px solid var(--border-primary);
                border-radius: 8px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.2);
                z-index: 1001;
                width: 400px;
                max-height: 300px;
                font-family: inherit;
            }
            
            .modal-content {
                padding: 0;
            }
            
            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                background: var(--bg-secondary);
                border-bottom: 1px solid var(--border-primary);
            }
            
            .modal-header h3 {
                margin: 0;
                font-size: 14px;
                color: var(--text-primary);
            }
            
            .modal-close {
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                color: var(--text-secondary);
            }
            
            .modal-body {
                padding: 16px;
                max-height: 200px;
                overflow-y: auto;
                color: var(--text-primary);
            }
            
            .thread-item {
                padding: 8px 0;
                border-bottom: 1px solid var(--border-subtle);
                font-size: 12px;
            }
            
            .thread-item:last-child {
                border-bottom: none;
            }
            
            .thread-title {
                font-weight: 600;
                color: var(--text-primary);
                text-decoration: none;
            }
            
            .thread-title:hover {
                color: var(--accent-primary);
            }
            
            .thread-meta {
                color: var(--text-secondary);
                font-size: 10px;
                margin-top: 4px;
            }
        `;
        
        document.head.appendChild(modalStyles);
        document.body.appendChild(modal);
        
        // Close button handler
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.style.display = 'none';
        });
        
        // Load recent threads
        this.loadRecentThreads();
    }
    
    async loadRecentThreads() {
        try {
            const response = await fetch('/api/user/recent-threads', {
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const threads = await response.json();
                this.displayRecentThreads(threads);
            }
        } catch (error) {
            console.error('Failed to load recent threads:', error);
            document.getElementById('recent-threads-content').innerHTML = 'Failed to load threads';
        }
    }
    
    displayRecentThreads(threads) {
        const container = document.getElementById('recent-threads-content');
        
        if (threads.length === 0) {
            container.innerHTML = '<p>No recent threads found</p>';
            return;
        }
        
        const threadItems = threads.map(thread => `
            <div class="thread-item">
                <a href="/boards/${thread.board_code}/threads/${thread.id}" class="thread-title">
                    ${thread.title}
                </a>
                <div class="thread-meta">
                    /${thread.board_code}/ • ${thread.created_at} • ${thread.reply_count} replies
                </div>
            </div>
        `).join('');
        
        container.innerHTML = threadItems;
    }
    
    logout() {
        if (confirm('Are you sure you want to logout?')) {
            // Create a form to POST to logout endpoint
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    hideAuthenticatedFeatures() {
        // Hide buttons that require authentication
        const editBtn = this.element.querySelector('.edit-profile');
        const logoutBtn = this.element.querySelector('.logout-btn');
        const recentThreadsBtn = this.element.querySelector('.recent-threads-toggle');
        
        if (editBtn) editBtn.style.display = 'none';
        if (logoutBtn) logoutBtn.style.display = 'none';
        if (recentThreadsBtn) recentThreadsBtn.style.display = 'none';
        
        // Make username not clickable for anonymous users
        if (this.usernameElement) {
            this.usernameElement.style.cursor = 'default';
            this.usernameElement.style.pointerEvents = 'none';
        }
    }
}

// Initialize when DOM is ready with error handling
function initializeToolbar() {
    try {
        if (!window.HaichanToolbar) {
            window.HaichanToolbar = new PersistentToolbar();
            console.log('✅ Haichan Toolbar initialized successfully');
        }
    } catch (error) {
        console.error('❌ Failed to initialize Haichan Toolbar:', error);
        // Retry after 1 second
        setTimeout(initializeToolbar, 1000);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeToolbar);
} else {
    initializeToolbar();
}

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PersistentToolbar;
}