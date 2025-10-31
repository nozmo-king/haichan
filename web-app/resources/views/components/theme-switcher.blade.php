<!-- Theme switcher disabled - classic only -->
<div class="language-toggle">
    <button id="lang-toggle" class="lang-toggle" type="button" onclick="toggleLanguage()">
        🌐 EN/JP
    </button>
</div>

<script nonce="{{ app('csp_nonce') }}">
// Language toggle functionality only
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
