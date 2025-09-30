import './bootstrap';
import './auth';
import './mining-brain';

// Theme is locked to classic - no theme switching allowed
document.addEventListener('DOMContentLoaded', () => {
    // Force classic theme permanently
    document.documentElement.setAttribute('data-theme', 'classic');
    document.body.className = document.body.className.replace(/theme-\w+/g, '');
    document.body.classList.add('theme-classic');
    
    // Remove any theme-related localStorage
    if (localStorage.getItem('haichan-theme')) {
        localStorage.removeItem('haichan-theme');
    }
    
    console.log('🔒 Theme permanently locked to classic');
});
