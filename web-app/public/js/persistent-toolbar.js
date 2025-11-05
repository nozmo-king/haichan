/* HAICHAN PERSISTENT TOOLBAR */
/* Fixed bottom toolbar with mining stats, chat access, and user controls */

class PersistentToolbar {
    constructor() {
        console.log('🔧 PersistentToolbar constructor called');
        
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
            console.warn('⚠️ HaichanState not found, creating minimal state manager');
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
        } else {
            console.log('✅ HaichanState found and connected');
        }
        
        try {
            this.init();
            this.checkForPointsUpdate();
            
            // Initialize theme from storage after toolbar is created
            setTimeout(() => {
                if (this.nightDayToggle) {
                    this.initializeThemeFromStorage();
                }
            }, 100);
            
            console.log('✅ PersistentToolbar constructor completed successfully');
        } catch (error) {
            console.error('❌ Error in PersistentToolbar constructor:', error);
            throw error;
        }
    }
    
    init() {
        console.log('🔧 Initializing toolbar components...');
        this.createToolbar();
        this.bindEvents();
        this.startUpdates();
        console.log('🔧 Persistent toolbar initialized');
    }
    
    createToolbar() {
        
        // Remove existing toolbar if present
        const existing = document.getElementById('haichan-persistent-toolbar');
        if (existing) {
            existing.remove();
        }
        
        // Create toolbar element
        this.element = document.createElement('div');
        this.element.id = 'haichan-persistent-toolbar';
        this.element.className = 'persistent-toolbar';
        
        console.log('📝 Created toolbar element:', this.element);
        
        this.element.innerHTML = `
            <div class="toolbar-section toolbar-left">
                <div class="user-display">
                    <a href="#" class="username-glow username-link" id="toolbar-username" title="View Profile">Loading...</a>
                    <button class="toolbar-btn edit-profile" title="Edit Profile">✏️</button>
                </div>
            </div>
            
            <div class="toolbar-section toolbar-right">
                <div class="mining-summary">
                    <span class="mining-proofs">Proofs: 0</span>
                    <span class="mining-separator">•</span>
                    <span class="mining-points">Points: 0.0</span>
                    <span class="mining-separator">•</span>
                    <span class="mining-hashes">Hashes: 0 H/s</span>
                </div>
                <button class="toolbar-btn recent-threads-toggle" title="Recent Threads">
                    📋 <span class="recent-count">0</span>
                </button>
                <button class="toolbar-btn chat-toggle" title="Toggle Chat">
                    💬 <span class="chat-badge">0</span>
                </button>
                <button class="toolbar-btn mini-dash-toggle" title="Toggle Mining Dashboard">
                    📊
                </button>
                <button class="toolbar-btn night-day-toggle" title="Toggle Night/Day Mode">
                    🌙
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
        this.chatButton = this.element.querySelector('.chat-toggle');
        this.nightDayToggle = this.element.querySelector('.night-day-toggle');
        this.anonymousToggle = this.element.querySelector('.anonymous-toggle');
        this.usernameElement = this.element.querySelector('#toolbar-username');
        this.diamondElement = this.element.querySelector('#toolbar-diamond');
        this.recentThreadsBtn = this.element.querySelector('.recent-threads-toggle');
        
        console.log('🔍 Cached toolbar element references:', {
            chatButton: !!this.chatButton,
            nightDayToggle: !!this.nightDayToggle,
            anonymousToggle: !!this.anonymousToggle,
            usernameElement: !!this.usernameElement,
            diamondElement: !!this.diamondElement,
            recentThreadsBtn: !!this.recentThreadsBtn
        });
        
        // Add styles
        this.addStyles();
        
        // Append to body
        console.log('📌 Appending toolbar to document body...');
        document.body.appendChild(this.element);
        
        // Verify it was added
        const addedElement = document.getElementById('haichan-persistent-toolbar');
        console.log('✅ Toolbar appended successfully:', !!addedElement);
        
        if (addedElement) {
            const computedStyle = getComputedStyle(addedElement);
            console.log('🎨 Toolbar styles applied:', {
                position: computedStyle.position,
                bottom: computedStyle.bottom,
                display: computedStyle.display,
                zIndex: computedStyle.zIndex
            });
            
            // Force display if hidden
            if (computedStyle.display === 'none') {
                console.warn('⚠️ Toolbar hidden, forcing display');
                addedElement.style.display = 'flex';
            }
            
            // Force position if not fixed
            if (computedStyle.position !== 'fixed') {
                console.warn('⚠️ Toolbar not fixed, forcing position');
                addedElement.style.position = 'fixed';
                addedElement.style.bottom = '0';
                addedElement.style.left = '0';
                addedElement.style.right = '0';
                addedElement.style.zIndex = '1000';
            }
        }
        
        // Start periodic hashrate updates
        this.startHashrateTracking();
    }
    
    addStyles() {
        if (document.getElementById('persistent-toolbar-styles')) {
            console.log('📝 Toolbar styles already exist');
            return;
        }
        
        console.log('🎨 Adding toolbar styles...');
        const styles = document.createElement('style');
        styles.id = 'persistent-toolbar-styles';
        styles.textContent = `
            /* Define CSS variables in case they're missing */
            :root {
                --space-xs: 3px;
                --space-sm: 6px;
                --space-md: 10px;
                --space-lg: 16px;
                --text-primary: #333;
                --text-secondary: #666;
                --bg-primary: #F5F5DC;
                --bg-secondary: #E5E5D0;
                --accent-primary: #55c294;
                --border-primary: #708B75;
            }
            
            .persistent-toolbar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 48px;
                background: var(--primary-bg);
                background-image: 
                    repeating-linear-gradient(0deg, transparent, transparent 1px, rgba(0,0,0,.04) 1px, rgba(0,0,0,.04) 2px),
                    repeating-linear-gradient(90deg, transparent, transparent 1px, rgba(0,0,0,.04) 1px, rgba(0,0,0,.04) 2px),
                    repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(0,0,0,.02) 2px, rgba(0,0,0,.02) 4px),
                    repeating-linear-gradient(135deg, transparent, transparent 2px, rgba(0,0,0,.02) 2px, rgba(0,0,0,.02) 4px),
                    url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' /%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.05'/%3E%3C/svg%3E");
                border-top: 1px solid var(--border-color);
                box-shadow: 
                    0 -2px 4px rgba(0, 255, 159, 0.2),
                    inset 0 1px 2px rgba(0, 255, 159, 0.1),
                    inset 0 -1px 0 rgba(0, 0, 0, 0.3);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 10px;
                z-index: 1000;
                font-family: 'Nova Cut', serif, sans-serif;
                font-size: 12px;
                box-shadow: 0 -2px 8px rgba(112, 139, 117, 0.2);
                backdrop-filter: blur(8px);
            }
            
            .toolbar-section {
                display: flex;
                align-items: center;
                gap: 6px;
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
                gap: 6px;
            }
            
            .username-glow {
                color: #00FF00;
                font-weight: 700;
                text-shadow:
                    -1px -1px 0 #FF69B4,
                    1px -1px 0 #FF69B4,
                    -1px 1px 0 #FF69B4,
                    1px 1px 0 #FF69B4,
                    0 0 5px #00FF00,
                    0 0 10px #00FF00,
                    0 0 15px #00FF00,
                    0 0 20px #00FF00;
                animation: glow-pulse 2s ease-in-out infinite alternate;
                font-size: 14px;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .username-link:hover {
                text-shadow:
                    -1px -1px 0 #FF0000,
                    1px -1px 0 #FF0000,
                    -1px 1px 0 #FF0000,
                    1px 1px 0 #FF0000,
                    0 0 8px #000000,
                    0 0 16px #000000,
                    0 0 24px #000000,
                    0 0 32px #000000 !important;
                transform: scale(1.05);
            }
            
            @keyframes glow-pulse {
                from {
                    text-shadow:
                        -1px -1px 0 #FF69B4,
                        1px -1px 0 #FF69B4,
                        -1px 1px 0 #FF69B4,
                        1px 1px 0 #FF69B4,
                        0 0 5px #9AB87A,
                        0 0 10px #9AB87A,
                        0 0 15px #9AB87A,
                        0 0 20px #9AB87A;
                }
                to {
                    text-shadow:
                        -1px -1px 0 #FF69B4,
                        1px -1px 0 #FF69B4,
                        -1px 1px 0 #FF69B4,
                        1px 1px 0 #FF69B4,
                        0 0 2px #9AB87A,
                        0 0 5px #9AB87A,
                        0 0 8px #9AB87A,
                        0 0 12px #9AB87A;
                }
            }
            
            .admin-glow {
                color: #00ff00 !important;
                text-shadow:
                    -1px -1px 0 #FF69B4,
                    1px -1px 0 #FF69B4,
                    -1px 1px 0 #FF69B4,
                    1px 1px 0 #FF69B4,
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
                        -1px -1px 0 #FF69B4,
                        1px -1px 0 #FF69B4,
                        -1px 1px 0 #FF69B4,
                        1px 1px 0 #FF69B4,
                        0 0 10px #00ff00,
                        0 0 20px #00ff00,
                        0 0 30px #00ff00,
                        0 0 40px #00ff00;
                }
                to {
                    text-shadow:
                        -1px -1px 0 #FF69B4,
                        1px -1px 0 #FF69B4,
                        -1px 1px 0 #FF69B4,
                        1px 1px 0 #FF69B4,
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

            .mining-summary {
                display: flex;
                align-items: center;
                gap: 3px;
                color: #9AB87A;
                font-weight: 600;
                background: rgba(154, 184, 122, 0.1);
                padding: 4px 8px;
                border-radius: 4px;
                border: 1px solid #9AB87A;
                font-family: 'Courier New', monospace;
                font-size: 13px;
                margin-right: 10px; /* Add some space between mining info and buttons */
            }
            
            .mining-proofs, .mining-points, .mining-hashes {
                color: #9AB87A;
                text-shadow:
                    -1px -1px 0 #000000,
                    1px -1px 0 #000000,
                    -1px 1px 0 #000000,
                    1px 1px 0 #000000;
            }
            
            .mining-separator {
                color: #6B7A6B;
                opacity: 0.5;
                margin: 0 4px;
            }
            
            .mining-hashrate {
                color: #9AB87A;
                font-weight: bold;
            }
            
            .mining-total {
                color: #708B75;
            }
            
            .mining-session {
                color: #3498db;
            }
            
            .mining-difficulty {
                color: #6B7A6B;
                font-size: 10px;
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
                gap: 6px;
                color: #666;
                font-size: 11px;
            }
            
            .toolbar-btn {
                background: #55c294;
                background-image: 
                    repeating-linear-gradient(0deg, transparent, transparent 1px, rgba(0,0,0,.04) 1px, rgba(0,0,0,.04) 2px),
                    repeating-linear-gradient(90deg, transparent, transparent 1px, rgba(0,0,0,.04) 1px, rgba(0,0,0,.04) 2px),
                    repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(0,0,0,.02) 2px, rgba(0,0,0,.02) 4px),
                    repeating-linear-gradient(135deg, transparent, transparent 2px, rgba(0,0,0,.02) 2px, rgba(0,0,0,.02) 4px),
                    url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' /%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.05'/%3E%3C/svg%3E");
                border: 1px solid #4ab584;
                border-radius: 4px;
                padding: 3px 6px;
                color: #9AB87A;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
                min-width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Courier New', monospace;
                font-weight: 600;
                text-shadow:
                    -1px -1px 0 #000000,
                    1px -1px 0 #000000,
                    -1px 1px 0 #000000,
                    1px 1px 0 #000000;
            }
            
            .toolbar-btn:hover {
                background: #4ab584;
                border-color: #4ab584;
                color: #9AB87A;
            }
            
            .toolbar-btn.active {
                background: #55c294;
                color: #9AB87A;
                border-color: #4ab584;
            }
            
            .chat-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: #dc3545;
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
                background: #55c294;
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
            
            /* Fake dithering effect for all images */
            img:not(.no-dither) {
                filter: contrast(1.15) saturate(0.85);
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
                position: relative;
            }
            
            img:not(.no-dither)::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-image: 
                    repeating-linear-gradient(0deg, transparent 0px, transparent 1px, rgba(0,0,0,.04) 1px, rgba(0,0,0,.04) 2px),
                    repeating-linear-gradient(90deg, transparent 0px, transparent 1px, rgba(0,0,0,.04) 1px, rgba(0,0,0,.04) 2px);
                pointer-events: none;
                mix-blend-mode: overlay;
                opacity: 0.6;
            }
            
            /* Dithering for thread images */
            .post-image img,
            .thread-image img,
            .attachment img {
                filter: contrast(1.15) saturate(0.85);
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
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
        console.log('🔗 Binding toolbar events...');
        
        // Username click to view profile
        const usernameLink = this.element.querySelector('#toolbar-username');
        if (usernameLink) {
            console.log('👤 Binding username link event');
            usernameLink.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('👤 Username link clicked', { currentUserId: this.currentUserId });
                
                if (this.currentUserId) {
                    console.log('🏠 Navigating to profile:', `/user/${this.currentUserId}`);
                    window.location.href = `/user/${this.currentUserId}`;
                } else {
                    console.log('🔐 No user ID, redirecting to login');
                    window.location.href = '/';
                }
            });
        } else {
            console.warn('⚠️ Username link element not found');
        }
        
        // Edit profile button
        const editBtn = this.element.querySelector('.edit-profile');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = '/user/profile/edit';
            });
        } else {
            console.warn('⚠️ Edit profile button not found');
        }
        
        // Recent threads toggle
        if (this.recentThreadsBtn) {
            console.log('📋 Binding recent threads button event');
            this.recentThreadsBtn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('📋 Recent threads clicked');
                this.toggleRecentThreads();
            });
        } else {
            console.warn('⚠️ Recent threads button not found');
        }
        
        // Chat toggle
        if (this.chatButton) {
            console.log('💬 Binding chat button event');
            this.chatButton.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('💬 Chat toggle clicked');
                this.state.toggleChat();
            });
        } else {
            console.warn('⚠️ Chat button not found');
        }
        
        // Mini dashboard toggle
        const miniDashBtn = this.element.querySelector('.mini-dash-toggle');
        if (miniDashBtn) {
            console.log('📊 Binding mini dashboard button event');
            miniDashBtn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('📊 Mini dashboard toggle clicked');
                this.state.toggleMiniDash();
            });
        } else {
            console.warn('⚠️ Mini dashboard button not found');
        }
        
        // Night/Day mode toggle
        if (this.nightDayToggle) {
            console.log('🌙 Binding night/day toggle button event');
            this.nightDayToggle.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('🌙 Night/Day mode toggle clicked');
                this.toggleNightDayMode();
            });
        } else {
            console.warn('⚠️ Night/Day toggle button not found');
        }
        
        // Anonymous mode toggle
        if (this.anonymousToggle) {
            console.log('🎭 Binding anonymous toggle button event');
            this.anonymousToggle.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('🎭 Anonymous mode toggle clicked');
                this.state.toggleAnonymousMode();
            });
        } else {
            console.warn('⚠️ Anonymous toggle button not found');
        }
        
        // Logout button
        const logoutBtn = this.element.querySelector('.logout-btn');
        if (logoutBtn) {
            console.log('🚪 Binding logout button event');
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('🚪 Logout button clicked');
                this.logout();
            });
        } else {
            console.warn('⚠️ Logout button not found');
        }
        
        console.log('✅ All toolbar events bound successfully');
        
        // State listeners
        this.state.on('state:change', (data) => {
            if (data.path.startsWith('mining.') || 
                data.path.startsWith('chat.') || 
                data.path.startsWith('ui.')) {
                this.updateDisplay();
            }
        });

        // Listen for PoW success events to immediately refresh points
        window.addEventListener('pow:success', (event) => {
            console.log('🎯 PoW success detected, refreshing user data', event.detail);
            this.loadUserData();
        });

        // Also listen for generic point updates
        window.addEventListener('points:updated', (event) => {
            console.log('💎 Points updated, refreshing toolbar', event.detail);
            if (event.detail && event.detail.total_points !== undefined) {
                // Immediately update the display with new points
                this.updatePointsDisplay(event.detail.total_points, event.detail.points_awarded);
            } else {
                this.loadUserData();
            }
        });
        
        // Listen for mining progress events from SimplePow
        window.addEventListener('mining:progress', (event) => {
            this.updateDisplay();
        });
        
        window.addEventListener('mining:complete', (event) => {
            this.updateDisplay();
            
            // Refresh user data to get updated points
            setTimeout(() => this.loadUserData(), 1000);
        });
        
        // Listen for proof submission events from mining page
        document.addEventListener('proofSubmitted', (event) => {
            console.log('✅ Proof submitted event:', event.detail);
            if (event.detail && event.detail.total_points !== undefined) {
                // Immediately update the display with new points
                this.updatePointsDisplay(event.detail.total_points, event.detail.points);
            }
            this.loadUserData();
        });
    }
    
    startUpdates() {
        console.log('🚀 Starting toolbar updates...');
        
        // Load user data immediately
        this.loadUserData();
        
        // Update display immediately
        this.updateDisplay();
        
        // Set up periodic updates every second
        setInterval(() => {
            this.updateHashrateDisplay();
        }, 1000);
        
        // Refresh user data every 30 seconds
        setInterval(() => {
            this.loadUserData();
        }, 30000);
        
        console.log('✅ Toolbar update intervals set up');
    }
    
    updateDisplay() {
        const mining = this.state.getState('mining') || {};
        const chat = this.state.getState('chat') || {};
        const ui = this.state.getState('ui') || {};
        
        
        // Update mining display
        const hashesEl = this.element.querySelector('.mining-hashes');
        
        if (hashesEl) {
            const hashText = mining.isActive 
                ? `Hashes: ${mining.hashrate || 0} H/s` 
                : 'Hashes: 0 H/s';
                
            hashesEl.textContent = hashText;
        } else {
            console.warn('⚠️ .mining-hashes element not found');
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
        // Get mining state directly from SimplePow
        let totalHashrate = 0;
        let totalHashes = 0;
        let isActive = false;
        
        if (window.simplePoW) {
            totalHashrate = window.simplePoW.currentHashrate || 0;
            totalHashes = window.simplePoW.totalHashes || 0;
            isActive = window.simplePoW.isMining || false;
            
        } else {
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
        console.log('🔍 Loading user data from API...');
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
            
            console.log('📡 Making API request to /api/user/toolbar-data');
            console.log('🔐 CSRF Token:', csrfToken ? 'Present' : 'Missing');

            const response = await fetch('/api/user/toolbar-data', {
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // Add CSRF token here
                }
            });
            
            console.log('📡 API Response:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('✅ User data loaded:', data);
                this.updateUserDisplay(data);
            } else if (response.status === 401) {
                console.log('👤 User not authenticated, showing anonymous state');
                this.showAnonymousState();
            } else {
                console.error('❌ Failed to load user data:', response.status, await response.text());
                this.showErrorState();
            }
        } catch (error) {
            console.error('❌ Network error loading user data:', error);
            this.showErrorState();
        }
    }
    
    updateUserDisplay(userData) {
        // Store user ID for profile link
        if (userData.user_id) {
            this.currentUserId = userData.user_id;
        }
        
        // Update username with glow effect and achievement emoji
        if (userData.username && this.usernameElement) {
            const username = userData.display_name || userData.username;
            const emoji = userData.personal_21e8_emoji ? userData.personal_21e8_emoji + ' ' : '';
            this.usernameElement.textContent = emoji + username;
            
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
            console.log('💎 Updating diamond display', { 
                personal_21e8_level: userData.personal_21e8_level,
                element: this.diamondElement 
            });
            
            if (userData.personal_21e8_level) {
                this.diamondElement.className = `diamond-color level-${userData.personal_21e8_level}`;
                this.diamondElement.title = `Personal 21e8 Achievement: ${userData.personal_21e8_level}`;
                console.log('💎 Diamond updated to level:', userData.personal_21e8_level);
            } else {
                this.diamondElement.className = 'diamond-color';
                this.diamondElement.title = 'No Personal 21e8 Achievement yet';
                console.log('💎 Diamond shows no achievement');
            }
        } else {
            console.warn('⚠️ Diamond element not found');
        }
        
        // Update recent threads count
        if (userData.recent_threads_count !== undefined) {
            const recentCountEl = this.element.querySelector('.recent-count');
            if (recentCountEl) {
                recentCountEl.textContent = userData.recent_threads_count;
            }
        }
        
        // Update total PoW points and proofs
        if (userData.total_pow_points !== undefined) {
            this.userTotalPow = userData.total_pow_points;
            const pointsEl = this.element.querySelector('.mining-points');
            if (pointsEl) {
                pointsEl.textContent = `Points: ${userData.total_pow_points.toFixed(1)}`;
            }
            const proofsEl = this.element.querySelector('.mining-proofs');
            if (proofsEl) {
                // Assuming 'total_pow_points' can also represent 'proofs' if a specific 'total_proofs' is not available.
                // If a separate 'total_proofs' field exists in userData, it should be used here instead.
                proofsEl.textContent = `Proofs: ${Math.floor(userData.total_pow_points)}`; 
            }
        }
    }
    
    updatePointsDisplay(totalPoints, pointsAwarded = null) {
        this.userTotalPow = totalPoints;
        const pointsEl = this.element.querySelector('.mining-points');
        if (pointsEl) {
            pointsEl.textContent = `Points: ${totalPoints.toFixed(1)}`;
            
            // Add visual feedback for new points
            if (pointsAwarded && pointsAwarded > 0) {
                pointsEl.style.color = '#FFD700';
                pointsEl.style.textShadow = '0 0 8px #FFD700';
                pointsEl.style.transform = 'scale(1.1)';
                
                // Reset after animation
                setTimeout(() => {
                    pointsEl.style.color = '';
                    pointsEl.style.textShadow = '';
                    pointsEl.style.transform = '';
                }, 1500);
            }
        }
        
        const proofsEl = this.element.querySelector('.mining-proofs');
        if (proofsEl) {
            proofsEl.textContent = `Proofs: ${Math.floor(totalPoints)}`;
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
                padding: 10px 0;
                border-bottom: 1px solid var(--border-subtle);
                font-size: 12px;
            }
            
            .thread-item:last-child {
                border-bottom: none;
            }
            
            .thread-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 8px;
                margin-bottom: 4px;
            }
            
            .thread-title {
                font-weight: 600;
                color: var(--text-primary);
                text-decoration: none;
                flex: 1;
                line-height: 1.3;
            }
            
            .thread-title:hover {
                color: var(--accent-primary);
            }
            
            .relation-badge {
                font-size: 10px;
                padding: 2px 6px;
                border-radius: 12px;
                font-weight: 600;
                white-space: nowrap;
                flex-shrink: 0;
            }
            
            .relation-badge.author {
                background: #e3f2fd;
                color: #1976d2;
            }
            
            .relation-badge.replied {
                background: #f3e5f5;
                color: #7b1fa2;
            }
            
            .relation-badge.activity {
                background: #fff3e0;
                color: #f57c00;
            }
            
            .thread-meta {
                color: var(--text-secondary);
                font-size: 10px;
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }
            
            .board-info {
                font-weight: 600;
                color: var(--accent-primary);
            }
            
            .thread-separator {
                opacity: 0.6;
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
        
        const threadItems = threads.map(thread => {
            // Determine user's relation to the thread based on API response
            let relationBadge = '';
            let relationText = '';
            
            if (thread.type === 'created') {
                relationBadge = '<span class="relation-badge author">📝 Created</span>';
                relationText = 'You created this thread';
            } else if (thread.type === 'replied') {
                relationBadge = '<span class="relation-badge replied">💬 Replied</span>';
                relationText = 'You replied to this thread';
            } else {
                relationBadge = '<span class="relation-badge activity">🔥 Activity</span>';
                relationText = 'Recent activity';
            }
            
            return `
                <div class="thread-item">
                    <div class="thread-header">
                        <a href="/${thread.board_code}/${thread.id}" class="thread-title" title="${relationText}">
                            ${thread.title || 'Untitled Thread'}
                        </a>
                        ${relationBadge}
                    </div>
                    <div class="thread-meta">
                        <span class="board-info">/${thread.board_code}/</span>
                        <span class="thread-separator">•</span>
                        <span class="thread-date">${thread.created_at}</span>
                        <span class="thread-separator">•</span>
                        <span class="reply-count">${thread.reply_count || 0} replies</span>
                    </div>
                </div>
            `;
        }).join('');
        
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
    
    showAnonymousState() {
        console.log('👤 Setting up anonymous toolbar state');
        
        if (this.usernameElement) {
            this.usernameElement.textContent = 'Anonymous';
            this.usernameElement.className = 'username-glow username-link';
            this.usernameElement.style.color = '#9AB87A';
            this.usernameElement.style.cursor = 'default';
            this.usernameElement.style.pointerEvents = 'none';
        }
        
        // Update points display for anonymous users
        const pointsEl = this.element.querySelector('.mining-points');
        if (pointsEl) {
            pointsEl.textContent = 'Points: 0.0';
        }
        
        const proofsEl = this.element.querySelector('.mining-proofs');
        if (proofsEl) {
            proofsEl.textContent = 'Proofs: 0';
        }
        
        this.hideAuthenticatedFeatures();
    }
    
    showErrorState() {
        console.log('❌ Setting up error toolbar state');
        
        if (this.usernameElement) {
            this.usernameElement.textContent = 'Error';
            this.usernameElement.style.color = '#ff6b6b';
            this.usernameElement.style.cursor = 'default';
            this.usernameElement.style.pointerEvents = 'none';
        }
        
        const pointsEl = this.element.querySelector('.mining-points');
        if (pointsEl) {
            pointsEl.textContent = 'Points: --';
        }
        
        const proofsEl = this.element.querySelector('.mining-proofs');
        if (proofsEl) {
            proofsEl.textContent = 'Proofs: --';
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
    }

    checkForPointsUpdate() {
        // Check for flash session data indicating points were just awarded
        const pointsAwarded = this.getFlashData('points_awarded');
        const totalPoints = this.getFlashData('total_points');
        
        if (pointsAwarded && totalPoints) {
            console.log('📊 Points update detected from session', { pointsAwarded, totalPoints });
            
            // Dispatch custom event to update UI immediately
            window.dispatchEvent(new CustomEvent('points:updated', {
                detail: {
                    points_awarded: parseFloat(pointsAwarded),
                    total_points: parseFloat(totalPoints)
                }
            }));
            
            // Clear the flash data so it doesn't trigger again
            this.clearFlashData('points_awarded');
            this.clearFlashData('total_points');
        }
    }

    getFlashData(key) {
        const metaTag = document.querySelector(`meta[name="flash-${key}"]`);
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    clearFlashData(key) {
        const metaTag = document.querySelector(`meta[name="flash-${key}"]`);
        if (metaTag) {
            metaTag.remove();
        }
    }
    
    toggleNightDayMode() {
        console.log('🌙 Toggling night/day mode');
        
        // Check current mode
        const isCurrentlyNightMode = document.querySelector('link[href*="serpiente.css"]');
        
        if (isCurrentlyNightMode) {
            // Switch to day mode
            this.enableDayMode();
            this.nightDayToggle.textContent = '☀️';
            localStorage.setItem('forcedTheme', 'day');
        } else {
            // Switch to night mode
            this.enableNightMode();
            this.nightDayToggle.textContent = '🌙';
            localStorage.setItem('forcedTheme', 'night');
        }
    }
    
    enableNightMode() {
        console.log('🌙 Enabling night mode');
        
        // Remove existing night mode stylesheets to avoid duplicates
        const existingSerpiente = document.querySelector('link[href*="serpiente.css"]');
        const existingOverride = document.querySelector('link[href*="serpiente-override.css"]');
        
        if (existingSerpiente) existingSerpiente.remove();
        if (existingOverride) existingOverride.remove();
        
        // Add night mode stylesheets
        const serpienteLink = document.createElement('link');
        serpienteLink.rel = 'stylesheet';
        serpienteLink.href = '/serpiente-assets/serpiente.css';
        document.head.appendChild(serpienteLink);
        
        const overrideLink = document.createElement('link');
        overrideLink.rel = 'stylesheet';
        overrideLink.href = '/serpiente-assets/serpiente-override.css';
        document.head.appendChild(overrideLink);
    }
    
    enableDayMode() {
        console.log('☀️ Enabling day mode');
        
        // Remove night mode stylesheets
        const serpienteLink = document.querySelector('link[href*="serpiente.css"]');
        const overrideLink = document.querySelector('link[href*="serpiente-override.css"]');
        
        if (serpienteLink) serpienteLink.remove();
        if (overrideLink) overrideLink.remove();
    }
    
    initializeThemeFromStorage() {
        const forcedTheme = localStorage.getItem('forcedTheme');
        if (forcedTheme === 'night') {
            this.enableNightMode();
            this.nightDayToggle.textContent = '🌙';
        } else if (forcedTheme === 'day') {
            this.enableDayMode();
            this.nightDayToggle.textContent = '☀️';
        } else {
            // Use default based on time or current state
            const isCurrentlyNightMode = document.querySelector('link[href*="serpiente.css"]');
            this.nightDayToggle.textContent = isCurrentlyNightMode ? '🌙' : '☀️';
        }
    }
}

// Initialize when DOM is ready with error handling
function initializeToolbar() {
    console.log('🔧 Attempting to initialize Haichan Toolbar...');
    console.log('📊 HaichanState available:', !!window.HaichanState);
    console.log('📋 DOM ready state:', document.readyState);
    
    try {
        if (!window.HaichanToolbar) {
            window.HaichanToolbar = new PersistentToolbar();
            console.log('✅ Haichan Toolbar initialized successfully');
        }
    } catch (error) {
        console.error('❌ Failed to initialize Haichan Toolbar:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack
        });
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
