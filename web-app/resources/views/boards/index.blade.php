@extends('layout')

@section('title', 'Haichan - Boards')

@section('content')
<!-- Japanese Web Aesthetic Container with Homepage Style -->
<div style="margin: 60px auto; max-width: 680px; background: #F5F5DC; border: 2px solid #708B75; box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header with proper color scheme -->
    <div style="background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); padding: 20px 30px; border-bottom: 2px solid #708B75; position: relative; text-align: center;">
        <div style="position: absolute; top: 15px; right: 20px; background: #3D315B; color: #FFFFEE; padding: 2px 8px; font-size: 9px; font-weight: 500; letter-spacing: 0.5px;">
            β版
        </div>

        <h1 style="font-size: 24px; color: #3D315B; margin: 0 0 8px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            <span class="strobing-emoji" style="font-size: 22px; color: #B87333;">🎯</span>
            <span class="fade-text" data-en="Board Directory" data-jp="板一覧">📋 Board Directory</span>
            <span class="strobing-emoji" style="font-size: 22px; color: #CD5C5C;">⚡</span>
        </h1>

        <div style="width: 80px; height: 2px; background: linear-gradient(to right, #708B75, #9AB87A); margin: 10px auto;"></div>

        <p class="fade-text" style="color: #708B75; font-size: 12px; line-height: 1.5; margin: 8px 0 0 0; font-weight: 400;" data-en="Anonymous discussion boards" data-jp="匿名掲示板">Anonymous discussion boards</p>
    </div>

    <!-- Content area -->
    <div style="padding: 30px; background: #FFFFEE;">
<div class="page-content">
    <!-- Navigation breadcrumb -->
    <div class="nav-breadcrumb">
        <span class="thread-info">Boards</span>
    </div>

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
</div>
    </div>
</div>
@endsection
