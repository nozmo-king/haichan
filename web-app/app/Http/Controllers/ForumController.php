<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;
use App\Models\ProofSubmission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('threads')->get();
        return view('boards.index', compact('boards'));
    }

    public function showBoard($board)
    {
        // Handle both board codes (gen) and board names (General)
        $board = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        $threads = Thread::where('board_id', $board->id)
            ->with('bitcoinUser')
            ->withCount('posts')
            ->orderByRaw('(bump_score + COALESCE((SELECT SUM(points) FROM proof_of_works WHERE thread_id = threads.id), 0)) DESC')
            ->orderBy('bumped_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = $thread->bump_score + \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');
                return $thread;
            });
        
        return view('boards.show', compact('board', 'threads'));
    }
    
    public function showCatalog($board)
    {
        $boardModel = Board::where('code', $board)->firstOrFail();
        $threads = Thread::where('board_id', $boardModel->id)
            ->with('bitcoinUser')
            ->withCount('posts')
            ->orderByRaw('(bump_score + COALESCE((SELECT SUM(points) FROM proof_of_works WHERE thread_id = threads.id), 0)) DESC')
            ->orderBy('bumped_at', 'desc')
            ->take(100)
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = $thread->bump_score + \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');
                return $thread;
            });

        return view('boards.catalog', ['board' => $boardModel, 'threads' => $threads]);
    }

    public function showTheMC()
    {
        // Get all threads from all boards with their board info
        $threads = Thread::with(['board', 'bitcoinUser'])
            ->withCount('posts')
            ->orderByRaw('(bump_score + COALESCE((SELECT SUM(points) FROM proof_of_works WHERE thread_id = threads.id), 0)) DESC')
            ->orderBy('bumped_at', 'desc')
            ->take(200) // Show more threads since it's from all boards
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = $thread->bump_score + \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');
                return $thread;
            });

        $totalBoards = Board::count();
        $totalThreads = Thread::count();

        return view('boards.the-mc', compact('threads', 'totalBoards', 'totalThreads'));
    }

    public function showThread($board, $threadId)
    {
        // Handle both board codes (gen) and board names (General)
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        $thread = Thread::with('bitcoinUser')->findOrFail($threadId);

        // Return just PoW score for AJAX requests
        if (request()->get('pow_only')) {
            $powScore = ProofSubmission::where('target_type', 'thread')
                ->where('target_id', $threadId)
                ->sum('difficulty');
            return response()->json(['pow_score' => $powScore]);
        }
        
        // Get sort preference (default: chronological)
        $sortBy = request()->get('sort', 'chronological');

        // Load top-level posts (no parent) with their nested replies
        $posts = Post::where('thread_id', $threadId)
                    ->whereNull('parent_id')
                    ->with([
                        'bitcoinUser',
                        'allReplies' => function($query) use ($sortBy) {
                            $query->with('bitcoinUser');
                            if ($sortBy === 'pow') {
                                $query->orderBy('pow_difficulty', 'desc')->orderBy('created_at', 'asc');
                            } else {
                                $query->orderBy('created_at', 'asc');
                            }
                        }
                    ]);

        if ($sortBy === 'pow') {
            $posts = $posts->orderBy('pow_difficulty', 'desc')->orderBy('created_at', 'asc');
        } else {
            $posts = $posts->orderBy('created_at', 'asc');
        }

        $posts = $posts->get();
        
        return view('boards.thread', ['board' => $boardModel, 'thread' => $thread, 'posts' => $posts]);
    }

    public function createThread($boardCode)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        return view('forum.create-thread', compact('board'));
    }

    public function storeThread(Request $request, $board)
    {
        // Get authenticated user or create anonymous user
        $userId = session('bitcoin_auth_id');
        $postAsAnonymous = $request->boolean('post_anonymous', false);

        if ($userId && !$postAsAnonymous) {
            $authorName = session('bitcoin_auth_user')->username;
            $finalUserId = $userId;
        } else {
            $authorName = 'Anonymous#' . substr(hash('sha256', $request->ip() . time()), 0, 8);
            $finalUserId = null;
        }
        
        // Simple validation - accept either title or subject
        $title = $request->input('title') ?: $request->input('subject');
        if (!$title) {
            return back()->withErrors(['title' => 'Title is required'])->withInput();
        }
        
        if (!$request->filled('content')) {
            return back()->withErrors(['content' => 'Content is required'])->withInput();
        }

        // Validate PoW and optional image upload
        $request->validate([
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string|size:32',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600'
        ]);

        $boardModel = Board::where('code', $board)->firstOrFail();

        // Generate proper challenge data and verify PoW
        $challengeData = "thread:{$boardModel->code}:{$title}:{$request->pow_challenge_id}";
        $verification = Thread::verifyProofOfWork(
            $challengeData,
            $request->pow_nonce,
            $request->pow_hash,
            '21e' // Global difficulty setting
        );

        if (!$verification['valid']) {
            Log::error('Thread PoW verification failed', [
                'error' => $verification['error'],
                'board' => $board,
                'title' => $title,
                'challenge_data' => $challengeData,
                'nonce' => $request->pow_nonce,
                'submitted_hash' => $request->pow_hash
            ]);
            return back()->withErrors(['pow' => 'Proof of work verification failed: ' . $verification['error']])->withInput();
        }

        $threadData = [
            'board_id' => $boardModel->id,
            'title' => $title,
            'content' => $request->content,
            'user_id' => $finalUserId,
            'author_name' => $authorName,
            'pow_nonce' => $request->pow_nonce,
            'pow_hash' => $request->pow_hash,
            'pow_challenge_id' => $request->pow_challenge_id,
            'pow_pattern' => '21e8',
            'pow_difficulty' => 1.0,
            'pow_verified_at' => now(),
            'ip_address' => $request->ip(),
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip())
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Store in ImageLibrary (which handles deduplication)
            $libraryImage = \App\Models\ImageLibrary::storeImage(
                $image,
                $request->ip(),
                null, // thread_id will be set after thread creation
                null, // post_id
                $request->boolean('dither', false)
            );

            $threadData['image_path'] = $libraryImage->file_path;
            $threadData['image_filename'] = $libraryImage->original_name;
        }

        try {
            $thread = Thread::create($threadData);

            // Update the library image with thread reference if image was uploaded
            if ($request->hasFile('image') && isset($libraryImage)) {
                $libraryImage->update([
                    'first_thread_id' => $thread->id,
                ]);
                // Award initial points for image usage in thread
                $libraryImage->awardPoW($this->calculateRealImagePoW($libraryImage, 'thread'));
            }

            return redirect("/$board/{$thread->id}");
            
        } catch (\Exception $e) {
            Log::error('Thread creation failed', [
                'board' => $board,
                'error' => $e->getMessage()
            ]);
            
            return redirect("/$board")
                ->withErrors(['database' => 'Failed to save thread: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function storeReply(Request $request, $board, $threadId)
    {
        // Log reply submission
        Log::info('Reply submission received', [
            'board' => $board,
            'thread_id' => $threadId,
            'content_length' => strlen($request->input('content', '')),
            'has_image' => $request->hasFile('image'),
            'pow_nonce' => $request->input('pow_nonce'),
            'pow_hash' => $request->input('pow_hash') ? substr($request->input('pow_hash'), 0, 16) . '...' : null,
            'pow_challenge_id' => $request->input('pow_challenge_id'),
            'request_method' => $request->method(),
            'user_agent' => substr($request->userAgent(), 0, 100)
        ]);

        $request->validate([
            'content' => 'required|max:5000',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string|size:32'
        ]);

        // Handle both board codes (gen) and board names (General)
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        $thread = Thread::with('bitcoinUser')->findOrFail($threadId);

        // Generate proper challenge data and verify PoW for reply
        $challengeData = "reply:{$boardModel->code}:{$threadId}:{$request->pow_challenge_id}";

        $verification = Thread::verifyProofOfWork(
            $challengeData,
            $request->pow_nonce,
            $request->pow_hash,
            '21e' // Intermediate difficulty for replies
        );

        if (!$verification['valid']) {
            Log::error('Reply PoW verification failed', [
                'error' => $verification['error'],
                'board' => $board,
                'thread_id' => $threadId,
                'challenge_data' => $challengeData,
                'nonce' => $request->pow_nonce,
                'submitted_hash' => $request->pow_hash
            ]);
            return back()->withErrors(['pow' => 'Proof of work verification failed: ' . $verification['error']])->withInput();
        }

        // Get authenticated user or create anonymous user
        $userId = session('bitcoin_auth_id');
        $postAsAnonymous = $request->boolean('post_anonymous', false);

        if ($userId && !$postAsAnonymous) {
            $authorName = session('bitcoin_auth_user')->username;
            $finalUserId = $userId;
        } else {
            $authorName = 'Anonymous#' . substr(hash('sha256', $request->ip() . time()), 0, 8);
            $finalUserId = null;
        }

        $postData = [
            'thread_id' => $thread->id,
            'content' => $request->content,
            'user_id' => $finalUserId,
            'author_name' => $authorName,
            'parent_id' => null, // Simple replies, no nesting for now
            'pow_nonce' => $request->pow_nonce,
            'pow_hash' => $request->pow_hash,
            'pow_challenge_id' => $request->pow_challenge_id,
            'pow_pattern' => '21e8',
            'pow_difficulty' => 1.0,
            'pow_verified_at' => now(),
            'ip_address' => $request->ip(),
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip())
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Store in ImageLibrary (which handles deduplication)
            $libraryImage = \App\Models\ImageLibrary::storeImage(
                $image,
                $request->ip(),
                $threadId, // thread_id
                null,      // post_id will be set after post creation
                $request->boolean('dither', false)
            );

            $postData['image_path'] = $libraryImage->file_path;
            $postData['image_filename'] = $libraryImage->original_name;
        }

        // Use database transaction to ensure data is committed before redirect
        $post = \DB::transaction(function() use ($postData) {
            $post = Post::create($postData);
            
            // Force immediate save and ensure relationships are fresh
            $post->refresh();
            
            return $post;
        });

        // Update the library image with post reference if image was uploaded
        if ($request->hasFile('image') && isset($libraryImage)) {
            $libraryImage->update([
                'first_post_id' => $post->id,
            ]);
            // Award initial points for image usage in post
            $libraryImage->awardPoW($this->calculateRealImagePoW($libraryImage, 'post'));
        }

        // Log the created post data for debugging
        Log::info('Reply created', [
            'id' => $post->id,
            'parent_id' => $post->parent_id,
            'thread_id' => $post->thread_id,
            'content' => substr($post->content, 0, 50) . '...'
        ]);

        return redirect("/$board/$threadId")->with('reply_created', $post->id);
    }

    // User post management
    public function deleteUserPost(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);
        $userId = session('bitcoin_auth_id');

        // Check if user owns this post or is admin
        if ($post->user_id !== $userId && (!session('bitcoin_auth_user') || !session('bitcoin_auth_user')->is_admin)) {
            return back()->with('error', 'You can only delete your own posts');
        }

        $threadId = $post->thread_id;
        $boardCode = $post->thread->board->code ?? 'gen';

        $post->delete();

        return redirect("/$boardCode/$threadId")->with('success', 'Post deleted successfully');
    }

    public function deleteUserThread(Request $request, $threadId)
    {
        $thread = Thread::findOrFail($threadId);
        $userId = session('bitcoin_auth_id');

        // Check if user owns this thread or is admin
        if ($thread->user_id !== $userId && (!session('bitcoin_auth_user') || !session('bitcoin_auth_user')->is_admin)) {
            return back()->with('error', 'You can only delete your own threads');
        }

        $boardCode = $thread->board->code ?? 'gen';

        // Delete all posts in the thread first
        Post::where('thread_id', $threadId)->delete();
        $thread->delete();

        return redirect("/$boardCode")->with('success', 'Thread deleted successfully');
    }

    public function serveThreadImage($id)
    {
        $thread = Thread::findOrFail($id);
        
        if (!$thread->image_path) {
            abort(404);
        }
        
        $fullPath = storage_path('app/public/' . $thread->image_path);
        
        if (!file_exists($fullPath)) {
            abort(404);
        }
        
        return response()->file($fullPath);
    }
    
    public function servePostImage($id)
    {
        $post = Post::findOrFail($id);
        
        if (!$post->image_filename) {
            abort(404);
        }
        
        $fullPath = storage_path('app/public/forum/images/' . $post->image_filename);
        
        if (!file_exists($fullPath)) {
            abort(404);
        }
        
        return response()->file($fullPath);
    }

    /**
     * Calculate real PoW points based on actual image properties and usage context
     */
    private function calculateRealImagePoW($libraryImage, $context = 'thread')
    {
        // Real calculation based on image properties
        $basePoints = 1;

        // Award more points for larger files (computation intensive)
        if ($libraryImage->file_size > 1000000) { // > 1MB
            $basePoints += 2;
        } elseif ($libraryImage->file_size > 500000) { // > 500KB
            $basePoints += 1;
        }

        // Award points based on uniqueness (deduplication prevention)
        $duplicateCount = \App\Models\ImageLibrary::where('sha256_hash', $libraryImage->sha256_hash)
            ->where('id', '!=', $libraryImage->id)
            ->count();

        if ($duplicateCount == 0) {
            $basePoints += 2; // Unique image bonus
        }

        // Context-based multiplier
        $multiplier = $context === 'thread' ? 1.5 : 1.0;

        return (int) ($basePoints * $multiplier);
    }

}
