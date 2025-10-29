@if(session('bitcoin_auth_id') == 1)
<!-- Updates popup modal -->
<div id="updates-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: none; align-items: center; justify-content: center;">
    <div id="updates-panel" style="background: linear-gradient(135deg, #2c2c2c, #1a1a1a); border: 2px solid #FFD700; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.5);">
        <!-- Header with tabs -->
        <div style="background: linear-gradient(135deg, #FFD700, #FFA500); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 20px; align-items: center;">
                <h3 style="margin: 0; color: #1a1a1a; font-size: 18px; font-weight: bold;">📢 Admin Updates</h3>
                <div id="updates-tabs" style="display: flex; gap: 5px;">
                    <button class="update-tab active" data-tab="global" style="background: rgba(0,0,0,0.3); color: #fff; border: none; padding: 6px 16px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                        🌍 Global
                    </button>
                    <button class="update-tab" data-tab="local" style="background: transparent; color: #1a1a1a; border: none; padding: 6px 16px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                        📍 Local ({{ $boardCode ?? 'board' }})
                    </button>
                </div>
            </div>
            <button id="close-updates" style="background: none; border: none; color: #1a1a1a; font-size: 28px; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: background 0.2s;">×</button>
        </div>
        
        <!-- Content area -->
        <div id="updates-content" style="padding: 20px; max-height: 500px; overflow-y: auto;">
            <!-- Global updates (default) -->
            <div id="global-updates" class="update-content" style="display: block;">
                <div id="global-updates-list" style="color: #F5F5DC;">
                    Loading global updates...
                </div>
                @if(session('bitcoin_auth_id') == 1)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #444;">
                    <textarea id="new-global-update" placeholder="Post a global update..." style="width: 100%; min-height: 80px; background: #1a1a1a; color: #F5F5DC; border: 1px solid #444; padding: 10px; border-radius: 6px; font-family: monospace; font-size: 13px; resize: vertical;"></textarea>
                    <button onclick="postUpdate('global')" style="margin-top: 10px; background: #FFD700; color: #1a1a1a; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px;">
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
                    <textarea id="new-local-update" placeholder="Post a board-specific update..." style="width: 100%; min-height: 80px; background: #1a1a1a; color: #F5F5DC; border: 1px solid #444; padding: 10px; border-radius: 6px; font-family: monospace; font-size: 13px; resize: vertical;"></textarea>
                    <button onclick="postUpdate('local')" style="margin-top: 10px; background: #FFD700; color: #1a1a1a; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px;">
                        Post Local Update
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

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

#close-updates:hover {
    background: rgba(0,0,0,0.2);
}
</style>

<script>
let currentBoardCode = '{{ $boardCode ?? "" }}';

// Toggle modal visibility
window.toggleUpdatesModal = function() {
    const modal = document.getElementById('updates-modal');
    if (modal.style.display === 'none' || !modal.style.display) {
        modal.style.display = 'flex';
        loadUpdates('global');
        updateUnreadCount();
    } else {
        modal.style.display = 'none';
    }
};

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('updates-modal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

document.getElementById('close-updates').addEventListener('click', function() {
    document.getElementById('updates-modal').style.display = 'none';
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
        const count = data.count || 0;
        
        // Update toolbar badge
        const toolbarBadge = document.getElementById('toolbar-update-count');
        if (toolbarBadge) {
            toolbarBadge.textContent = count;
            toolbarBadge.style.display = count > 0 ? 'flex' : 'none';
        }
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