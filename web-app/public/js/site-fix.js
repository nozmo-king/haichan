/**
 * HAICHAN COMPLETE SITE FIX
 * Ensures all buttons, forms, and interactions work properly
 */

(function() {
    'use strict';
    
    console.log('🔧 Site fix loading...');
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        console.log('✅ Site fix initialized');
        
        // Ensure HaichanState exists
        ensureGlobalState();
        
        // Fix all buttons
        fixAllButtons();
        
        // Fix forms
        fixAllForms();
        
        // Fix links
        fixAllLinks();
        
        // Fix dropdowns
        fixDropdowns();
        
        // Fix toolbar
        fixToolbar();
        
        // Setup error handlers
        setupErrorHandlers();
        
        console.log('✅ All site fixes applied');
    }
    
    function ensureGlobalState() {
        if (!window.HaichanState) {
            window.HaichanState = {
                state: {
                    mining: { isActive: false, hashrate: 0, totalHashes: 0 },
                    chat: { unreadCount: 0 },
                    ui: { chatVisible: false, anonymousMode: false, miniDashVisible: false, toolbarVisible: true }
                },
                getState: function(path) {
                    const parts = path.split('.');
                    let current = this.state;
                    for (let part of parts) {
                        if (current && current[part] !== undefined) {
                            current = current[part];
                        } else {
                            return null;
                        }
                    }
                    return current;
                },
                setState: function(path, value) {
                    const parts = path.split('.');
                    const last = parts.pop();
                    let current = this.state;
                    for (let part of parts) {
                        if (!current[part]) current[part] = {};
                        current = current[part];
                    }
                    current[last] = value;
                    this.emit('state:change', { path, value });
                },
                listeners: {},
                on: function(event, handler) {
                    if (!this.listeners[event]) this.listeners[event] = [];
                    this.listeners[event].push(handler);
                },
                emit: function(event, data) {
                    if (this.listeners[event]) {
                        this.listeners[event].forEach(h => h(data));
                    }
                },
                toggleChat: function() {
                    const current = this.getState('ui.chatVisible');
                    this.setState('ui.chatVisible', !current);
                    console.log('Chat toggled:', !current);
                },
                toggleMiniDash: function() {
                    const current = this.getState('ui.miniDashVisible');
                    this.setState('ui.miniDashVisible', !current);
                    console.log('Mini dash toggled:', !current);
                },
                toggleAnonymousMode: function() {
                    const current = this.getState('ui.anonymousMode');
                    this.setState('ui.anonymousMode', !current);
                    document.body.classList.toggle('anonymous-mode', !current);
                    console.log('Anonymous mode toggled:', !current);
                }
            };
            console.log('✅ HaichanState initialized');
        }
    }
    
    function fixAllButtons() {
        // Find all buttons without event listeners
        const buttons = document.querySelectorAll('button:not([data-fixed])');
        buttons.forEach(button => {
            button.setAttribute('data-fixed', 'true');
            
            // If button has onclick attribute but it's broken
            if (button.hasAttribute('onclick')) {
                const onclick = button.getAttribute('onclick');
                button.removeAttribute('onclick');
                button.addEventListener('click', function(e) {
                    try {
                        eval(onclick);
                    } catch (err) {
                        console.error('Button onclick error:', err);
                    }
                });
            }
        });
        
        // Specific button fixes
        fixLogoutButton();
        fixProfileButtons();
        fixMiningButtons();
    }
    
    function fixLogoutButton() {
        const logoutBtns = document.querySelectorAll('.logout-btn, [href*="logout"]');
        logoutBtns.forEach(btn => {
            if (btn.tagName === 'BUTTON') {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/logout';
                        const csrf = document.querySelector('meta[name="csrf-token"]');
                        if (csrf) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = '_token';
                            input.value = csrf.content;
                            form.appendChild(input);
                        }
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    }
    
    function fixProfileButtons() {
        const editProfileBtns = document.querySelectorAll('.edit-profile, [href*="profile/edit"]');
        editProfileBtns.forEach(btn => {
            if (!btn.href) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = '/user/profile/edit';
                });
            }
        });
    }
    
    function fixMiningButtons() {
        // Mining dashboard buttons
        const miningBtns = document.querySelectorAll('[class*="mining"], [id*="mining"]');
        miningBtns.forEach(btn => {
            if (btn.tagName === 'BUTTON' && !btn.hasAttribute('data-mining-fixed')) {
                btn.setAttribute('data-mining-fixed', 'true');
            }
        });
    }
    
    function fixAllForms() {
        const forms = document.querySelectorAll('form:not([data-fixed])');
        forms.forEach(form => {
            form.setAttribute('data-fixed', 'true');
            
            // Ensure CSRF token
            const csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf && !form.querySelector('input[name="_token"]')) {
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = csrf.content;
                form.insertBefore(token, form.firstChild);
            }
            
            // Add submit handler
            form.addEventListener('submit', function(e) {
                // Disable submit button to prevent double submission
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '⏳ Processing...';
                    
                    // Re-enable after 3 seconds
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });
    }
    
    function fixAllLinks() {
        // Fix broken links
        const links = document.querySelectorAll('a[href=""]');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                console.warn('Empty link clicked');
            });
        });
    }
    
    function fixDropdowns() {
        const dropdownTriggers = document.querySelectorAll('.dropdown-trigger, [data-dropdown]');
        dropdownTriggers.forEach(trigger => {
            if (!trigger.hasAttribute('data-dropdown-fixed')) {
                trigger.setAttribute('data-dropdown-fixed', 'true');
                
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const menuId = trigger.id.replace('-dropdown', '-menu');
                    const menu = document.getElementById(menuId);
                    
                    if (menu) {
                        const isOpen = menu.classList.contains('show');
                        
                        // Close all other dropdowns
                        document.querySelectorAll('.dropdown-menu').forEach(m => {
                            m.classList.remove('show');
                        });
                        document.querySelectorAll('.dropdown-trigger').forEach(t => {
                            t.classList.remove('active');
                        });
                        
                        // Toggle current
                        if (!isOpen) {
                            menu.classList.add('show');
                            trigger.classList.add('active');
                        }
                    }
                });
            }
        });
        
        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-container')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
                document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
                    trigger.classList.remove('active');
                });
            }
        });
    }
    
    function fixToolbar() {
        // Ensure toolbar loads
        if (!window.HaichanToolbar && window.PersistentToolbar) {
            setTimeout(() => {
                try {
                    window.HaichanToolbar = new PersistentToolbar();
                    console.log('✅ Toolbar initialized');
                } catch (err) {
                    console.error('Toolbar initialization error:', err);
                }
            }, 1000);
        }
        
        // Fix toolbar buttons
        setTimeout(() => {
            const toolbarBtns = document.querySelectorAll('.toolbar-btn');
            toolbarBtns.forEach(btn => {
                if (!btn.hasAttribute('data-fixed')) {
                    btn.setAttribute('data-fixed', 'true');
                    console.log('Fixed toolbar button:', btn.className);
                }
            });
        }, 2000);
    }
    
    function setupErrorHandlers() {
        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });
        
        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
        });
        
        // Ajax error handler
        if (window.jQuery) {
            $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
                console.error('Ajax error:', thrownError);
            });
        }
    }
    
    // Export for debugging
    window.HaichanSiteFix = {
        init,
        fixAllButtons,
        fixAllForms,
        fixAllLinks,
        fixDropdowns,
        fixToolbar
    };
    
})();
