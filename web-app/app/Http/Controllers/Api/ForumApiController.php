<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;

class ForumApiController extends Controller
{
    public function getBoards()
    {
        $boards = Board::withCount('threads')->get();
        
        return response()->json([
            'boards' => $boards->map(function ($board) {
                return [
                    'id' => $board->id,
                    'name' => $board->name,
                    'code' => $board->code,
                    'description' => $board->description,
                    'threads_count' => $board->threads_count
                ];
            })
        ]);
    }

    public function getBoard($code)
    {
        $board = Board::where('code', $code)->firstOrFail();
        
        return response()->json([
            'board' => [
                'id' => $board->id,
                'name' => $board->name,
                'code' => $board->code,
                'description' => $board->description
            ]
        ]);
    }

    public function getThreads($code, Request $request)
    {
        $board = Board::where('code', $code)->firstOrFail();
        
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        
        $threads = Thread::where('board_id', $board->id)
            ->withCount('posts')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return response()->json([
            'threads' => $threads->map(function ($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'content' => $thread->content,
                    'author_name' => $thread->getAuthorDisplayName(),
                    'posts_count' => $thread->posts_count,
                    'created_at' => $thread->created_at,
                    'image_path' => $thread->image_path,
                    'image_filename' => $thread->image_filename
                ];
            }),
            'pagination' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total()
            ]
        ]);
    }

    public function getThread($code, $threadId)
    {
        $board = Board::where('code', $code)->firstOrFail();
        $thread = Thread::with(['posts' => function($query) {
            $query->whereNull('parent_id')
                  ->orderBy('created_at', 'asc')
                  ->with(['allReplies']);
        }])->findOrFail($threadId);
        
        return response()->json([
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'content' => $thread->content,
                'author_name' => $thread->getAuthorDisplayName(),
                'posts_count' => $thread->posts()->count(),
                'created_at' => $thread->created_at,
                'image_path' => $thread->image_path,
                'image_filename' => $thread->image_filename
            ],
            'posts' => $this->formatPosts($thread->posts)
        ]);
    }

    public function createThread(Request $request, $code)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required|max:2000'
        ]);

        $board = Board::where('code', $code)->firstOrFail();

        $thread = Thread::create([
            'board_id' => $board->id,
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id(),
            'author_name' => substr(auth()->user()->allowedPublicKey->public_key, 0, 12) . '...'
        ]);

        return response()->json([
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'content' => $thread->content,
                'author_name' => $thread->author_name,
                'posts_count' => 0,
                'created_at' => $thread->created_at,
                'image_path' => $thread->image_path,
                'image_filename' => $thread->image_filename
            ]
        ], 201);
    }

    public function createReply(Request $request, $code, $threadId)
    {
        // Debug logging for API reply submission
        \Log::info('API Reply submission received', [
            'parent_id' => $request->input('parent_id'),
            'content_preview' => substr($request->input('content', ''), 0, 30) . '...',
            'thread_id' => $threadId,
            'board_code' => $code
        ]);

        $request->validate([
            'content' => 'required|max:2000',
            'parent_id' => 'nullable|exists:posts,id'
        ]);

        $board = Board::where('code', $code)->firstOrFail();
        $thread = Thread::findOrFail($threadId);

        $post = Post::create([
            'thread_id' => $thread->id,
            'content' => $request->content,
            'user_id' => auth()->id(),
            'author_name' => substr(auth()->user()->allowedPublicKey->public_key, 0, 12) . '...',
            'parent_id' => $request->parent_id
        ]);

        return response()->json([
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $post->author_name,
                'parent_id' => $post->parent_id,
                'created_at' => $post->created_at
            ]
        ], 201);
    }

    private function formatPosts($posts)
    {
        return $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'author_name' => $post->getAuthorDisplayName(),
                'created_at' => $post->created_at,
                'image_path' => $post->image_path,
                'image_filename' => $post->image_filename,
                'replies' => $this->formatPosts($post->allReplies ?? collect())
            ];
        });
    }
}