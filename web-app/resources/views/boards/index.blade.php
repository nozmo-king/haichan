<!DOCTYPE html>
<html>
<head>
    <title>Haichan - Boards</title>
    <link rel="stylesheet" href="/css/haichan.css">
</head>
<body>
    <div class="container">
        @include('components.navigation')

        <div class="board-listing">
            <h2>Boards</h2>
            <div class="boards-grid">
                @foreach($boards as $board)
                <div class="board-card">
                    <h3><a href="{{ $board->url }}">{{ $board->title }}</a></h3>
                    <p>{{ $board->description }}</p>
                    <div class="board-stats">
                        <span>{{ $board->threads_count }} threads</span>
                        <span>{{ $board->post_count }} posts</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
