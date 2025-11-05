@extends('layout')

@section('title', 'Image Library - Haichan')

@section('content')

<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: var(--content-bg); border: 3px solid var(--accent-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--accent-color); margin: 0 0 15px 0;">
            🖼️ IMAGE LIBRARY
        </h1>
        <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-bottom: 10px;">
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">{{ $totalImages }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">Unique Images</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $duplicatesPrevented }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">Duplicates Prevented</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">{{ $total }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">Total Shown</div>
            </div>
        </div>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">
            Automatic duplicate detection prevents wasteful storage of identical images
        </p>
    </div>

    <!-- Filter Bar -->
    <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <form method="GET" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <label style="font-weight: bold; color: var(--text-primary);">Board:</label>
            <select name="board" style="padding: 8px; border: 2px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg);">
                <option value="">All Boards</option>
                @foreach($boards as $board)
                    <option value="{{ $board->code }}" {{ $currentBoard == $board->code ? 'selected' : '' }}>
                        /{{ $board->code }}/ - {{ $board->name }}
                    </option>
                @endforeach
            </select>
            
            <label style="font-weight: bold; color: var(--text-primary);">Sort:</label>
            <select name="sort" style="padding: 8px; border: 2px solid var(--border-color); border-radius: 4px; background: var(--secondary-bg);">
                <option value="newest" {{ $sortBy == 'newest' ? 'selected' : '' }}>Newest First</option>
                <option value="oldest" {{ $sortBy == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="most_used" {{ $sortBy == 'most_used' ? 'selected' : '' }}>Most Reused</option>
                <option value="least_used" {{ $sortBy == 'least_used' ? 'selected' : '' }}>Least Reused</option>
            </select>
            
            <button type="submit" style="padding: 8px 16px; background: var(--accent-color); color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                Apply Filters
            </button>
            @if($currentBoard || $sortBy != 'newest')
                <a href="/image-library" style="padding: 8px 16px; background: #999; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Image Grid -->
    @if($images && $images->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            @foreach($images as $image)
                <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; overflow: hidden; transition: transform 0.2s;">
                    <a href="{{ $image->file_path }}" style="display: block; text-decoration: none;">
                        <div style="aspect-ratio: 1; overflow: hidden; background: #f0f0f0; position: relative;">
                            @if(file_exists(public_path($image->file_path)))
                                <img src="{{ $image->file_path }}" alt="Image" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">
                                    <span>Image not found</span>
                                </div>
                            @endif
                            <div style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                {{ $image->hash ? substr($image->hash, 0, 8) : 'N/A' }}
                            </div>
                        </div>
                        <div style="padding: 12px;">
                            <div style="font-size: 12px; color: var(--accent-color); font-weight: bold; margin-bottom: 5px;">
                                {{ $image['board_name'] }}
                            </div>
                            <div style="font-size: 13px; color: var(--text-primary); margin-bottom: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $image['subject'] ? \Illuminate\Support\Str::limit($image['subject'], 30) : 'No subject' }}
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary);">
                                {{ $image['created_at']->diffForHumans() }}
                            </div>
                            <div style="font-size: 10px; color: var(--text-secondary); margin-top: 4px;">
                                {{ ucfirst($image['type']) }} #{{ $image['id'] }}
                                @if(isset($image['file_size']) && $image['file_size'] > 0)
                                    • {{ round($image['file_size'] / 1024, 1) }}KB
                                @endif
                                <br>
                                @if(isset($image['usage_count']))
                                    <span style="color: {{ $image['usage_count'] > 1 ? '#28a745' : '#6c757d' }}; font-weight: bold;">
                                        @if($image['usage_count'] > 1)
                                            ♻️ Reused {{ $image['usage_count'] }} times (Saved {{ ($image['usage_count'] - 1) * round($image['file_size'] / 1024, 1) }}KB)
                                        @else
                                            📁 Original upload
                                        @endif
                                    </span>
                                @endif
                                <br>
                                <span style="font-size: 9px; font-family: monospace; color: #999;">
                                    Hash: {{ substr($image['hash'], 0, 8) }}...
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($lastPage > 1)
            <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap;">
                    @if($currentPage > 1)
                        <a href="?page={{ $currentPage - 1 }}{{ $currentBoard ? '&board=' . $currentBoard : '' }}" style="padding: 8px 16px; background: var(--accent-color); color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                            ← Previous
                        </a>
                    @endif
                    
                    <span style="color: var(--text-primary); font-weight: bold;">
                        Page {{ $currentPage }} of {{ $lastPage }}
                    </span>
                    
                    @if($currentPage < $lastPage)
                        <a href="?page={{ $currentPage + 1 }}{{ $currentBoard ? '&board=' . $currentBoard : '' }}" style="padding: 8px 16px; background: var(--accent-color); color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                            Next →
                        </a>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 40px; text-align: center;">
            <div style="font-size: 60px; margin-bottom: 20px;">📭</div>
            <h2 style="color: var(--text-secondary); margin: 0;">No images found</h2>
            @if($currentBoard)
                <p style="color: var(--text-secondary); margin-top: 10px;">
                    Try removing the board filter to see all images.
                </p>
            @endif
        </div>
    @endif

</div>

<style>
div[style*="grid-template-columns"] > div:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
</style>

@endsection
