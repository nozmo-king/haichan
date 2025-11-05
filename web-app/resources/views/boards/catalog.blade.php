@extends('layout')

@section('title', '/{{ $board->code }}/ - Catalog')

@section('content')
<div style="text-align: center; margin: 20px 0; padding: 15px; background: var(--ib-header); border-radius: 8px;">
    <h1 style="margin: 0; font-size: 24px; font-weight: 600;">/{{ $board->code }}/ - Catalog</h1>
    <p style="font-size: 14px; color: var(--ib-text-muted); margin: 8px 0 0 0;">{{ $board->description }}</p>
    <p style="font-size: 12px; color: var(--ib-accent); margin: 5px 0 0 0;">⚡ Threads ordered by Proof-of-Work score</p>
</div>

<div class="nav-links" style="margin-bottom: 20px;">
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a> |
    <a href="#bottom">Bottom</a>
</div>

<div class="catalog-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin: 0 auto; max-width: 1400px;">
    @forelse($threads as $thread)
    <div class="catalog-thread" 
         style="cursor: pointer; 
                transition: all 0.15s ease; 
                background: #fff; 
                border: 1px solid #ddd; 
                border-radius: 4px;
                overflow: hidden;
                position: relative;
                box-shadow: 0 1px 2px rgba(0,0,0,0.1);" 
         data-thread-id="{{ $thread->id }}"
         data-href="/{{ $board->code }}/{{ $thread->id }}">
        
        <div class="pow-indicator" data-value="{{ $thread->accumulated_points ?? 0 }}"
             style="position: absolute; top: 8px; right: 8px; 
                    background: #2e7d32; color: white; 
                    font-size: 10px; font-weight: bold; 
                    padding: 2px 6px; border-radius: 10px; 
                    z-index: 10; transition: all 0.3s ease;">
            ⚡{{ number_format($thread->accumulated_points ?? 0, 1) }}
        </div>

        @if($thread->image_path)
        <div style="height: 140px; overflow: hidden; background: #f5f5f5;">
            <img src="{{ route('thread.image', $thread->id) }}" 
                 data-hash="{{ $thread->image_hash ?? '' }}" data-thread-id="{{ $thread->id }}"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        @endif

        <div style="padding: 10px;">
            <div style="font-size: 13px; font-weight: 600; color: #333; 
                       margin-bottom: 6px; line-height: 1.3;
                       overflow: hidden; text-overflow: ellipsis; 
                       white-space: nowrap;">
                {{ $thread->title ?: 'No Subject' }}
            </div>
            
            <div style="font-size: 11px; color: #666; line-height: 1.4; 
                       margin-bottom: 8px; height: 45px; overflow: hidden;">
                {{ Str::limit($thread->content, 100) }}
            </div>
            
            <div style="display: flex; justify-content: space-between; 
                       align-items: center; font-size: 10px; color: #888; 
                       padding-top: 6px; border-top: 1px solid #eee;">
                <span>No.{{ $thread->id }}</span>
                <div style="display: flex; gap: 8px;">
                    <span>R:{{ $thread->reply_count ?? 0 }}</span>
                    @if($thread->image_path)<span>I:1</span>@endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; 
                background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
        <p style="color: #666; margin: 0 0 15px 0; font-size: 16px;">No threads in catalog yet.</p>
        <a href="/{{ $board->code }}" 
           style="color: #2e7d32; text-decoration: none; font-weight: 600; 
                  padding: 10px 20px; border: 1px solid #2e7d32; border-radius: 4px;">
            Create the first thread
        </a>
    </div>
    @endforelse
</div>

<div class="nav-links" style="margin: 30px 0; text-align: center;">
    <a name="bottom"></a>
    <a href="#">Top</a> |
    <a href="/">Board List</a> |
    <a href="/{{ $board->code }}">Board Index</a>
</div>

<script nonce="{{ app('csp_nonce') }}">
let catalogPollInterval;

function startCatalogPolling() {
    updateCatalogOrder();
    catalogPollInterval = setInterval(updateCatalogOrder, 5000);
}

async function updateCatalogOrder() {
    try {
        const response = await fetch('/api/boards/{{ $board->code }}/thread-order');
        const data = await response.json();
        console.log('📊 Catalog: API response:', data);
        
        let updatesCount = 0;
        data.threads.forEach((thread, index) => {
            console.log(`🔍 Processing thread ${thread.id}: accumulated_points = ${thread.accumulated_points}`);
            const threadEl = document.querySelector(`[data-thread-id="${thread.id}"]`);
            console.log(`🔍 Thread element found:`, !!threadEl);
            
            if (threadEl) {
                const powIndicator = threadEl.querySelector('.pow-indicator');
                console.log(`🔍 PoW indicator found:`, !!powIndicator);
                
                if (powIndicator) {
                    const oldValue = parseFloat(powIndicator.dataset.value || 0);
                    const newValue = thread.accumulated_points;
                    console.log(`📊 Thread ${thread.id}: ${oldValue} vs ${newValue} (equal: ${oldValue === newValue})`);
                    
                    if (newValue !== oldValue) {
                        console.log(`📊 Updating thread ${thread.id}: ${oldValue} -> ${newValue}`);
                        powIndicator.dataset.value = newValue;
                        powIndicator.textContent = `⚡${newValue.toFixed(1)}`;
                        updatesCount++;
                        
                        if (newValue > oldValue) {
                            powIndicator.classList.add('pow-increased');
                            setTimeout(() => powIndicator.classList.remove('pow-increased'), 1000);
                        }
                    }
                } else {
                    console.log(`❌ No .pow-indicator found in thread ${thread.id}`);
                }
            } else {
                console.log(`❌ No element found with data-thread-id="${thread.id}"`);
            }
        });
        
        const container = document.querySelector('.catalog-grid');
        if (container) {
            data.threads.forEach((thread, index) => {
                const threadEl = document.querySelector(`[data-thread-id="${thread.id}"]`);
                if (threadEl && threadEl.parentElement === container) {
                    const currentIndex = Array.from(container.children).indexOf(threadEl);
                    if (currentIndex !== index && currentIndex !== -1) {
                        threadEl.style.transition = 'transform 0.5s ease';
                        if (index === 0) {
                            container.prepend(threadEl);
                        } else {
                            const beforeEl = container.children[index];
                            if (beforeEl && beforeEl !== threadEl) {
                                container.insertBefore(threadEl, beforeEl);
                            }
                        }
                    }
                }
            });
        }
        
        console.log(`✅ Catalog update complete: ${updatesCount} threads updated`);
    } catch (error) {
        console.error('❌ Catalog polling error:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Set up click handlers for catalog items
    document.querySelectorAll('.catalog-thread').forEach(thread => {
        thread.addEventListener('click', function() {
            const href = this.dataset.href;
            if (href) {
                window.location.href = href;
            }
        });
        
        // Add hover effects
        thread.addEventListener('mouseenter', function() {
            this.style.borderColor = '#999';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
        });
        
        thread.addEventListener('mouseleave', function() {
            this.style.borderColor = '#ddd';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 1px 2px rgba(0,0,0,0.1)';
        });
    });
    
    // Listen for mining events to trigger immediate updates
    console.log('📊 Catalog: Setting up mining event listeners...');
    
    document.addEventListener('proofSubmitted', function(e) {
        console.log('📊 Catalog: Mining proof submitted, updating immediately', e.detail);
        updateCatalogOrder();
    });
    
    window.addEventListener('mining:complete', function(e) {
        console.log('📊 Catalog: Mining completed, updating order', e.detail);
        setTimeout(() => updateCatalogOrder(), 500);
    });
    
    // Also listen for all mining events to see what's happening
    window.addEventListener('mining:progress', function(e) {
        console.log('📊 Catalog: Mining progress detected', e.detail);
    });
    
    // Test function for catalog
    window.testCatalogUpdate = function() {
        console.log('🧪 Testing catalog update...');
        updateCatalogOrder();
    };
    
    // Debug function to check DOM elements
    window.debugCatalog = function() {
        console.log('🔍 Checking catalog DOM elements...');
        const threadElements = document.querySelectorAll('[data-thread-id]');
        console.log(`Found ${threadElements.length} thread elements:`);
        threadElements.forEach(el => {
            const threadId = el.dataset.threadId;
            const powIndicator = el.querySelector('.pow-indicator');
            console.log(`  Thread ${threadId}: pow-indicator = ${!!powIndicator}`);
            if (powIndicator) {
                console.log(`    Current value: ${powIndicator.dataset.value}, text: ${powIndicator.textContent}`);
            }
        });
    };
    
    startCatalogPolling();
});
</script>

<style nonce="{{ app('csp_nonce') }}">
.pow-indicator {
    transition: all 0.3s ease;
}

.pow-increased {
    animation: powPulse 0.8s ease;
}

@keyframes powPulse {
    0%, 100% { 
        transform: scale(1); 
        background: #2e7d32;
    }
    50% { 
        transform: scale(1.2); 
        background: #4caf50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
    }
}
</style>
@endsection