<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;

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
                    'threads_count' => $board->threads_count,
                ];
            }),
        ]);
    }

    public function getBoardsMetadata()
    {
        $boards = Board::withCount(['threads', 'posts'])->get();

        return response()->json([
            'boards' => $boards->map(function ($board) {
                // Calculate activity metrics
                $recentActivity = $board->threads()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();

                $todayActivity = $board->threads()
                    ->where('created_at', '>=', now()->startOfDay())
                    ->count();

                // Activity score based on recent posts and threads
                $recentPosts = $board->posts()
                    ->where('posts.created_at', '>=', now()->subDays(7))
                    ->count();

                $activityScore = ($recentActivity * 2) + $recentPosts;

                return [
                    'id' => $board->id,
                    'name' => $board->name,
                    'code' => $board->code,
                    'description' => $board->description,
                    'threads_count' => $board->threads_count,
                    'posts_count' => $board->posts_count,
                    'activity_score' => $activityScore,
                    'recent_activity' => $recentActivity,
                    'daily_activity' => $todayActivity,
                    'updated_at' => now(),
                ];
            }),
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
                'description' => $board->description,
            ],
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
                    'image_filename' => $thread->image_filename,
                ];
            }),
            'pagination' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total(),
            ],
        ]);
    }

    public function getThread($code, $threadId)
    {
        \Log::info('=== API GET THREAD START ===', [
            'board_code' => $code,
            'thread_id' => $threadId,
            'user_id' => auth()->id(),
        ]);

        try {
            $board = Board::where('code', $code)->firstOrFail();
            \Log::info('Board found for thread retrieval', ['board_id' => $board->id, 'board_name' => $board->name]);

            $thread = Thread::with(['posts' => function ($query) {
                $query->whereNull('parent_id')
                    ->orderBy('created_at', 'asc')
                    ->with(['allReplies']);
            }])->findOrFail($threadId);

            $totalPosts = $thread->posts()->count();
            $topLevelPosts = $thread->posts()->whereNull('parent_id')->count();

            \Log::info('=== API THREAD DATA RETRIEVED ===', [
                'thread_id' => $thread->id,
                'thread_title' => $thread->title,
                'total_posts_in_db' => $totalPosts,
                'top_level_posts' => $topLevelPosts,
                'posts_with_eager_loading' => $thread->posts->count(),
                'board_code' => $code,
            ]);

            $formattedPosts = $this->formatPosts($thread->posts);
            \Log::info('Posts formatted for API response', ['formatted_posts_count' => count($formattedPosts)]);

            return response()->json([
                'thread' => [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'content' => $thread->content,
                    'author_name' => $thread->getAuthorDisplayName(),
                    'posts_count' => $totalPosts,
                    'created_at' => $thread->created_at,
                    'image_path' => $thread->image_path,
                    'image_filename' => $thread->image_filename,
                ],
                'posts' => $formattedPosts,
            ]);

        } catch (\Exception $e) {
            \Log::error('=== API GET THREAD FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'board_code' => $code,
                'thread_id' => $threadId,
            ]);
            throw $e;
        }
    }

    public function createThread(Request $request, $code)
    {
        // Comprehensive debug logging for API thread creation
        \Log::info('=== API THREAD CREATION START ===', [
            'user_id' => auth()->id(),
            'board_code' => $code,
            'request_data' => [
                'title' => $request->input('title'),
                'content_length' => strlen($request->input('content', '')),
                'content_preview' => substr($request->input('content', ''), 0, 50).'...',
                'has_image' => $request->hasFile('image'),
                'user_agent' => $request->header('User-Agent'),
            ],
            'auth_user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'public_key_preview' => substr(auth()->user()->allowedPublicKey->public_key ?? 'none', 0, 16).'...',
            ] : 'not authenticated',
        ]);

        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required|max:2000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        try {
            $board = Board::where('code', $code)->firstOrFail();
            \Log::info('Board found for thread creation', ['board_id' => $board->id, 'board_name' => $board->name]);

            // Handle image upload
            $imageData = null;
            if ($request->hasFile('image')) {
                $imageData = $this->handleImageUpload($request->file('image'));
                \Log::info('Image uploaded for thread', ['image_data' => $imageData]);
            }

            $thread = Thread::create([
                'board_id' => $board->id,
                'title' => $request->title,
                'content' => $request->content,
                'user_id' => auth()->id(),
                'author_name' => substr(auth()->user()->allowedPublicKey->public_key, 0, 12).'...',
                'image_path' => $imageData['path'] ?? null,
                'image_filename' => $imageData['filename'] ?? null,
                'image_original_name' => $imageData['original_name'] ?? null,
                'image_size' => $imageData['size'] ?? null,
                'image_count' => $imageData ? 1 : 0,
            ]);

            \Log::info('=== API THREAD CREATED SUCCESSFULLY ===', [
                'thread_id' => $thread->id,
                'board_code' => $code,
                'title' => $thread->title,
                'author_name' => $thread->author_name,
                'content_length' => strlen($thread->content),
                'created_at' => $thread->created_at,
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
                    'image_filename' => $thread->image_filename,
                ],
            ], 201);

        } catch (\Exception $e) {
            \Log::error('=== API THREAD CREATION FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'board_code' => $code,
                'user_id' => auth()->id(),
                'title' => $request->input('title'),
            ]);
            throw $e;
        }
    }

    public function createReply(Request $request, $code, $threadId)
    {
        // Comprehensive debug logging for API reply submission
        \Log::info('=== API REPLY SUBMISSION START ===', [
            'user_id' => auth()->id(),
            'board_code' => $code,
            'thread_id' => $threadId,
            'request_data' => [
                'parent_id' => $request->input('parent_id'),
                'content_length' => strlen($request->input('content', '')),
                'content_preview' => substr($request->input('content', ''), 0, 50).'...',
                'has_image' => $request->hasFile('image'),
                'user_agent' => $request->header('User-Agent'),
                'content_type' => $request->header('Content-Type'),
            ],
            'auth_user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'public_key_preview' => substr(auth()->user()->allowedPublicKey->public_key ?? 'none', 0, 16).'...',
            ] : 'not authenticated',
        ]);

        $request->validate([
            'content' => 'required|max:2000',
            'parent_id' => 'nullable|exists:posts,id',
        ]);

        try {
            $board = Board::where('code', $code)->firstOrFail();
            \Log::info('Board found', ['board_id' => $board->id, 'board_name' => $board->name]);

            $thread = Thread::findOrFail($threadId);
            \Log::info('Thread found', ['thread_id' => $thread->id, 'thread_title' => $thread->title]);

            $post = Post::create([
                'thread_id' => $thread->id,
                'content' => $request->content,
                'user_id' => auth()->id(),
                'author_name' => substr(auth()->user()->allowedPublicKey->public_key, 0, 12).'...',
                'parent_id' => $request->parent_id,
            ]);

            \Log::info('=== API REPLY CREATED SUCCESSFULLY ===', [
                'post_id' => $post->id,
                'thread_id' => $thread->id,
                'board_code' => $code,
                'author_name' => $post->author_name,
                'content_length' => strlen($post->content),
                'parent_id' => $post->parent_id,
                'created_at' => $post->created_at,
            ]);

            return response()->json([
                'post' => [
                    'id' => $post->id,
                    'content' => $post->content,
                    'author_name' => $post->author_name,
                    'parent_id' => $post->parent_id,
                    'created_at' => $post->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            \Log::error('=== API REPLY CREATION FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'board_code' => $code,
                'thread_id' => $threadId,
                'user_id' => auth()->id(),
            ]);
            throw $e;
        }
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
                'replies' => $this->formatPosts($post->allReplies ?? collect()),
            ];
        });
    }

    private function handleImageUpload($file)
    {
        $filename = \Str::uuid().'.'.$file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        // Store original image using the public disk
        $file->storeAs('images', $filename, 'public');

        // Create simple thumbnail (copy for now)
        $file->storeAs('thumbs', $filename, 'public');

        return [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $size,
            'path' => "images/{$filename}",
        ];
    }
}
