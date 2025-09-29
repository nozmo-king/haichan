/**
 * Haichan Quick Navigation System
 * Provides keyboard shortcuts and quick navigation functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🧭 Quick navigation system loaded');
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Only activate shortcuts if not typing in an input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        switch(e.key.toLowerCase()) {
            case 'h':
                window.location.href = '/';
                break;
            case 'c':
                window.location.href = '/catalog';
                break;
            case 'g':
                window.location.href = '/gen';
                break;
            case 'm':
                // Toggle mining dashboard
                if (window.enhancedMiningDashboard) {
                    window.enhancedMiningDashboard.toggle();
                }
                break;
        }
    });
    
    // Quick board links
    const boardShortcuts = {
        'gen': '/gen',
        'tech': '/tech', 
        'biz': '/biz',
        'film': '/film',
        'x': '/x',
        'lit': '/lit',
        'meta': '/meta',
        'mu': '/mu'
    };
    
    // Add board navigation hints
    if (window.location.pathname === '/') {
        console.log('📋 Board shortcuts: g=gen, t=tech, b=biz, f=film, x=random, l=lit, m=meta, u=music');
    }
});

// Legacy compatibility
window.quickNavigation = {
    navigateToBoard: function(boardCode) {
        window.location.href = '/' + boardCode;
    }
};