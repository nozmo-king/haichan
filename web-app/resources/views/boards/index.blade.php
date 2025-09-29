@extends('layout')

@section('title', 'Haichan - Boards')

@section('content')
<x-board-layout title="Board Directory" description="Anonymous discussion boards" :showNav="false">
    <div class="boards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        @foreach($boards as $board)
        <div class="tui-window" style="padding: 0;">
            <div class="tui-header">
                <div class="tui-dots">
                    <div class="tui-dot"></div>
                    <div class="tui-dot"></div>
                    <div class="tui-dot"></div>
                </div>
                <div class="tui-title">{{ $board->code }}</div>
                <div class="tui-badge">{{ $board->threads_count }} threads</div>
            </div>
            <div class="tui-p">
                <h3 style="margin: 0 0 8px 0;">
                    <a href="{{ $board->url }}" style="color: var(--tui-accent); text-decoration: none;">
                        {{ $board->title }}
                    </a>
                </h3>
                <p style="color: var(--tui-muted); font-size: 12px; margin: 0 0 12px 0; line-height: 1.4;">
                    {{ $board->description }}
                </p>
                <div style="display: flex; gap: 12px; font-size: 11px; color: var(--tui-muted);">
                    <span class="tui-badge">{{ $board->threads_count }} threads</span>
                    <span class="tui-badge">{{ $board->post_count ?? 0 }} posts</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-board-layout>
@endsection
