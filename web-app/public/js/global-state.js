/* HAICHAN GLOBAL STATE MANAGEMENT */
/* Persistent component state across all pages */

class HaichanGlobalState {
    constructor() {
        this.state = {
            // Mining state
            mining: {
                isActive: false,
                hashrate: 0,
                totalHashes: 0,
                currentChallenge: null,
                stats: {
                    threadsCreated: 0,
                    repliesPosted: 0,
                    totalTime: 0
                }
            },
            
            // UI state
            ui: {
                anonymousMode: false,
                chatVisible: false,
                miniDashVisible: true,
                toolbarVisible: true,
                theme: 'default'
            },
            
            // User state
            user: {
                pubkey: null,
                isLoggedIn: false,
                preferences: {}
            },
            
            // Chat state
            chat: {
                messages: [],
                unreadCount: 0,
                isConnected: false
            }
        };
        
        this.listeners = new Map();
        this.persistentKeys = ['ui', 'user.preferences', 'mining.stats'];
        
        this.init();
    }
    
    init() {
        // Load persistent state from localStorage
        this.loadPersistedState();
        
        // Set up event bus
        this.eventBus = new EventTarget();
        
        // Set up periodic state persistence
        setInterval(() => this.persistState(), 5000);
        
        // Set up page unload persistence
        window.addEventListener('beforeunload', () => this.persistState());
        
        // Check sessionStorage for anonymous mode activation
        if (sessionStorage.getItem('anonymous_mode') === 'true') {
            this.state.ui.anonymousMode = true;
            this.applyAnonymousMode();
            console.log('👻 Anonymous mode restored from session');
        } else if (this.state.ui.anonymousMode) {
            // Initialize from persisted state
            this.applyAnonymousMode();
        }
        
        console.log('🌐 Global state manager initialized');
    }
    
    // State management
    setState(path, value) {
        const keys = path.split('.');
        let current = this.state;
        
        // Navigate to parent
        for (let i = 0; i < keys.length - 1; i++) {
            if (!current[keys[i]]) current[keys[i]] = {};
            current = current[keys[i]];
        }
        
        // Set value
        const lastKey = keys[keys.length - 1];
        const oldValue = current[lastKey];
        current[lastKey] = value;
        
        // Emit change event
        this.emit(`state:${path}`, { oldValue, newValue: value });
        this.emit('state:change', { path, oldValue, newValue: value });
        
        return this;
    }
    
    getState(path = null) {
        if (!path) return this.state;
        
        const keys = path.split('.');
        let current = this.state;
        
        for (const key of keys) {
            if (current[key] === undefined) return undefined;
            current = current[key];
        }
        
        return current;
    }
    
    // Event system
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
        return () => this.off(event, callback);
    }
    
    off(event, callback) {
        const callbacks = this.listeners.get(event);
        if (callbacks) {
            const index = callbacks.indexOf(callback);
            if (index > -1) callbacks.splice(index, 1);
        }
    }
    
    emit(event, data = null) {
        const callbacks = this.listeners.get(event);
        if (callbacks) {
            callbacks.forEach(callback => callback(data));
        }
    }
    
    // Persistence
    loadPersistedState() {
        try {
            const saved = localStorage.getItem('haichan:globalState');
            if (saved) {
                const parsed = JSON.parse(saved);
                this.mergePersistentState(parsed);
            }
        } catch (error) {
            console.warn('Failed to load persisted state:', error);
        }
    }
    
    persistState() {
        try {
            const toPersist = {};
            
            this.persistentKeys.forEach(key => {
                const value = this.getState(key);
                if (value !== undefined) {
                    this.setNestedValue(toPersist, key, value);
                }
            });
            
            localStorage.setItem('haichan:globalState', JSON.stringify(toPersist));
        } catch (error) {
            console.warn('Failed to persist state:', error);
        }
    }
    
    setNestedValue(obj, path, value) {
        const keys = path.split('.');
        let current = obj;
        
        for (let i = 0; i < keys.length - 1; i++) {
            if (!current[keys[i]]) current[keys[i]] = {};
            current = current[keys[i]];
        }
        
        current[keys[keys.length - 1]] = value;
    }
    
    mergePersistentState(saved) {
        const merge = (target, source) => {
            for (const key in source) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    if (!target[key]) target[key] = {};
                    merge(target[key], source[key]);
                } else {
                    target[key] = source[key];
                }
            }
        };
        
        merge(this.state, saved);
    }
    
    // Anonymous mode
    toggleAnonymousMode() {
        const newMode = !this.state.ui.anonymousMode;
        this.setState('ui.anonymousMode', newMode);
        
        if (newMode) {
            this.applyAnonymousMode();
        } else {
            this.removeAnonymousMode();
        }
    }
    
    applyAnonymousMode() {
        document.documentElement.classList.add('anonymous-mode');
        // Also ensure body gets the class for compatibility
        document.body.classList.add('anonymous-mode');
        
        // Show visual feedback
        console.log('👻 Anonymous mode activated - All colors inverted');
        
        this.emit('ui:anonymousMode', true);
    }
    
    removeAnonymousMode() {
        document.documentElement.classList.remove('anonymous-mode');
        document.body.classList.remove('anonymous-mode');
        
        console.log('🌈 Anonymous mode deactivated - Normal colors restored');
        
        this.emit('ui:anonymousMode', false);
    }
    
    // Mining helpers
    updateMiningStats(stats) {
        Object.keys(stats).forEach(key => {
            this.setState(`mining.stats.${key}`, stats[key]);
        });
    }
    
    setMiningActive(active) {
        this.setState('mining.isActive', active);
        this.emit('mining:statusChange', active);
    }
    
    // Chat helpers
    addChatMessage(message) {
        const messages = [...this.state.chat.messages, message];
        this.setState('chat.messages', messages);
        
        if (!this.state.ui.chatVisible) {
            this.setState('chat.unreadCount', this.state.chat.unreadCount + 1);
        }
    }
    
    markChatRead() {
        this.setState('chat.unreadCount', 0);
    }
    
    // UI helpers
    toggleChat() {
        const visible = !this.state.ui.chatVisible;
        this.setState('ui.chatVisible', visible);
        
        if (visible) {
            this.markChatRead();
        }
    }
    
    toggleMiniDash() {
        this.setState('ui.miniDashVisible', !this.state.ui.miniDashVisible);
    }
}

// Initialize global state
window.HaichanState = new HaichanGlobalState();

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HaichanGlobalState;
}