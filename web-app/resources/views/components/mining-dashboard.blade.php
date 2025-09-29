<!-- Mining Dashboard -->
<div id="mining-dashboard" class="mining-dashboard">
    <div class="mining-dashboard__header">
        <h3>Mining Activity</h3>
        <button id="dashboard-toggle" class="dashboard-toggle">_</button>
    </div>
    
    <div class="mining-dashboard__content">
        <!-- Session Stats -->
        <div class="session-stats">
            <div class="stat">
                <span class="stat__label">Session</span>
                <span id="session-hashes" class="stat__value">0</span>
            </div>
            <div class="stat">
                <span class="stat__label">Proofs</span>
                <span id="session-proofs" class="stat__value">0</span>
            </div>
            <div class="stat">
                <span class="stat__label">Points</span>
                <span id="session-points" class="stat__value">0</span>
            </div>
        </div>
        
        <!-- Rare Hash Notifications -->
        <div id="rare-hash-notifications" class="rare-notifications">
            <h4>Rare Hashes</h4>
            <div id="rare-hash-list" class="rare-hash-list"></div>
        </div>
    </div>
</div>