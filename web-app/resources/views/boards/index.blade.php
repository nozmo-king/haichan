@extends('layout')

@section('title', 'Haichan - Boards')

@section('content')

        <div class="board-listing">
            <h2>Boards</h2>
            <div class="boards-grid">
                @foreach($boards as $board)
                <div class="board-card" 
                     data-board-code="{{ $board->code }}"
                     data-board-name="{{ $board->title }}">
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
@endsection
