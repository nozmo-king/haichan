<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

final class PostController extends Controller
{
    public function index(Request $request, Thread $thread)
    {
        $posts = Post::query()
            ->where('thread_id', $thread->id)
            ->orderBy('created_at')
            ->get(['id','thread_id','parent_id','user_id','content as body','image_path','created_at']);

        return response()->json([
            'data' => $posts,
        ]);
    }

    public function store(Request $request, Thread $thread)
    {
        // NOTE: tie this to your secp256k1 session auth middleware
        $user = $request->user();

        $validated = $request->validate([
            'body'      => ['nullable','string','max:8000'],
            'parent_id' => ['nullable','integer', Rule::exists('posts','id')->where('thread_id', $thread->id)],
            'image'     => ['nullable','file','image','max:8192'], // 8MB
        ]);

        if (empty($validated['body']) && !$request->hasFile('image')) {
            return response()->json(['message' => 'Post must include text or an image'], 422);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public'); // storage/app/public/posts/...
        }

        $post = new Post([
            'thread_id'  => $thread->id,
            'parent_id'  => $validated['parent_id'] ?? null,
            'user_id'    => $user?->id, // or your public-key user mapping
            'content'    => $validated['body'] ?? null, // Map body to content
            'image_path' => $path,
        ]);

        $post->save();

        return response()->json(['data' => $post], 201);
    }
}