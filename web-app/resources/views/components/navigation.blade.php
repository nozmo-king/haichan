@php
    $allBoards = \App\Models\Board::orderBy('created_at')->get();
@endphp

<div class="header">
    <h1><a href="/" style="font-family: 'Nova Cut', serif; color: #9AB87A; text-shadow: -1px -1px 0 #000000, 1px -1px 0 #000000, -1px 1px 0 #000000, 1px 1px 0 #000000;">📻 Haichan ⚡<br><span style="font-size: 12px; color: #9AB87A; text-shadow: -1px -1px 0 #000000, 1px -1px 0 #000000, -1px 1px 0 #000000, 1px 1px 0 #000000;">PoW Imageboard • β版</span></a></h1>
    <nav class="main-navigation">
        <div class="nav-section">
            <!-- Boards Dropdown -->
            <div class="dropdown-container">
                <button class="nav-link dropdown-trigger" id="boards-dropdown">
                    📋 Boards ▾
                </button>
                <div class="dropdown-menu" id="boards-menu">
                    <a href="/boards" class="dropdown-item">📋 All Boards</a>
                    <hr class="dropdown-divider">
                    @foreach($allBoards as $boardItem)
                    <a href="/{{ $boardItem->code }}" class="dropdown-item">
                        <strong>/{{ $boardItem->code }}/</strong> - {{ $boardItem->name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="nav-section">
            @if(isset($board))
                <a href="/{{ $board->code }}" class="nav-link current-board">{{ $board->code }}/</a>
                <a href="/{{ $board->code }}/catalog" class="nav-link">📑 Catalog</a>
            @endif
            <a href="/mining" class="nav-link">⛏️ Mining</a>
            <a href="/shop" class="nav-link">🛒 Shop</a>
            <a href="/chat" class="nav-link">💬 Chat</a>
            <a href="/stats" class="nav-link">📊 Stats</a>
            <a href="/rules" class="nav-link">📜 Rules</a>
            <a href="/faq" class="nav-link">❓ FAQ</a>
            
            @if(session('bitcoin_auth_user') && session('bitcoin_auth_user')->is_admin)
                <a href="/admin" class="nav-link admin-cp-btn">⚙️ Admin CP</a>
            @endif
        </div>
    </nav>
</div>

<style>
/* Navigation Styling */
.header {
    background: #F5F5DC !important;
    background-color: #F5F5DC !important;
    padding: 12px 20px;
    border-bottom: 3px solid #9AB87A !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    text-align: center;
}

.header h1 {
    margin: 0;
    display: inline-block;
    margin-right: 30px;
    text-align: center;
}

.header h1 a {
    color: #F5F5DC;
    text-decoration: none;
    font-size: 18pt;
    font-weight: bold;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.main-navigation {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

.nav-section {
    display: flex;
    align-items: center;
    gap: 5px;
}

.nav-link {
    background: #F5F5DC !important;
    color: #000000 !important;
    border: 2px solid #8B4513 !important;
    border-radius: 4px !important;
    padding: 4px 8px !important;
    margin: 2px !important;
    font-size: 9pt !important;
    font-weight: bold !important;
    font-family: 'Courier New', monospace !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    text-decoration: none !important;
    display: inline-block !important;
    white-space: nowrap;
}

.nav-link:hover {
    background: #FFFACD !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
    color: #000000 !important;
    border-color: #CD853F !important;
}

/* ULTIMATE NUCLEAR override for nav button colors - MUST BE BLACK */
.header .nav-link,
.header .nav-link *,
.header .nav-link:hover,
.header .nav-link:hover *,
.header .dropdown-trigger,
.header .dropdown-trigger *,
.header .dropdown-trigger:hover,
.header .dropdown-trigger:hover *,
.main-navigation .nav-link,
.main-navigation .nav-link *,
.main-navigation .nav-link:hover,
.main-navigation .nav-link:hover * {
    color: #000000 !important;
}

.nav-link.current-board {
    background: rgba(245, 245, 220, 0.2);
    border-color: rgba(245, 245, 220, 0.4);
    font-weight: bold;
}

.home-link {
    background: rgba(245, 245, 220, 0.15);
    border-color: rgba(245, 245, 220, 0.3);
    font-weight: bold;
}

.admin-cp-btn {
    background: rgba(255, 165, 0, 0.2) !important;
    border-color: rgba(255, 165, 0, 0.5) !important;
    color: #FFD700 !important;
    font-weight: bold;
    text-shadow: 0 0 5px rgba(255, 165, 0, 0.7);
    animation: admin-glow 2s ease-in-out infinite alternate;
}

.admin-cp-btn:hover {
    background: rgba(255, 165, 0, 0.3) !important;
    border-color: rgba(255, 215, 0, 0.8) !important;
    color: #FFF !important;
    text-shadow: 0 0 10px rgba(255, 165, 0, 0.9);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(255, 165, 0, 0.4);
}

@keyframes admin-glow {
    from {
        text-shadow: 0 0 5px rgba(255, 165, 0, 0.7);
        border-color: rgba(255, 165, 0, 0.5);
    }
    to {
        text-shadow: 0 0 8px rgba(255, 165, 0, 0.9);
        border-color: rgba(255, 215, 0, 0.7);
    }
}

/* Dropdown Styling */
.dropdown-container {
    position: relative;
    display: inline-block;
}

.dropdown-trigger {
    background: none;
    border: 1px solid transparent;
    cursor: pointer;
    user-select: none;
}

.dropdown-trigger:hover,
.dropdown-trigger.active {
    background: rgba(245, 245, 220, 0.1);
    border-color: rgba(245, 245, 220, 0.3);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: var(--primary-bg) !important;
    background-image: var(--crosshatch) !important;
    border: 3px solid var(--border-color) !important;
    border-radius: 12px;
    min-width: 280px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    z-index: 1000;
    display: none;
    margin-top: 4px;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    padding: 10px 16px;
    color: #444B6E;
    text-decoration: none;
    border-bottom: 1px solid #E0E0D0;
    font-size: 10pt;
    transition: background-color 0.2s ease;
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item:hover {
    background: #E8E8D0;
    color: #708B75;
}

.dropdown-divider {
    margin: 0;
    border: none;
    border-top: 1px solid #C0C0B0;
}

/* Theme Integration */
.theme-business .header {
    background: var(--business-highlight);
    border-bottom: 2px solid;
    border-color: var(--business-border-sunken);
}

.theme-business .dropdown-menu {
    background: var(--business-panel);
    border: 2px solid;
    border-color: var(--business-border-raised);
}

.theme-business .dropdown-item:hover {
    background: var(--business-concrete-light);
}

.theme-pleasure .header {
    background: var(--pleasure-highlight);
    border-bottom: 2px solid var(--pleasure-cyan);
    box-shadow: 0 2px 8px rgba(0, 255, 255, 0.3);
}

.theme-pleasure .nav-link {
    color: var(--pleasure-neon);
    text-shadow: 0 0 3px currentColor;
}

.theme-pleasure .nav-link:hover {
    background: rgba(0, 255, 65, 0.1);
    border-color: var(--pleasure-cyan);
    box-shadow: 0 0 5px var(--pleasure-cyan);
}

.theme-pleasure .dropdown-menu {
    background: var(--pleasure-panel);
    border: 2px solid var(--pleasure-cyan);
    box-shadow: 0 4px 16px rgba(0, 255, 255, 0.4);
}

.theme-pleasure .dropdown-item {
    color: var(--pleasure-neon);
    text-shadow: 0 0 2px currentColor;
}

.theme-pleasure .dropdown-item:hover {
    background: var(--pleasure-concrete-neon);
    color: var(--pleasure-pink);
    text-shadow: 0 0 5px currentColor;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header {
        padding: 8px 12px;
    }
    
    .main-navigation {
        background: #F5F5DC !important;
        gap: 10px;
        flex-direction: column;
        align-items: center;
    }
    
    .nav-section {
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .nav-link {
        padding: 6px 8px;
        font-size: 10pt;
    }
    
    .dropdown-menu {
        min-width: 240px;
        position: fixed;
        left: 12px;
        right: 12px;
        width: auto;
    }
}
</style>

<script>
// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownTrigger = document.getElementById('boards-dropdown');
    const dropdownMenu = document.getElementById('boards-menu');
    
    if (dropdownTrigger && dropdownMenu) {
        dropdownTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = dropdownMenu.classList.contains('show');
            
            // Close all dropdowns first
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
                trigger.classList.remove('active');
            });
            
            // Toggle current dropdown
            if (!isOpen) {
                dropdownMenu.classList.add('show');
                dropdownTrigger.classList.add('active');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownTrigger.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                dropdownTrigger.classList.remove('active');
            }
        });
        
        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
                dropdownTrigger.classList.remove('active');
            }
        });
    }
});
</script>