<!DOCTYPE html>
<html>
<head>
    <title>{{ $board->title }} - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/global-mining.js')
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><a href="/">Haichan</a></h1>
            <nav>
                <a href="/boards">📋 Boards</a>
                <a href="/{{ $board->name }}/catalog">📑 Catalog</a>
                <a href="/mining">⛏️ Mining</a>
            </nav>
        </div>

        <div class="board-header">
            <h2>{{ $board->title }}</h2>
            <p>{{ $board->description }}</p>
        </div>


        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="post-form">
            <h3>[Start a new thread]</h3>
            <form method="POST" action="/{{ $board->name }}" enctype="multipart/form-data">
                @csrf
                <table>
                    <tr>
                        <td>Subject</td>
                        <td><input type="text" name="title" size="35" maxlength="200" required></td>
                    </tr>
                    <tr>
                        <td>Comment</td>
                        <td><textarea name="content" rows="5" cols="50" required></textarea></td>
                    </tr>
                    <tr>
                        <td>File</td>
                        <td><input type="file" name="image" accept="image/*"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button type="submit" class="btn-primary">Submit</button></td>
                    </tr>
                </table>
            </form>
        </div>

        <div class="threads-list">
            @forelse($threads as $thread)
            <div class="thread-preview" data-thread-id="{{ $thread->id }}" data-thread-title="{{ $thread->title }}">
                <div class="thread-header">
                    <span class="subject">
                        <a href="/{{ $board->name }}/{{ $thread->id }}">
                            {{ $thread->title ?: 'No Subject' }}
                        </a>
                    </span>
                    <span class="poster-info">
                        Anonymous {{ $thread->created_at->format('m/d/y H:i') }} No.{{ $thread->id }}
                    </span>
                </div>

                <div class="thread-content">
                    @if($thread->image_path)
                    <div class="thread-image">
                        <img src="/storage/{{ $thread->image_path }}" class="thumbnail">
                    </div>
                    @endif
                    
                    <div class="thread-text">
                        <p>{!! nl2br(e(Str::limit($thread->content, 300))) !!}</p>
                    </div>
                </div>

                <div style="font-size: 8pt; color: #888; margin-top: 5px;">
                    Replies: {{ $thread->reply_count }} | 
                    Images: {{ $thread->image_count }} | 
                    Last: {{ $thread->bumped_at ? $thread->bumped_at->diffForHumans() : $thread->created_at->diffForHumans() }}
                </div>
            </div>
            @empty
            <div class="thread-preview">
                <p style="text-align: center; padding: 40px;">No threads yet. Start the first one!</p>
            </div>
            @endforelse
        </div>

        <div style="text-align: center; padding: 20px;">
            {{ $threads->links() }}
        </div>
    </div>

    <script>
        // Auto-refresh threads every 10 seconds for live updates
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                // Fetch updated thread list
                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Parse response and update thread list
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newThreadsList = doc.querySelector('.threads-list');
                    const currentThreadsList = document.querySelector('.threads-list');
                    
                    if (newThreadsList && currentThreadsList) {
                        // Smoothly fade out, update, fade in
                        currentThreadsList.style.opacity = '0.5';
                        setTimeout(() => {
                            currentThreadsList.innerHTML = newThreadsList.innerHTML;
                            currentThreadsList.style.opacity = '1';
                        }, 200);
                    }
                })
                .catch(err => console.log('Refresh failed:', err));
            }, 10000); // 10 seconds
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', () => {
            startAutoRefresh();
        });
        
        // Stop when user navigates away
        window.addEventListener('beforeunload', stopAutoRefresh);
    </script>
</body>
</html>
