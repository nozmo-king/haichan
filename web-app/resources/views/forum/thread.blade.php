@extends('layout')

@section('title', $thread->title . ' - ' . $board->code)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > 
    <a href="{{ route('forum.board', $board->code) }}">{{ $board->code }}</a> > 
    {{ $thread->title }}
</div>

<h2>{{ $thread->title }}</h2>

<div class="post">
    <div class="post-header">
        {{ $thread->getAuthorDisplayName() }} {{ $thread->created_at->format('m/d/y(D)H:i') }} No.{{ $thread->id }}
        <a href="#" onclick="toggleReplyForm({{ $thread->id }}, 'thread')" style="float: right; font-size: 11px;">[Reply]</a>
    </div>
    <div class="post-content">
        @if($thread->image_path)
            <div class="thread-image" style="margin: 10px 0;">
                <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->image_filename }}" 
                     style="max-width: 300px; max-height: 300px; border: 1px solid #ccc; cursor: pointer;" 
                     onclick="toggleImageSize(this)">
                <div style="font-size: 11px; color: #666; margin-top: 5px;">{{ $thread->image_filename }}</div>
            </div>
        @endif
        {{ $thread->content }}
    </div>
</div>

<div id="reply-form-thread-{{ $thread->id }}" class="reply-form" style="display: none;">
    <form action="{{ route('forum.reply', [$board->code, $thread->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <textarea name="content" rows="4" placeholder="Write your reply..." required maxlength="2000"></textarea>
        <div style="margin: 10px 0;">
            <label for="image-thread-{{ $thread->id }}" style="font-size: 12px;">Image (optional):</label>
            <input type="file" name="image" id="image-thread-{{ $thread->id }}" accept="image/*" 
                   style="width: 100%; padding: 5px; margin: 5px 0;">
            <small style="color: #666; font-size: 11px;">Max 2MB. JPEG, PNG, JPG, GIF</small>
        </div>
        <button type="submit">Post Reply</button>
    </form>
</div>

@foreach($thread->posts as $post)
    @include('forum.post', ['post' => $post, 'level' => 0])
@endforeach

<script>
function toggleReplyForm(id, type) {
    const form = document.getElementById('reply-form-' + type + '-' + id);
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

function toggleImageSize(img) {
    if (img.style.maxWidth === '300px') {
        img.style.maxWidth = '100%';
        img.style.maxHeight = 'none';
        img.style.cursor = 'zoom-out';
    } else {
        img.style.maxWidth = '300px';
        img.style.maxHeight = '300px';
        img.style.cursor = 'zoom-in';
    }
}
</script>
@endsection