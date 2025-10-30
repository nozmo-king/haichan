<div id="new-toolbar" style="position: fixed; bottom: 0; left: 0; width: 100%; background: #708B75; color: #fafa0b; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; font-size: 14px; border-top: 2px solid #fafa0b; z-index: 1000; background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100"><defs><pattern id="hatch" patternUnits="userSpaceOnUse" width="10" height="10"><path d="M-1,1 l2,-2 M0,10 l10,-10 M9,11 l2,-2" stroke="%23fafa0b" stroke-width="1"/></pattern><filter id="noise"><feTurbulence baseFrequency="0.9" numOctaves="4" result="noise" seed="2"/><feColorMatrix in="noise" type="saturate" values="0"/></filter></defs><rect width="100" height="100" fill="%23708B75"/><rect width="100" height="100" fill="url(%23hatch)" opacity="0.1"/><rect width="100" height="100" filter="url(%23noise)" opacity="0.4"/></svg>');">
    <div class="toolbar-left">
        <a href="/profile" class="toolbar-link">👤 {{ session('bitcoin_auth_user')->username ?? 'Guest' }}</a>
        <a href="/profile/edit" class="toolbar-link">✏️ Edit</a>
    </div>
    <div class="toolbar-center">
        <span id="hashrate" class="toolbar-stat">⚡ H/s: 0</span>
        <div class="throttle-control">
            <label for="throttle">Throttle:</label>
            <input type="range" id="throttle" min="1" max="10" value="5" style="width: 100px;">
        </div>
        <span id="mining-updates" class="toolbar-stat"></span>
    </div>
    <div class="toolbar-right">
        <a href="/library" class="toolbar-link">🖼️ Library</a>
        <a href="/shop" class="toolbar-link">🛍️ Shop</a>
        <a href="/leaderboard" class="toolbar-link">🏆 Leaderboard</a>
        <a href="/updates" class="toolbar-link">📢 Updates</a>
    </div>
</div>

<style>
.toolbar-link {
    color: #fafa0b;
    text-decoration: none;
    margin: 0 10px;
    font-weight: bold;
}
.toolbar-link:hover {
    text-decoration: underline;
}
.toolbar-stat {
    margin: 0 10px;
    font-weight: bold;
}
.throttle-control {
    display: inline-flex;
    align-items: center;
    margin: 0 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hashrateEl = document.getElementById('hashrate');
    const throttleEl = document.getElementById('throttle');
    const miningUpdatesEl = document.getElementById('mining-updates');

    // Function to update hashrate
    function updateHashrate(hashrate) {
        if (hashrateEl) {
            hashrateEl.textContent = `⚡ H/s: ${hashrate}`;
        }
    }

    // Function to update mining updates
    function updateMiningUpdates(message) {
        if (miningUpdatesEl) {
            miningUpdatesEl.textContent = message;
        }
    }

    // Listen for hashrate updates from simple-pow.js
    window.addEventListener('hashrate-update', function(e) {
        updateHashrate(e.detail.hashrate);
    });

    // Listen for mining updates from simple-pow.js
    window.addEventListener('mining-update', function(e) {
        updateMiningUpdates(e.detail.message);
    });

    // Throttle control
    if (throttleEl) {
        throttleEl.addEventListener('input', function() {
            const throttleValue = this.value;
            // Dispatch an event to notify simple-pow.js about the throttle change
            window.dispatchEvent(new CustomEvent('throttle-change', { detail: { throttle: throttleValue } }));
        });
    }
});
</script>
