<div class="post {{ $level > 0 ? 'nested-post' : '' }}">
    <div class="post-header">
        {{ $post->getAuthorDisplayName() }} {{ $post->created_at->format('m/d/y(D)H:i') }} No.{{ $post->id }}
        @if($post->parent_id)
            >>{{ $post->parent_id }}
        @endif
        <a href="#" onclick="toggleReplyForm({{ $post->id }}, 'post')" style="float: right; font-size: 11px;">[Reply]</a>
    </div>
    <div class="post-content">
        @if($post->image_path)
            <div class="post-image" style="margin: 10px 0;">
                <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->image_filename }}" 
                     style="max-width: 300px; max-height: 300px; border: 1px solid #ccc; cursor: pointer;" 
                     onclick="toggleImageSize(this)">
                <div style="font-size: 11px; color: #666; margin-top: 5px;">{{ $post->image_filename }}</div>
            </div>
        @endif
        {{ $post->content }}
    </div>
</div>

<div id="reply-form-post-{{ $post->id }}" class="reply-form" style="display: none;">
    <form action="{{ strtolower('/' . $board->code . '/' . $thread->id . '/reply') }}" method="POST" enctype="multipart/form-data" data-original-action="{{ strtolower('/' . $board->code . '/' . $thread->id . '/reply') }}">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $post->id }}">
        <!-- Hidden PoW fields -->
        <input type="hidden" name="pow_nonce" id="pow_nonce_post_{{ $post->id }}" required>
        <input type="hidden" name="pow_hash" id="pow_hash_post_{{ $post->id }}" required>
        <input type="hidden" name="pow_challenge_id" id="pow_challenge_id_post_{{ $post->id }}" required>

        <textarea name="content" rows="3" placeholder="Write your reply..." required maxlength="5000"></textarea>
        <div style="margin: 10px 0;">
            <label for="image-post-{{ $post->id }}" style="font-size: 12px;">Image (optional):</label>
            <input type="file" name="image" id="image-post-{{ $post->id }}" accept="image/*"
                   style="width: 100%; padding: 5px; margin: 5px 0;">
            <small style="color: #666; font-size: 11px;">Max 2MB. JPEG, PNG, JPG, GIF</small>
            <div style="margin-top: 5px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 11px;">
                    <input type="checkbox" name="dither" value="1">
                    <span style="color: #666;">🎨 Dither</span>
                </label>
            </div>
        </div>
        <button type="submit">Post Reply</button>
    </form>
</div>

@if($post->allReplies)
    @foreach($post->allReplies as $reply)
        @include('forum.post', ['post' => $reply, 'level' => $level + 1])
    @endforeach
@endif