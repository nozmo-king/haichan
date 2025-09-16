@extends('layout')

@section('title', '/' . $board->code . '/ - Catalog')

@section('content')
<style>
.catalog-container {
    max-width: 900px;
    margin: 60px auto 40px auto;
    background: #F5F5DC;
    border: 2px solid #708B75;
    box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);
}


.catalog-header {
    text-align: center;
    margin-bottom: 0;
    padding: 25px 40px;
    background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%);
    border-bottom: 2px solid #708B75;
}

.catalog-header h2 {
    color: #3D315B;
    margin: 0 0 15px 0;
    font-size: 24px;
    font-weight: 300;
    letter-spacing: 1.5px;
    font-family: 'Nova Cut', serif;
}

.catalog-header p {
    color: #708B75;
    margin: 15px 0 0 0;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.5;
}

.catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
    margin: 0;
    padding: 40px;
    background: #FFFFEE;
}

.catalog-thread {
    border: 2px solid #708B75;
    background: #F5F5DC;
    padding: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(68, 75, 110, 0.2);
}

.catalog-thread:hover {
    border-color: #444B6E;
    background: #FEFEFE;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.catalog-thread-image {
    width: 100%;
    max-height: 120px;
    object-fit: cover;
    border: 1px solid #CCCCCC;
    margin-bottom: 8px;
    display: block;
}

.catalog-thread-title {
    font-weight: bold;
    font-size: 12px;
    color: #0f0c5d;
    margin-bottom: 6px;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.catalog-thread-excerpt {
    font-size: 11px;
    color: #000;
    margin-bottom: auto;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
}

.catalog-thread-stats {
    font-size: 10px;
    color: #117743;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #EEEEEE;
}

.catalog-pow-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #9AB87A;
    color: #444B6E;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: bold;
    border: 1px solid #708B75;
}

.catalog-thread-number {
    color: #444B6E;
    font-weight: bold;
}

.catalog-empty {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-style: italic;
}

.catalog-empty a {
    color: #444B6E;
    text-decoration: none;
}

.catalog-empty a:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    .catalog-container {
        padding: 10px;
    }

    .catalog-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .catalog-thread {
        min-height: 120px;
        padding: 8px;
    }
}
</style>
<div class="catalog-container">
    <!-- Navigation breadcrumb -->
    <div class="nav-breadcrumb">
        [<a href="{{ route('boards.index') }}">Boards</a>]
        [<a href="/{{ $board->code }}">/{{ $board->code }}/</a>]
        <span class="thread-info">Catalog</span>
    </div>

    <!-- Board header -->
    <div class="catalog-header">
        <h2>/{{ $board->code }}/ - {{ $board->name }}</h2>
        <p>{{ $board->description }}</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="background: #ffeeee; border: 1px solid #dd0000; padding: 10px; margin: 10px 0; color: #dd0000;">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="catalog-grid">
        @forelse($threads as $thread)
        <div class="catalog-thread" onclick="window.location.href='/{{ $board->code }}/{{ $thread->id }}'"
             data-thread-id="{{ $thread->id }}"
             data-thread-title="{{ $thread->title }}">

            @if($thread->accumulated_points > 0)
            <div class="catalog-pow-badge">{{ number_format($thread->accumulated_points, 1) }}⚡</div>
            @endif

            @if($thread->image_path)
            <img src="{{ route('thread.image', $thread->id) }}" class="catalog-thread-image" alt="Thread image">
            @endif

            <div class="catalog-thread-title">
                {{ $thread->title ?: 'No Subject' }}
            </div>

            <div class="catalog-thread-excerpt">
                {{ Str::limit(strip_tags($thread->content), 120) }}
            </div>

            <div class="catalog-thread-stats">
                <span>R: {{ $thread->posts_count ?? 0 }}</span>
                <span class="catalog-thread-number">No.{{ $thread->id }}</span>
            </div>
        </div>
        @empty
        <div class="catalog-empty">
            <p>No threads found.</p>
            <p><a href="/{{ $board->code }}">← Back to board</a> or <a href="/{{ $board->code }}">create the first thread</a></p>
        </div>
        @endforelse
    </div>

    @if(count($threads) >= 20)
    <div style="text-align: center; padding: 20px; color: #708B75; font-size: 14px;">
        <em>Showing top 20 threads by energy expenditure</em>
    </div>
    @endif
</div>
@endsection