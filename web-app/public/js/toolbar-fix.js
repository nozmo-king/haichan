/* HAICHAN TOOLBAR - EMERGENCY FIX */
/* This ensures the toolbar works even if other systems fail */

document.addEventListener('DOMContentLoaded', function() {
    // Force toolbar to work even with missing dependencies
    if (!window.HaichanState) {
        window.HaichanState = {
            getState: function(path) {
                const states = {
                    'mining': { isActive: false, hashrate: 0, totalHashes: 0 },
                    'chat': { unreadCount: 0 },
                    'ui': { chatVisible: false, anonymousMode: false, miniDashVisible: false }
                };
                return states[path] || {};
            },
            setState: function() {},
            on: function() {},
            toggleChat: function() {
                console.log('Chat toggled');
            },
            toggleMiniDash: function() {
                console.log('Mini dash toggled');
            },
            toggleAnonymousMode: function() {
                document.body.classList.toggle('anonymous-mode');
                console.log('Anonymous mode toggled');
            }
        };
    }
    
    // Ensure CSS variables exist
    const root = document.documentElement;
    const style = getComputedStyle(root);
    
    if (!style.getPropertyValue('--bg-primary')) {
        root.style.setProperty('--bg-primary', '#F5F5DC');
        root.style.setProperty('--bg-secondary', '#e6d2ab');
        root.style.setProperty('--text-primary', '#3d2f1b');
        root.style.setProperty('--text-secondary', '#666');
        root.style.setProperty('--border-primary', '#b89b6e');
        root.style.setProperty('--border-subtle', '#ddd');
        root.style.setProperty('--accent-primary', '#9AB87A');
        root.style.setProperty('--accent-hover', '#85a366');
        root.style.setProperty('--text-error', '#dc3545');
        root.style.setProperty('--text-mining', '#007bff');
        root.style.setProperty('--space-xs', '4px');
        root.style.setProperty('--space-sm', '8px');
        root.style.setProperty('--space-md', '16px');
    }
    
    console.log('✅ Haichan emergency fixes applied');
});