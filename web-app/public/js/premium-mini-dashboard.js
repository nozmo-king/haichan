/**
 * HAICHAN PREMIUM MINI DASHBOARD
 * Elegant floating mining dashboard for board traversal
 * Appears automatically when browsing boards/threads with sophisticated animations
 */

class PremiumMiniDashboard {
    constructor() {
        this.element = null;
        this.isVisible = false;
        this.isCollapsed = false;
        this.currentStats = {
            hashrate: 0,
            totalHashes: 0,
            proofs: 0,
            points: 0,
            efficiency: 0,
            activeSessions: 0
        };
        
        this.position = { x: 20, y: 20 }; // Default position
        this.isDragging = false;
        this.dragOffset = { x: 0, y: 0 };
        
        this.createDashboard();
        this.setupEventListeners();
        this.startAutoShow();
        
        console.log('✨ Premium Mini Dashboard initialized');
    }
    
    createDashboard() {
        this.element = document.createElement('div');
        this.element.id = 'premium-mini-dashboard';
        this.element.className = 'premium-mini-dashboard';
        
        this.element.innerHTML = `
            <div class="dashboard-header" id="dashboard-header">
                <div class="dashboard-title">
                    <span class="title-icon">⛏️</span>
                    <span class="title-text">MINING</span>
                    <div class="status-indicator" id="mining-status-dot"></div>
                </div>
                <div class="dashboard-controls">
                    <button class="control-btn collapse-btn" id="collapse-btn" title="Collapse">📊</button>
                    <button class="control-btn close-btn" id="close-btn" title="Hide">✕</button>
                </div>
            </div>
            
            <div class="dashboard-content" id="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="stat-hashrate">0</div>
                        <div class="stat-label">H/s</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="stat-proofs">0</div>
                        <div class="stat-label">Proofs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="stat-points">0</div>
                        <div class="stat-label">Points</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="stat-efficiency">0%</div>
                        <div class="stat-label">Efficiency</div>
                    </div>
                </div>
                
                <div class="hashrate-chart" id="hashrate-chart">
                    <div class="chart-bars" id="chart-bars"></div>
                </div>
                
                <div class="mining-activity" id="mining-activity">
                    <div class="activity-header">Recent Activity</div>
                    <div class="activity-list" id="activity-list">
                        <div class="activity-item">
                            <span class="activity-icon">💎</span>
                            <span class="activity-text">Elite mining ready</span>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-footer">
                    <div class="intensity-selector">
                        <select id="intensity-select">
                            <option value="CASUAL">Casual</option>
                            <option value="ACTIVE">Active</option>
                            <option value="ELITE" selected>Elite</option>
                            <option value="LEGENDARY">Legendary</option>
                        </select>
                    </div>
                    <div class="mining-toggle">
                        <button class="toggle-btn active" id="mining-toggle">⚡ ACTIVE</button>
                    </div>
                </div>
            </div>
        `;
        
        this.addStyles();
        document.body.appendChild(this.element);
        
        // Initially hidden
        this.element.style.display = 'none';
        
        // Setup dragging
        this.setupDragging();
        
        // Initialize chart
        this.initializeChart();
    }
    
    addStyles() {
        if (document.getElementById('premium-mini-dashboard-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'premium-mini-dashboard-styles';
        styles.textContent = `
            .premium-mini-dashboard {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 280px;
                background: linear-gradient(135deg, rgba(0, 20, 40, 0.95), rgba(0, 40, 80, 0.95));
                border: 1px solid rgba(0, 169, 165, 0.3);
                border-radius: 16px;
                backdrop-filter: blur(20px);
                box-shadow: 
                    0 8px 32px rgba(0, 0, 0, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                z-index: 9999;
                font-family: 'Berkeley Mono', monospace;
                font-size: 12px;
                color: #e8f4f8;
                overflow: hidden;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: default;
                user-select: none;
            }
            
            .premium-mini-dashboard.collapsed {
                height: 48px;
            }
            
            .premium-mini-dashboard.collapsed .dashboard-content {
                display: none;
            }
            
            .premium-mini-dashboard.show {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            
            .premium-mini-dashboard.hide {
                opacity: 0;
                transform: translateX(100%) scale(0.9);
            }
            
            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                background: linear-gradient(90deg, rgba(0, 169, 165, 0.2), rgba(144, 194, 231, 0.2));
                border-bottom: 1px solid rgba(0, 169, 165, 0.2);
                cursor: move;
            }
            
            .dashboard-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                font-size: 13px;
            }
            
            .title-icon {
                font-size: 16px;
                animation: mining-pulse 2s ease-in-out infinite;
            }
            
            .status-indicator {
                width: 8px;
                height: 8px;
                background: #00ffa5;
                border-radius: 50%;
                box-shadow: 0 0 8px #00ffa5;
                animation: status-pulse 2s ease-in-out infinite;
            }
            
            .dashboard-controls {
                display: flex;
                gap: 6px;
            }
            
            .control-btn {
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 6px;
                color: #e8f4f8;
                padding: 4px 8px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .control-btn:hover {
                background: rgba(0, 169, 165, 0.3);
                border-color: rgba(0, 169, 165, 0.5);
                transform: scale(1.05);
            }
            
            .dashboard-content {
                padding: 16px;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin-bottom: 16px;
            }
            
            .stat-item {
                text-align: center;
                padding: 8px;
                background: rgba(0, 169, 165, 0.1);
                border: 1px solid rgba(0, 169, 165, 0.2);
                border-radius: 8px;
                transition: all 0.3s ease;
            }
            
            .stat-item:hover {
                background: rgba(0, 169, 165, 0.2);
                border-color: rgba(0, 169, 165, 0.4);
                transform: translateY(-2px);
            }
            
            .stat-value {
                font-size: 16px;
                font-weight: 700;
                color: #00ffa5;
                margin-bottom: 4px;
                font-family: monospace;
            }
            
            .stat-label {
                font-size: 10px;
                color: rgba(232, 244, 248, 0.7);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .hashrate-chart {
                height: 60px;
                margin-bottom: 16px;
                background: rgba(0, 0, 0, 0.2);
                border: 1px solid rgba(0, 169, 165, 0.2);
                border-radius: 8px;
                padding: 8px;
                overflow: hidden;
            }
            
            .chart-bars {
                display: flex;
                align-items: flex-end;
                height: 100%;
                gap: 2px;
            }
            
            .chart-bar {
                flex: 1;
                background: linear-gradient(to top, rgba(0, 169, 165, 0.8), rgba(144, 194, 231, 0.8));
                border-radius: 1px;
                min-height: 2px;
                transition: height 0.5s ease;
                opacity: 0.7;
            }
            
            .chart-bar:last-child {
                opacity: 1;
                box-shadow: 0 0 6px rgba(0, 169, 165, 0.6);
            }
            
            .mining-activity {
                margin-bottom: 16px;
            }
            
            .activity-header {
                font-size: 11px;
                color: rgba(232, 244, 248, 0.8);
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .activity-list {
                max-height: 80px;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: rgba(0, 169, 165, 0.5) transparent;
            }
            
            .activity-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 8px;
                margin-bottom: 4px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 6px;
                font-size: 11px;
                opacity: 0;
                transform: translateX(-10px);
                animation: activity-fade-in 0.5s ease-out forwards;
            }
            
            .activity-icon {
                font-size: 14px;
                flex-shrink: 0;
            }
            
            .activity-text {
                flex: 1;
                color: rgba(232, 244, 248, 0.9);
            }
            
            .dashboard-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
            }
            
            .intensity-selector select {
                background: rgba(0, 0, 0, 0.3);
                border: 1px solid rgba(0, 169, 165, 0.3);
                color: #e8f4f8;
                padding: 6px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .intensity-selector select:hover {
                border-color: rgba(0, 169, 165, 0.5);
                background: rgba(0, 0, 0, 0.5);
            }
            
            .toggle-btn {
                background: linear-gradient(135deg, #00ffa5, #00d4aa);
                border: none;
                color: #003d47;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 11px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px rgba(0, 255, 165, 0.3);
            }
            
            .toggle-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 255, 165, 0.5);
            }
            
            .toggle-btn.inactive {
                background: rgba(255, 255, 255, 0.2);
                color: rgba(232, 244, 248, 0.7);
                box-shadow: none;
            }
            
            @keyframes mining-pulse {
                0%, 100% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.1); opacity: 0.8; }
            }
            
            @keyframes status-pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            
            @keyframes activity-fade-in {
                0% {
                    opacity: 0;
                    transform: translateX(-10px);
                }
                100% {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .premium-mini-dashboard {
                    width: 260px;
                    top: 10px;
                    right: 10px;
                }
                
                .stats-grid {
                    grid-template-columns: 1fr;
                    gap: 8px;
                }
                
                .stat-item {
                    padding: 6px;
                }
                
                .hashrate-chart {
                    height: 40px;
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    setupEventListeners() {
        // Collapse/expand toggle
        const collapseBtn = this.element.querySelector('#collapse-btn');
        collapseBtn.addEventListener('click', () => this.toggleCollapse());
        
        // Close button
        const closeBtn = this.element.querySelector('#close-btn');
        closeBtn.addEventListener('click', () => this.hide());
        
        // Mining toggle
        const miningToggle = this.element.querySelector('#mining-toggle');
        miningToggle.addEventListener('click', () => this.toggleMining());
        
        // Intensity selector
        const intensitySelect = this.element.querySelector('#intensity-select');
        intensitySelect.addEventListener('change', (e) => this.changeIntensity(e.target.value));
        
        // Integration with global mining system
        this.setupMiningIntegration();
    }
    
    setupMiningIntegration() {
        // Listen for mining events
        document.addEventListener('mouseoverMinerReady', (e) => {
            if (e.detail && e.detail.getStats) {
                this.miner = e.detail;
                this.startStatsTracking();
            }
        });
        
        // Fallback: check for existing miner
        if (window.mouseoverMiner) {
            this.miner = window.mouseoverMiner;
            this.startStatsTracking();
        }
    }
    
    setupDragging() {
        const header = this.element.querySelector('.dashboard-header');
        
        header.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            const rect = this.element.getBoundingClientRect();
            this.dragOffset = {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
            document.addEventListener('mousemove', this.handleDrag);
            document.addEventListener('mouseup', this.handleDragEnd);
            e.preventDefault();
        });
    }
    
    handleDrag = (e) => {
        if (!this.isDragging) return;
        
        const newX = e.clientX - this.dragOffset.x;
        const newY = e.clientY - this.dragOffset.y;
        
        // Keep within viewport bounds
        const maxX = window.innerWidth - this.element.offsetWidth;
        const maxY = window.innerHeight - this.element.offsetHeight;
        
        this.position.x = Math.max(0, Math.min(newX, maxX));
        this.position.y = Math.max(0, Math.min(newY, maxY));
        
        this.element.style.left = `${this.position.x}px`;
        this.element.style.top = `${this.position.y}px`;
        this.element.style.right = 'auto';
    }
    
    handleDragEnd = () => {
        this.isDragging = false;
        document.removeEventListener('mousemove', this.handleDrag);
        document.removeEventListener('mouseup', this.handleDragEnd);
        
        // Save position to localStorage
        localStorage.setItem('miniDashboardPosition', JSON.stringify(this.position));
    }
    
    initializeChart() {
        const chartBars = this.element.querySelector('#chart-bars');
        
        // Create 20 bars for hashrate history
        for (let i = 0; i < 20; i++) {
            const bar = document.createElement('div');
            bar.className = 'chart-bar';
            bar.style.height = '2px';
            chartBars.appendChild(bar);
        }
    }
    
    startAutoShow() {
        // Auto-show when user is actively browsing boards/threads
        let inactivityTimer;
        let lastActivity = Date.now();
        
        const trackActivity = () => {
            lastActivity = Date.now();
            clearTimeout(inactivityTimer);
            
            // Show dashboard when browsing
            if (this.shouldAutoShow()) {
                this.show();
            }
            
            // Hide after inactivity
            inactivityTimer = setTimeout(() => {
                if (!this.isDragging) {
                    this.hide();
                }
            }, 30000); // Hide after 30 seconds of inactivity
        };
        
        // Track various user interactions
        document.addEventListener('mousemove', trackActivity);
        document.addEventListener('click', trackActivity);
        document.addEventListener('scroll', trackActivity);
        document.addEventListener('keydown', trackActivity);
        
        // Check URL changes for board navigation
        let currentPath = window.location.pathname;
        const checkPathChange = () => {
            if (window.location.pathname !== currentPath) {
                currentPath = window.location.pathname;
                if (this.shouldAutoShow()) {
                    this.show();
                    trackActivity();
                }
            }
        };
        setInterval(checkPathChange, 1000);
    }
    
    shouldAutoShow() {
        const path = window.location.pathname;
        
        // Show on boards and threads
        return (
            path.match(/^\/[a-z]+\/?$/) || // Board pages like /gen/
            path.match(/^\/[a-z]+\/\d+\/?$/) || // Thread pages like /gen/123
            path.includes('/catalog') ||
            path.includes('/thread/')
        );
    }
    
    startStatsTracking() {
        if (!this.miner) return;
        
        setInterval(() => {
            if (this.miner && this.miner.getStats) {
                const stats = this.miner.getStats();
                this.updateStats(stats);
            }
        }, 2000);
    }
    
    show() {
        if (this.isVisible) return;
        
        this.isVisible = true;
        this.element.style.display = 'block';
        
        // Load saved position
        const savedPosition = localStorage.getItem('miniDashboardPosition');
        if (savedPosition) {
            this.position = JSON.parse(savedPosition);
            this.element.style.left = `${this.position.x}px`;
            this.element.style.top = `${this.position.y}px`;
            this.element.style.right = 'auto';
        }
        
        // Animate in
        requestAnimationFrame(() => {
            this.element.classList.add('show');
            this.element.classList.remove('hide');
        });
        
        // Add activity
        this.addActivity('🎯', 'Dashboard activated');
    }
    
    hide() {
        if (!this.isVisible) return;
        
        this.isVisible = false;
        this.element.classList.remove('show');
        this.element.classList.add('hide');
        
        setTimeout(() => {
            if (!this.isVisible) {
                this.element.style.display = 'none';
            }
        }, 400);
    }
    
    toggleCollapse() {
        this.isCollapsed = !this.isCollapsed;
        this.element.classList.toggle('collapsed', this.isCollapsed);
        
        const collapseBtn = this.element.querySelector('#collapse-btn');
        collapseBtn.textContent = this.isCollapsed ? '📈' : '📊';
        
        // Save collapsed state
        localStorage.setItem('miniDashboardCollapsed', this.isCollapsed);
    }
    
    toggleMining() {
        const toggleBtn = this.element.querySelector('#mining-toggle');
        const statusDot = this.element.querySelector('#mining-status-dot');
        
        if (this.miner) {
            this.miner.toggleMining();
            const isActive = this.miner.isActive;
            
            toggleBtn.textContent = isActive ? '⚡ ACTIVE' : '⏸️ PAUSED';
            toggleBtn.classList.toggle('inactive', !isActive);
            
            statusDot.style.background = isActive ? '#00ffa5' : '#ff6b6b';
            statusDot.style.boxShadow = isActive ? '0 0 8px #00ffa5' : '0 0 8px #ff6b6b';
            
            this.addActivity(isActive ? '⚡' : '⏸️', isActive ? 'Mining resumed' : 'Mining paused');
        }
    }
    
    changeIntensity(intensity) {
        if (this.miner && this.miner.intensity !== intensity) {
            this.miner.intensity = intensity;
            this.addActivity('⚙️', `Intensity changed to ${intensity}`);
            
            // Update visual style based on intensity
            this.updateIntensityVisuals(intensity);
        }
    }
    
    updateIntensityVisuals(intensity) {
        const intensityColors = {
            'CASUAL': 'rgba(144, 194, 231, 0.3)',
            'ACTIVE': 'rgba(0, 169, 165, 0.3)',
            'ELITE': 'rgba(0, 255, 165, 0.3)',
            'LEGENDARY': 'rgba(255, 215, 0, 0.3)'
        };
        
        const color = intensityColors[intensity] || intensityColors['ELITE'];
        this.element.style.borderColor = color.replace('0.3', '0.5');
        
        const header = this.element.querySelector('.dashboard-header');
        header.style.background = `linear-gradient(90deg, ${color}, ${color.replace('0.3', '0.2')})`;
    }
    
    updateStats(stats) {
        if (!stats) return;
        
        this.currentStats = { ...stats };
        
        // Update stat displays
        this.element.querySelector('#stat-hashrate').textContent = stats.hashrate?.toLocaleString() || '0';
        this.element.querySelector('#stat-proofs').textContent = stats.proofs || '0';
        this.element.querySelector('#stat-points').textContent = (stats.points || 0).toFixed(1);
        this.element.querySelector('#stat-efficiency').textContent = `${Math.round(stats.efficiency || 0)}%`;
        
        // Update chart
        this.updateChart(stats.hashrate || 0);
    }
    
    updateChart(currentHashrate) {
        const bars = this.element.querySelectorAll('.chart-bar');
        
        // Shift existing values and add new one
        for (let i = 0; i < bars.length - 1; i++) {
            const nextHeight = bars[i + 1].style.height;
            bars[i].style.height = nextHeight;
        }
        
        // Add new value to last bar
        const maxHeight = 100; // Percentage
        const normalizedHeight = Math.min((currentHashrate / 10000) * maxHeight, maxHeight);
        bars[bars.length - 1].style.height = `${Math.max(normalizedHeight, 2)}%`;
    }
    
    addActivity(icon, text) {
        const activityList = this.element.querySelector('#activity-list');
        
        const item = document.createElement('div');
        item.className = 'activity-item';
        item.innerHTML = `
            <span class="activity-icon">${icon}</span>
            <span class="activity-text">${text}</span>
        `;
        
        // Add to top
        activityList.insertBefore(item, activityList.firstChild);
        
        // Keep only latest 5 items
        const items = activityList.querySelectorAll('.activity-item');
        if (items.length > 5) {
            items[items.length - 1].remove();
        }
    }
    
    // Public API
    setStats(stats) {
        this.updateStats(stats);
    }
    
    isShowing() {
        return this.isVisible;
    }
    
    forceShow() {
        this.show();
    }
    
    forceHide() {
        this.hide();
    }
}

// Initialize and make globally available
window.PremiumMiniDashboard = PremiumMiniDashboard;

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.premiumMiniDashboard = new PremiumMiniDashboard();
    });
} else {
    window.premiumMiniDashboard = new PremiumMiniDashboard();
}

console.log('✨ Premium Mini Dashboard loaded - Auto-shows during board traversal');