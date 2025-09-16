@extends('layout')

@section('title', $board->code . ' - ' . $board->name)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > {{ $board->code }}
</div>

<h2>{{ $board->code }} - {{ $board->name }}</h2>
<p>{{ $board->description }}</p>

<div style="margin: 15px 0;">
    <a href="{{ route('forum.create', $board->code) }}" style="background: #333; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">
        [Create New Thread]
    </a>
</div>

<table class="thread-list">
    <thead>
        <tr>
            <th>Thread</th>
            <th>Replies</th>
            <th>Last Post</th>
        </tr>
    </thead>
    <tbody>
        @forelse($threads as $thread)
            <tr>
                <td colspan="3">
                    <div class="thread-preview">
                        <div class="thread-header">
                            <div class="thread-title">
                                <a href="{{ route('forum.thread', [$board->code, $thread->id]) }}">{{ $thread->title }}</a>
                            </div>
                            <div class="thread-meta">
                                by {{ $thread->getAuthorDisplayName() }} - {{ $thread->created_at->format('m/d/y H:i') }} | {{ $thread->posts_count }} replies
                            </div>
                        </div>
                        
                        <div class="thread-content">
                            <div class="post-preview">
                                @if($thread->image_path)
                                    <div class="thread-preview-image" style="float: left; margin: 0 20px 20px 0;">
                                        <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->image_filename }}" 
                                             style="width: 180px; height: 180px; border: 1px solid #ccc; border-radius: 5px; object-fit: cover;">
                                    </div>
                                @endif
                                {{ Str::limit($thread->content, 800) }}
                                <div style="clear: both;"></div>
                            </div>
                            
                            @if($thread->posts->count() > 0)
                                <div class="replies-preview">
                                    @foreach($thread->posts->take(7) as $post)
                                        <div class="reply-preview">
                                            @if($post->image_path)
                                                <div class="post-preview-image" style="float: left; margin: 0 15px 15px 0;">
                                                    <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->image_filename }}" 
                                                         style="width: 120px; height: 120px; border: 1px solid #ccc; border-radius: 4px; object-fit: cover;">
                                                </div>
                                            @endif
                                            <span class="reply-author">{{ $post->getAuthorDisplayName() }}</span>: 
                                            {{ Str::limit($post->content, 350) }}
                                            <div style="clear: both;"></div>
                                            
                                            @if($post->replies->count() > 0)
                                                @foreach($post->replies->take(3) as $reply)
                                                    <div class="nested-reply">
                                                        @if($reply->image_path)
                                                            <div class="reply-preview-image" style="float: left; margin: 0 12px 12px 0;">
                                                                <img src="{{ asset('storage/' . $reply->image_path) }}" alt="{{ $reply->image_filename }}" 
                                                                     style="width: 80px; height: 80px; border: 1px solid #ccc; border-radius: 3px; object-fit: cover;">
                                                            </div>
                                                        @endif
                                                        <span class="reply-author">{{ $reply->getAuthorDisplayName() }}</span>: 
                                                        {{ Str::limit($reply->content, 280) }}
                                                        <div style="clear: both;"></div>
                                                        
                                                        @if($reply->replies->count() > 0)
                                                            @foreach($reply->replies->take(2) as $nestedReply)
                                                                <div class="deeply-nested-reply" style="margin-left: 30px; margin-top: 3px; font-size: 12px; color: #777;">
                                                                    @if($nestedReply->image_path)
                                                                        <div style="float: left; margin: 0 10px 10px 0;">
                                                                            <img src="{{ asset('storage/' . $nestedReply->image_path) }}" alt="{{ $nestedReply->image_filename }}" 
                                                                                 style="width: 60px; height: 60px; border: 1px solid #ccc; border-radius: 3px; object-fit: cover;">
                                                                        </div>
                                                                    @endif
                                                                    <span class="reply-author">{{ $nestedReply->getAuthorDisplayName() }}</span>: 
                                                                    {{ Str::limit($nestedReply->content, 240) }}
                                                                    <div style="clear: both;"></div>
                                                                </div>
                                                            @endforeach
                                                            @if($reply->replies->count() > 2)
                                                                <div class="more-replies" style="margin-left: 30px; font-size: 11px;">... {{ $reply->replies->count() - 2 }} more nested replies</div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endforeach
                                                @if($post->replies->count() > 3)
                                                    <div class="more-replies">... {{ $post->replies->count() - 3 }} more replies</div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($thread->posts_count > 7)
                                        <div class="more-posts">
                                            <a href="{{ route('forum.thread', [$board->code, $thread->id]) }}">
                                                View all {{ $thread->posts_count }} replies →
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No threads yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 30px; text-align: center; padding: 20px; border-top: 1px solid #ddd;">
    {{ $threads->links() }}
</div>
@endsection