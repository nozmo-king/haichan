/**
 * Quick Navigation System for Haichan
 * Provides rapid thread access and navigation shortcuts
 */

class QuickNavigation {
    constructor() {
        this.isActive = false;
        this.searchResults = [];
        this.currentSelection = -1;
        this.init();
    }

    init() {
        this.createQuickOpenModal();
        this.setupKeyboardShortcuts();
        this.setupThreadJumping();
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for quick open
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.showQuickOpen();
            }
            
            // Ctrl/Cmd + G for go to thread by ID
            if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
                e.preventDefault();
                this.showGoToThread();
            }
            
            // Esc to close modal
            if (e.key === 'Escape' && this.isActive) {
                this.hideModal();
            }
            
            // Arrow keys for navigation in modal
            if (this.isActive) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.navigateDown();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.navigateUp();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    this.selectCurrentResult();
                }
            }
        });
    }

    createQuickOpenModal() {
        const modal = document.createElement('div');
        modal.id = 'quick-nav-modal';
        modal.className = 'quick-nav-modal hidden';
        modal.innerHTML = `
            <div class="quick-nav-overlay"></div>
            <div class="quick-nav-content">
                <div class="quick-nav-header">
                    <input type="text" id="quick-nav-input" placeholder="Search threads, boards, or enter thread ID..." autocomplete="off">
                    <div class="quick-nav-shortcuts">
                        <span class="shortcut-hint">Ctrl+K: Quick Open</span>
                        <span class="shortcut-hint">Ctrl+G: Go to Thread</span>
                    </div>
                </div>
                <div class="quick-nav-results" id="quick-nav-results">
                    <div class="quick-nav-help">
                        <div class="help-section">
                            <h4>Quick Commands:</h4>
                            <ul>
                                <li><code>/b [board]</code> - Go to board (e.g., /b g)</li>
                                <li><code>/t [id]</code> - Go to thread by ID</li>
                                <li><code>/r</code> - Go to random thread</li>
                                <li><code>/recent</code> - Show recent threads</li>
                                <li><code>/active</code> - Show active threads</li>
                            </ul>
                        </div>
                        <div class="help-section">
                            <h4>Navigation:</h4>
                            <ul>
                                <li>↑↓ arrows to navigate</li>
                                <li>Enter to select</li>
                                <li>Esc to close</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Setup input handler
        const input = document.getElementById('quick-nav-input');
        input.addEventListener('input', (e) => this.handleSearch(e.target.value));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                this.handleTabCompletion();
            }
        });
        
        // Click outside to close
        modal.querySelector('.quick-nav-overlay').addEventListener('click', () => {
            this.hideModal();
        });
    }

    showQuickOpen() {
        this.isActive = true;
        const modal = document.getElementById('quick-nav-modal');
        const input = document.getElementById('quick-nav-input');
        
        modal.classList.remove('hidden');
        input.focus();
        input.value = '';
        this.showHelp();
        this.currentSelection = -1;
    }

    showGoToThread() {
        this.showQuickOpen();
        const input = document.getElementById('quick-nav-input');
        input.value = '/t ';
        input.setSelectionRange(3, 3);
    }

    hideModal() {
        this.isActive = false;
        const modal = document.getElementById('quick-nav-modal');
        modal.classList.add('hidden');
        this.searchResults = [];
        this.currentSelection = -1;
    }

    async handleSearch(query) {
        if (!query.trim()) {
            this.showHelp();
            return;
        }

        // Handle quick commands
        if (query.startsWith('/')) {
            this.handleQuickCommand(query);
            return;
        }

        // Search threads and boards
        try {
            const response = await fetch('/api/search/quick', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ query: query.trim() })
            });

            if (response.ok) {
                const data = await response.json();
                this.displayResults(data.results || []);
            }
        } catch (error) {
            console.error('Search error:', error);
            this.displayError('Search failed. Please try again.');
        }
    }

    handleQuickCommand(command) {
        const results = document.getElementById('quick-nav-results');
        const parts = command.split(' ');
        const cmd = parts[0];
        const arg = parts.slice(1).join(' ');

        switch (cmd) {
            case '/b':
                if (arg) {
                    results.innerHTML = `<div class="quick-result selected" data-url="/boards/${arg}">
                        📋 Go to /${arg}/ board
                    </div>`;
                } else {
                    results.innerHTML = `<div class="quick-help">Type board name after /b (e.g., /b g)</div>`;
                }
                break;

            case '/t':
                if (arg && /^\d+$/.test(arg)) {
                    results.innerHTML = `<div class="quick-result selected" data-thread-id="${arg}">
                        🧵 Go to thread #${arg}
                    </div>`;
                } else {
                    results.innerHTML = `<div class="quick-help">Type thread ID after /t (e.g., /t 123)</div>`;
                }
                break;

            case '/r':
                results.innerHTML = `<div class="quick-result selected" data-action="random">
                    🎲 Go to random thread
                </div>`;
                break;

            case '/recent':
                this.loadRecentThreads();
                break;

            case '/active':
                this.loadActiveThreads();
                break;

            default:
                results.innerHTML = `<div class="quick-help">Unknown command: ${cmd}</div>`;
        }

        this.currentSelection = 0;
    }

    async loadRecentThreads() {
        try {
            const response = await fetch('/api/threads/recent');
            if (response.ok) {
                const data = await response.json();
                this.displayThreadResults(data.threads, '🕒 Recent Threads');
            }
        } catch (error) {
            this.displayError('Failed to load recent threads');
        }
    }

    async loadActiveThreads() {
        try {
            const response = await fetch('/api/threads/active');
            if (response.ok) {
                const data = await response.json();
                this.displayThreadResults(data.threads, '🔥 Active Threads');
            }
        } catch (error) {
            this.displayError('Failed to load active threads');
        }
    }

    displayThreadResults(threads, title) {
        const results = document.getElementById('quick-nav-results');
        let html = `<div class="results-title">${title}</div>`;
        
        threads.forEach((thread, index) => {
            html += `
                <div class="quick-result ${index === 0 ? 'selected' : ''}" data-thread-id="${thread.id}">
                    <div class="thread-title">🧵 ${thread.subject || 'No Subject'}</div>
                    <div class="thread-info">/${thread.board}/ • ${thread.reply_count} replies • ${this.formatDate(thread.updated_at)}</div>
                </div>
            `;
        });
        
        results.innerHTML = html;
        this.currentSelection = 0;
    }

    displayResults(results) {
        const container = document.getElementById('quick-nav-results');
        
        if (results.length === 0) {
            container.innerHTML = '<div class="no-results">No results found</div>';
            return;
        }

        let html = '';
        results.forEach((result, index) => {
            const selected = index === 0 ? 'selected' : '';
            
            if (result.type === 'thread') {
                html += `
                    <div class="quick-result ${selected}" data-thread-id="${result.id}">
                        <div class="thread-title">🧵 ${result.subject || 'No Subject'}</div>
                        <div class="thread-info">/${result.board}/ • ${result.reply_count} replies</div>
                    </div>
                `;
            } else if (result.type === 'board') {
                html += `
                    <div class="quick-result ${selected}" data-url="/boards/${result.name}">
                        <div class="board-title">📋 /${result.code}/ - ${result.name}</div>
                        <div class="board-info">${result.description}</div>
                    </div>
                `;
            }
        });

        container.innerHTML = html;
        this.currentSelection = 0;
    }

    showHelp() {
        const results = document.getElementById('quick-nav-results');
        results.innerHTML = results.querySelector('.quick-nav-help').outerHTML;
    }

    displayError(message) {
        const results = document.getElementById('quick-nav-results');
        results.innerHTML = `<div class="quick-error">❌ ${message}</div>`;
    }

    navigateDown() {
        const results = document.querySelectorAll('.quick-result');
        if (results.length === 0) return;

        if (this.currentSelection < results.length - 1) {
            results[this.currentSelection]?.classList.remove('selected');
            this.currentSelection++;
            results[this.currentSelection].classList.add('selected');
            results[this.currentSelection].scrollIntoView({ block: 'nearest' });
        }
    }

    navigateUp() {
        const results = document.querySelectorAll('.quick-result');
        if (results.length === 0) return;

        if (this.currentSelection > 0) {
            results[this.currentSelection]?.classList.remove('selected');
            this.currentSelection--;
            results[this.currentSelection].classList.add('selected');
            results[this.currentSelection].scrollIntoView({ block: 'nearest' });
        }
    }

    selectCurrentResult() {
        const selected = document.querySelector('.quick-result.selected');
        if (!selected) return;

        if (selected.dataset.threadId) {
            this.goToThread(selected.dataset.threadId);
        } else if (selected.dataset.url) {
            window.location.href = selected.dataset.url;
        } else if (selected.dataset.action === 'random') {
            this.goToRandomThread();
        }

        this.hideModal();
    }

    async goToThread(threadId) {
        try {
            const response = await fetch(`/api/threads/${threadId}/url`);
            if (response.ok) {
                const data = await response.json();
                window.location.href = data.url;
            } else {
                alert('Thread not found');
            }
        } catch (error) {
            console.error('Error navigating to thread:', error);
            alert('Navigation failed');
        }
    }

    async goToRandomThread() {
        try {
            const response = await fetch('/api/threads/random');
            if (response.ok) {
                const data = await response.json();
                window.location.href = data.url;
            }
        } catch (error) {
            console.error('Error getting random thread:', error);
        }
    }

    setupThreadJumping() {
        // Add thread jump buttons to thread pages
        if (window.location.pathname.includes('/thread/')) {
            this.addThreadNavigationButtons();
        }
    }

    addThreadNavigationButtons() {
        const threadHeader = document.querySelector('.thread-header, .board-header');
        if (!threadHeader) return;

        const navButtons = document.createElement('div');
        navButtons.className = 'thread-nav-buttons';
        navButtons.innerHTML = `
            <button class="nav-btn" onclick="quickNav.goToPreviousThread()" title="Previous Thread (Alt+←)">← Prev</button>
            <button class="nav-btn" onclick="quickNav.goToRandomThread()" title="Random Thread (Alt+R)">🎲 Random</button>
            <button class="nav-btn" onclick="quickNav.goToNextThread()" title="Next Thread (Alt+→)">Next →</button>
        `;

        threadHeader.appendChild(navButtons);

        // Add keyboard shortcuts for thread navigation
        document.addEventListener('keydown', (e) => {
            if (e.altKey) {
                switch (e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        this.goToPreviousThread();
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        this.goToNextThread();
                        break;
                    case 'r':
                        e.preventDefault();
                        this.goToRandomThread();
                        break;
                }
            }
        });
    }

    async goToPreviousThread() {
        const currentThreadId = this.getCurrentThreadId();
        if (!currentThreadId) return;

        try {
            const response = await fetch(`/api/threads/${currentThreadId}/previous`);
            if (response.ok) {
                const data = await response.json();
                window.location.href = data.url;
            }
        } catch (error) {
            console.error('Error navigating to previous thread:', error);
        }
    }

    async goToNextThread() {
        const currentThreadId = this.getCurrentThreadId();
        if (!currentThreadId) return;

        try {
            const response = await fetch(`/api/threads/${currentThreadId}/next`);
            if (response.ok) {
                const data = await response.json();
                window.location.href = data.url;
            }
        } catch (error) {
            console.error('Error navigating to next thread:', error);
        }
    }

    getCurrentThreadId() {
        const match = window.location.pathname.match(/\/thread\/(\d+)/);
        return match ? match[1] : null;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        
        if (minutes < 60) return `${minutes}m ago`;
        
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}h ago`;
        
        const days = Math.floor(hours / 24);
        return `${days}d ago`;
    }

    handleTabCompletion() {
        const input = document.getElementById('quick-nav-input');
        const value = input.value;
        
        // Auto-complete common commands
        if (value === '/') {
            input.value = '/b ';
            input.setSelectionRange(3, 3);
        } else if (value === '/b') {
            input.value = '/b ';
            input.setSelectionRange(3, 3);
        } else if (value === '/t') {
            input.value = '/t ';
            input.setSelectionRange(3, 3);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.quickNav = new QuickNavigation();
});