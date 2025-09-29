<!-- Theme Switcher -->
<div class="theme-switcher">
    <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme">
        <span class="theme-icon">🌓</span>
    </button>
    
    <div class="language-toggle">
        <button id="lang-toggle" class="lang-toggle" type="button" onclick="toggleLanguage()">
            🌐 EN/JP
        </button>
    </div>
</div>

<script>
// Language toggle functionality
function toggleLanguage() {
    const elements = document.querySelectorAll('.fade-text[data-jp]');
    elements.forEach(el => {
        const en = el.getAttribute('data-en') || el.textContent;
        const jp = el.getAttribute('data-jp');
        
        if (el.textContent === en) {
            el.textContent = jp;
        } else {
            el.textContent = en;
        }
    });
}
</script>