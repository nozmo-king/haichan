<!DOCTYPE html>
<html>
<head>
    <title>{{ $board->title }} Catalog - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/global-mining.js')
    <style>
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .catalog-thread {
            border: 1px solid #708B75;
            background: #E8E8D0;
            color: #444B6E;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .catalog-thread:hover {
            background: #F5F5DC;
            box-shadow: 0 2px 8px rgba(112, 139, 117, 0.2);
        }
        .catalog-thread-image {
            width: 100%;
            max-height: 120px;
            object-fit: cover;
            border-radius: 3px;
            margin-bottom: 8px;
        }
        .catalog-thread-title {
            font-weight: bold;
            font-size: 11pt;
            color: #444B6E;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        .catalog-thread-excerpt {
            font-size: 9pt;
            color: #708B75;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .catalog-thread-stats {
            font-size: 8pt;
            color: #708B75;
            display: flex;
            justify-content: space-between;
            margin-top: auto;
        }
        .catalog-pow-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #708B75;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">📋 Boards</a>
                <a href="/{{ $board->name }}">{{ $board->name }}/</a>
                <a href="/mining">⛏️ Mining</a>
            </nav>
        </div>

        <div class="board-header">
            <h2>{{ $board->title }} - Catalog</h2>
            <p>{{ $board->description }}</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="catalog-grid">
            @forelse($threads as $thread)
            <div class="catalog-thread" onclick="window.location.href='/{{ $board->name }}/{{ $thread->id }}'"
                 data-thread-id="{{ $thread->id }}" 
                 data-thread-title="{{ $thread->title }}">
                
                @if($thread->proof_of_work_sum_points > 0)
                <div class="catalog-pow-badge">{{ $thread->proof_of_work_sum_points }}⚡</div>
                @endif
                
                @if($thread->image_path)
                <img src="/storage/{{ $thread->image_path }}" class="catalog-thread-image" alt="Thread image">
                @endif
                
                <div class="catalog-thread-title">
                    {{ $thread->title ?: 'No Subject' }}
                </div>
                
                <div class="catalog-thread-excerpt">
                    {{ Str::limit(strip_tags($thread->content), 100) }}
                </div>
                
                <div class="catalog-thread-stats">
                    <span>R: {{ $thread->posts_count ?? 0 }}</span>
                    <span>{{ $thread->created_at->format('m/d H:i') }}</span>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <p>No threads yet. Start the first one!</p>
            </div>
            @endforelse
        </div>

        <div style="text-align: center; padding: 20px;">
            {{ $threads->links() }}
        </div>
    </div>

</body>
</html>