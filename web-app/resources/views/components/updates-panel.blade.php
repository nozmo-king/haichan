@if(session('bitcoin_auth_id') == 1)
<div id="updates-panel" style="background: linear-gradient(135deg, #2c2c2c, #1a1a1a); border: 2px solid #FFD700; border-radius: 8px; margin-bottom: 20px; overflow: hidden; display: none;">
    <!-- Header with tabs -->
    <div style="background: linear-gradient(135deg, #FFD700, #FFA500); padding: 10px 15px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <h3 style="margin: 0; color: #1a1a1a; font-size: 16px; font-weight: bold;">📢 Admin Updates</h3>
            <div id="updates-tabs" style="display: flex; gap: 5px;">
                <button class="update-tab active" data-tab="global" style="background: rgba(0,0,0,0.3); color: #fff; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                    🌍 Global
                </button>
                <button class="update-tab" data-tab="local" style="background: transparent; color: #1a1a1a; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                    📍 Local ({{ $boardCode ?? 'board' }})
                </button>
            </div>
        </div>
        <button id="close-updates" style="background: none; border: none; color: #1a1a1a; font-size: 20px; cursor: pointer; padding: 0 5px;">×</button>
    </div>
    
    <!-- Content area -->
    <div id="updates-content" style="padding: 15px; max-height: 400px; overflow-y: auto;">
        <!-- Global updates (default) -->
        <div id="global-updates" class="update-content" style="display: block;">
            <div id="global-updates-list" style="color: #F5F5DC;">
                Loading global updates...
            </div>
            @if(session('bitcoin_auth_id') == 1)
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #444;">
                <textarea id="new-global-update" placeholder="Post a global update..." style="width: 100%; min-height: 60px; background: #1a1a1a; color: #F5F5DC; border: 1px solid #444; padding: 8px; border-radius: 4px; font-family: monospace; font-size: 12px;"></textarea>
                <button onclick="postUpdate('global')" style="margin-top: 10px; background: #FFD700; color: #1a1a1a; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    Post Global Update
                </button>
            </div>
            @endif
        </div>
        
        <!-- Local updates -->
        <div id="local-updates" class="update-content" style="display: none;">
            <div id="local-updates-list" style="color: #F5F5DC;">
                Loading board updates...
            </div>
            @if(session('bitcoin_auth_id') == 1)
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #444;">
                <textarea id="new-local-update" placeholder="Post a board-specific update..." style="width: 100%; min-height: 60px; background: #1a1a1a; color: #F5F5DC; border: 1px solid #444; padding: 8px; border-radius: 4px; font-family: monospace; font-size: 12px;"></textarea>
                <button onclick="postUpdate('local')" style="margin-top: 10px; background: #FFD700; color: #1a1a1a; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    Post Local Update
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Floating trigger button -->
<button id="updates-trigger" style="position: fixed; top: 80px; right: 20px; background: linear-gradient(135deg, #FFD700, #FFA500); color: #1a1a1a; border: none; padding: 8px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 12px; z-index: 9998; box-shadow: 0 2px 10px rgba(255,215,0,0.5); display: flex; align-items: center; gap: 5px;">
    <span style="animation: pulse 2s infinite;">📢</span> Updates
    <span id="update-count" style="background: #FF0000; color: white; border-radius: 10px; padding: 2px 6px; font-size: 10px; margin-left: 5px;">0</span>
</button>

<style>
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.update-tab.active {
    background: rgba(0,0,0,0.3) !important;
    color: #fff !important;
}

.update-tab:hover {
    background: rgba(0,0,0,0.2) !important;
    color: #fff !important;
}

.update-item {
    background: rgba(255,255,255,0.05);
    border: 1px solid #333;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 10px;
}

.update-item .update-time {
    color: #999;
    font-size: 11px;
}

.update-item .update-text {
    color: #F5F5DC;
    font-size: 13px;
    margin-top: 5px;
    white-space: pre-wrap;
}
</style>

<script>
let currentBoardCode = '{{ $boardCode ?? "" }}';

// Toggle panel visibility
document.getElementById('updates-trigger').addEventListener('click', function() {
    const panel = document.getElementById('updates-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    if (panel.style.display === 'block') {
        loadUpdates('global');
        updateUnreadCount();
    }
});

document.getElementById('close-updates').addEventListener('click', function() {
    document.getElementById('updates-panel').style.display = 'none';
});

// Tab switching
document.querySelectorAll('.update-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.update-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Show corresponding content
        const tabName = this.getAttribute('data-tab');
        document.querySelectorAll('.update-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById(tabName + '-updates').style.display = 'block';
        
        // Load updates for this tab
        loadUpdates(tabName);
    });
});

// Load updates
async function loadUpdates(type) {
    try {
        const endpoint = type === 'global' ? '/api/updates/global' : `/api/updates/board/${currentBoardCode}`;
        const response = await fetch(endpoint, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        const listElement = document.getElementById(type + '-updates-list');
        
        if (data.updates && data.updates.length > 0) {
            listElement.innerHTML = data.updates.map(update => `
                <div class="update-item">
                    <div class="update-time">${formatTime(update.created_at)}</div>
                    <div class="update-text">${escapeHtml(update.message)}</div>
                    ${session_id == 1 ? `<button onclick="deleteUpdate(${update.id})" style="background: none; border: none; color: #FF6B6B; cursor: pointer; font-size: 11px; margin-top: 5px;">Delete</button>` : ''}
                </div>
            `).join('');
        } else {
            listElement.innerHTML = '<div style="color: #666; font-style: italic;">No updates yet.</div>';
        }
    } catch (error) {
        console.error('Failed to load updates:', error);
    }
}

// Post update
async function postUpdate(type) {
    const textarea = document.getElementById('new-' + type + '-update');
    const message = textarea.value.trim();
    
    if (!message) return;
    
    try {
        const response = await fetch('/api/updates/post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                type: type,
                board_code: type === 'local' ? currentBoardCode : null,
                message: message
            })
        });
        
        if (response.ok) {
            textarea.value = '';
            loadUpdates(type);
        }
    } catch (error) {
        console.error('Failed to post update:', error);
    }
}

// Delete update
async function deleteUpdate(id) {
    if (!confirm('Delete this update?')) return;
    
    try {
        const response = await fetch(`/api/updates/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            // Reload current tab
            const activeTab = document.querySelector('.update-tab.active').getAttribute('data-tab');
            loadUpdates(activeTab);
        }
    } catch (error) {
        console.error('Failed to delete update:', error);
    }
}

// Update unread count
async function updateUnreadCount() {
    try {
        const response = await fetch('/api/updates/unread-count', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        const countElement = document.getElementById('update-count');
        countElement.textContent = data.count || 0;
        countElement.style.display = data.count > 0 ? 'inline-block' : 'none';
    } catch (error) {
        console.error('Failed to get unread count:', error);
    }
}

// Helper functions
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' min ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + ' hours ago';
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

const session_id = {{ session('bitcoin_auth_id') ?? 0 }};

// Check for updates periodically
setInterval(updateUnreadCount, 30000);

// Initial load
updateUnreadCount();
</script>
@endif