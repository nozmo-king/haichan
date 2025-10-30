/**
 * FORCE PINK/TAN ON GREEN WITH JAVASCRIPT
 */
(function() {
    function forcePink() {
        const selectors = [
            'button', 'button *',
            '.btn', '.btn *',
            'input[type="submit"]', 'input[type="submit"] *',
            'input[type="button"]', 'input[type="button"] *',
            '.toolbar-btn', '.toolbar-btn *',
            '.board-box', '.board-box *',
            'a[style*="background"]', 'a[style*="background"] *'
        ];
        
        selectors.forEach(selector => {
            try {
                document.querySelectorAll(selector).forEach(el => {
                    el.style.setProperty('color', '#D4AC7A', 'important');
                });
            } catch(e) {}
        });
    }
    
    // Run immediately
    forcePink();
    
    // Run after DOM loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forcePink);
    }
    
    // Run after page fully loads
    window.addEventListener('load', forcePink);
    
    // Keep running every 500ms
    setInterval(forcePink, 500);
    
    console.log('🌸 Pink button text enforcer active');
})();
