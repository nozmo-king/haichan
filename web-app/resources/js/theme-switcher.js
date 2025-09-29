/**
 * THEME SWITCHER DISABLED
 * Theme is permanently locked to classic
 */

console.log('🚫 Theme switcher disabled - classic theme only');

// Ensure theme stays classic
document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.setAttribute('data-theme', 'classic');
    document.body.classList.remove('theme-business', 'theme-pleasure');
    document.body.classList.add('theme-classic');
});

// Override any attempts to switch themes
window.switchTheme = function() {
    console.warn('Theme switching is disabled');
};

// Block localStorage theme changes
if (typeof Storage !== 'undefined') {
    const originalSetItem = Storage.prototype.setItem;
    Storage.prototype.setItem = function(key, value) {
        if (key === 'haichan-theme') {
            console.warn('Theme localStorage is blocked');
            return;
        }
        return originalSetItem.call(this, key, value);
    };
}