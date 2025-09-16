@extends('layout')

@section('title', 'Forum Home')

@section('content')
<div class="board-grid">
    @forelse($boards as $board)
        <div class="board-item">
            <div class="board-code">
                <a href="{{ route('forum.board', $board->code) }}">{{ $board->code }}</a>
            </div>
            <div class="board-name">{{ $board->name }}</div>
            <div class="board-desc">{{ $board->description }}</div>
            <div class="board-stats">
                {{ $board->threads_count }} threads
            </div>
        </div>
    @empty
        <p>No boards available.</p>
    @endforelse
</div>
@endsection