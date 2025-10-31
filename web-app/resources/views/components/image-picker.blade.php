@props([
    'name' => 'image_hash',
    'label' => 'Select Image',
    'placeholder' => 'Or enter image hash manually...',
    'required' => false,
    'showLibrary' => true
])

<div class="form-group">
    @if($label)
        <label class="form-label">{{ $label }}</label>
    @endif
    
    <!-- Image Hash Input -->
    <div class="flex gap-sm">
        <input 
            type="text" 
            name="{{ $name }}"
            id="{{ $name }}"
            class="form-input" 
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes }}
        >
        
        @if($showLibrary)
            <x-button type="button" variant="secondary" onclick="openImageLibraryPicker('{{ $name }}')">
                🖼️ Library
            </x-button>
        @endif
    </div>
    
    <!-- Image Preview -->
    <div id="{{ $name }}_preview" class="mt-sm" style="display: none;">
        <div class="flex align-center gap-sm p-sm bg-secondary rounded">
            <img id="{{ $name }}_preview_img" src="" alt="Preview" style="width: 64px; height: 64px; object-fit: cover; border-radius: 4px;">
            <div class="flex-1">
                <div class="text-small text-bold" id="{{ $name }}_preview_name">Image Name</div>
                <div class="text-small text-muted" id="{{ $name }}_preview_hash">Hash: ...</div>
                <x-button type="button" variant="ghost" size="small" onclick="clearImageSelection('{{ $name }}')">
                    ✕ Clear
                </x-button>
            </div>
        </div>
    </div>
</div>

<!-- Image Library Modal -->
<div id="image-library-modal" class="modal" style="display: none;">
    <div class="modal-content card" style="max-width: 80vw; max-height: 80vh; overflow: auto;">
        <div class="card-header flex justify-between align-center">
            <h3 class="card-title">Choose Image</h3>
            <x-button type="button" variant="ghost" onclick="closeImageLibraryPicker()">✕</x-button>
        </div>
        <div class="card-body">
            <div id="image-library-grid" class="grid gap-md" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-library-item {
    cursor: pointer;
    border: 2px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.image-library-item:hover {
    border-color: var(--border-accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.image-library-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.image-library-item .item-info {
    padding: var(--space-sm);
    background: var(--bg-primary);
}

.image-library-item .pow-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--accent-primary);
    color: var(--bg-primary);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-size: 10px;
    font-weight: bold;
}
</style>

<script nonce="{{ app('csp_nonce') }}">
let currentImagePickerField = null;

// Open image library picker modal
async function openImageLibraryPicker(fieldName) {
    currentImagePickerField = fieldName;
    
    // Fetch available images
    try {
        const response = await fetch('/api/image-library/shifting', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            populateImageLibraryModal(data.arrangement || []);
            document.getElementById('image-library-modal').style.display = 'flex';
        } else {
            console.error('Failed to load images');
        }
    } catch (error) {
        console.error('Error loading image library:', error);
    }
}

// Close image library picker modal
function closeImageLibraryPicker() {
    document.getElementById('image-library-modal').style.display = 'none';
    currentImagePickerField = null;
}

// Populate modal with images
function populateImageLibraryModal(images) {
    const grid = document.getElementById('image-library-grid');
    grid.innerHTML = '';
    
    if (images.length === 0) {
        grid.innerHTML = '<div class="text-center text-muted p-lg">No images available</div>';
        return;
    }
    
    images.forEach(image => {
        const item = document.createElement('div');
        item.className = 'image-library-item';
        item.onclick = () => selectImageFromLibrary(image);
        
        item.innerHTML = `
            <img src="/api/image-library/${image.id}/full" alt="${image.original_name}" loading="lazy">
            <div class="pow-badge">${image.total_pow_earned || 0}⚡</div>
            <div class="item-info">
                <div class="text-small text-bold mb-xs">${image.original_name || 'Untitled'}</div>
                <div class="text-small text-muted">Used ${image.usage_count || 0} times</div>
            </div>
        `;
        
        grid.appendChild(item);
    });
}

// Select image from library
function selectImageFromLibrary(image) {
    if (!currentImagePickerField) return;
    
    const field = document.getElementById(currentImagePickerField);
    if (!field) return;
    
    // Set the hash value
    field.value = image.hash;
    
    // Show preview
    showImagePreview(currentImagePickerField, image);
    
    // Trigger change event for form validation
    field.dispatchEvent(new Event('change'));
    field.dispatchEvent(new Event('input'));
    
    // Close modal
    closeImageLibraryPicker();
    
    // Show success feedback
    field.style.backgroundColor = 'var(--bg-glass)';
    field.style.borderColor = 'var(--accent-primary)';
    setTimeout(() => {
        field.style.backgroundColor = '';
        field.style.borderColor = '';
    }, 2000);
}

// Show image preview
function showImagePreview(fieldName, image) {
    const preview = document.getElementById(fieldName + '_preview');
    const img = document.getElementById(fieldName + '_preview_img');
    const name = document.getElementById(fieldName + '_preview_name');
    const hash = document.getElementById(fieldName + '_preview_hash');
    
    if (preview && img && name && hash) {
        img.src = `/api/image-library/${image.id}/full`;
        name.textContent = image.original_name || 'Untitled';
        hash.textContent = `Hash: ${image.hash.substring(0, 16)}...`;
        preview.style.display = 'block';
    }
}

// Clear image selection
function clearImageSelection(fieldName) {
    const field = document.getElementById(fieldName);
    const preview = document.getElementById(fieldName + '_preview');
    
    if (field) {
        field.value = '';
        field.dispatchEvent(new Event('change'));
        field.dispatchEvent(new Event('input'));
    }
    
    if (preview) {
        preview.style.display = 'none';
    }
}

// Auto-load image data if hash is entered manually
document.addEventListener('DOMContentLoaded', function() {
    // Set up hash field listeners
    document.querySelectorAll('input[name*="image_hash"], input[id*="image_hash"]').forEach(field => {
        field.addEventListener('input', async function() {
            const hash = this.value.trim();
            if (hash.length === 64) {  // Full hash length
                try {
                    const response = await fetch(`/api/image-library/hash/${hash}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    if (response.ok) {
                        const image = await response.json();
                        showImagePreview(this.name || this.id, image);
                    }
                } catch (error) {
                    console.log('Hash not found in library:', hash);
                }
            } else if (hash.length === 0) {
                // Clear preview if field is empty
                const preview = document.getElementById((this.name || this.id) + '_preview');
                if (preview) preview.style.display = 'none';
            }
        });
    });
    
    // Auto-populate from clipboard if available
    const clipboardData = localStorage.getItem('haichan_image_clipboard');
    if (clipboardData) {
        try {
            const data = JSON.parse(clipboardData);
            const age = Date.now() - data.timestamp;
            
            if (age < 3600000) { // Less than 1 hour old
                document.querySelectorAll('input[name*="image_hash"], input[id*="image_hash"]').forEach(field => {
                    if (!field.value) {
                        field.value = data.hash;
                        showImagePreview(field.name || field.id, data);
                        field.style.backgroundColor = 'var(--bg-glass)';
                        setTimeout(() => field.style.backgroundColor = '', 3000);
                    }
                });
            }
        } catch (error) {
            console.error('Error loading clipboard data:', error);
        }
    }
});
</script>
