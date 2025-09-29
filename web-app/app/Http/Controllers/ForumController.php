<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;
use App\Models\ProofSubmission;
use App\Models\Thread;
use App\Services\ImageIndexingService;
use Illuminate\Http\Request;
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

        // Cache thread data for 5 minutes
        $cacheKey = "board_threads_{$board->id}";
        $threads = \Cache::remember($cacheKey, 300, function () use ($board) {
            return Thread::where('board_id', $board->id)
                ->with(['bitcoinUser', 'proofOfWork'])
                ->withCount('posts')
                ->withSum('proofOfWork as pow_points', 'points')
                ->orderByDesc(\DB::raw('bump_score + COALESCE(pow_points, 0)'))
                ->orderBy('bumped_at', 'desc')
                ->take(20)
                ->get()
                ->map(function ($thread) {
                    $thread->accumulated_points = $thread->bump_score + ($thread->pow_points ?? 0);

                    return $thread;
                });
        });

        return view('boards.show', compact('board', 'threads'));
    }

    public function showCatalog($board)
    {
        $boardModel = Board::where('code', $board)->firstOrFail();
        $threads = Thread::where('board_id', $boardModel->id)
            ->with(['bitcoinUser', 'proofOfWork'])
            ->withCount('posts')
            ->withSum('proofOfWork as pow_points', 'points')
            ->orderByDesc(\DB::raw('bump_score + COALESCE(pow_points, 0)'))
            ->orderBy('bumped_at', 'desc')
            ->take(100)
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = $thread->bump_score + ($thread->pow_points ?? 0);

                return $thread;
            });

        return view('boards.catalog', ['board' => $boardModel, 'threads' => $threads]);
    }

    public function showTheMC()
    {
        // Get all threads from all boards with their board info
        $threads = Thread::with(['board', 'bitcoinUser', 'proofOfWork'])
            ->withCount('posts')
            ->withSum('proofOfWork as pow_points', 'points')
            ->orderByDesc(\DB::raw('bump_score + COALESCE(pow_points, 0)'))
            ->orderBy('bumped_at', 'desc')
            ->take(200) // Show more threads since it's from all boards
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = $thread->bump_score + ($thread->pow_points ?? 0);

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

        // Calculate accumulated points for this thread
        $thread->accumulated_points = $thread->bump_score + \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');

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
                'allReplies' => function ($query) use ($sortBy) {
                    $query->with('bitcoinUser');
                    if ($sortBy === 'pow') {
                        $query->orderBy('pow_difficulty', 'desc')->orderBy('created_at', 'asc');
                    } else {
                        $query->orderBy('created_at', 'asc');
                    }
                },
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
        Log::info('=== THREAD CREATION ATTEMPT ===', [
            'board' => $board,
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'has_title' => $request->has('title'),
            'has_content' => $request->has('content'),
            'has_image' => $request->hasFile('image'),
            'has_pow_hash' => $request->has('pow_hash'),
            'session_data' => [
                'bitcoin_auth_id' => session('bitcoin_auth_id'),
                'authenticated' => auth()->check()
            ]
        ]);

        // Comprehensive input validation - image OR image_hash required
        $validated = $request->validate([
            'title' => 'required|string|max:200|min:3',
            'content' => 'required|string|max:5000|min:10',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
            'image_hash' => 'nullable|string|size:64|regex:/^[a-f0-9]{64}$/',
            'pow_nonce' => 'required|integer|min:0',
            'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
            'pow_challenge_id' => 'required|string|size:32|regex:/^[a-f0-9]{32}$/',
            'post_anonymous' => 'boolean',
        ]);

        // Validate that either image file OR image hash is provided
        if (!$request->hasFile('image') && !$request->filled('image_hash')) {
            return back()->withErrors(['image' => 'Either upload an image or provide an image hash from the library.'])->withInput();
        }

        // Validate that both are not provided simultaneously
        if ($request->hasFile('image') && $request->filled('image_hash')) {
            return back()->withErrors(['image' => 'Please provide either an image upload OR an image hash, not both.'])->withInput();
        }

        // Find the board by code with validation
        $boardModel = Board::where('code', $board)->firstOrFail();

        // Get authenticated user or create anonymous user
        $userId = session('bitcoin_auth_id');
        $postAsAnonymous = $validated['post_anonymous'] ?? false;

        if ($userId && ! $postAsAnonymous) {
            $authorName = e(session('bitcoin_auth_user')->username ?? 'User');
            $finalUserId = $userId;
        } else {
            $authorName = 'Anonymous#'.substr(hash('sha256', $request->ip().time()), 0, 8);
            $finalUserId = null;
        }

        $title = e($validated['title']);
        $content = e($validated['content']);

        // Generate proper challenge data and verify PoW
        $challengeData = "thread:{$boardModel->code}:{$title}:{$validated['pow_challenge_id']}";
        $verification = Thread::verifyProofOfWork(
            $challengeData,
            $validated['pow_nonce'],
            $validated['pow_hash'],
            '21e8' // Minimum 21e8 required for threads
        );

        if (! $verification['valid']) {
            Log::error('Thread PoW verification failed', [
                'error' => $verification['error'],
                'board' => $board,
                'title' => $title,
                'challenge_data' => $challengeData,
                'nonce' => $request->pow_nonce,
                'submitted_hash' => $request->pow_hash,
                'expected_pattern' => '21e8',
                'hash_starts_with' => substr($request->pow_hash, 0, 10),
                'calculated_hash' => hash('sha256', $challengeData.':'.$request->pow_nonce),
            ]);

            return back()->withErrors(['pow' => 'Proof of work verification failed: '.$verification['error']])->withInput();
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
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip()),
        ];

        // Handle image upload or existing hash
        if ($request->hasFile('image')) {
            // New image upload
            $imageIndexingService = new ImageIndexingService;
            $imageResult = $imageIndexingService->processAndIndexImage(
                $request->file('image'),
                null, // thread_id will be set after thread creation
                null, // post_id
                $request->ip()
            );

            if (! $imageResult['success']) {
                return back()->withErrors(['image' => 'Image processing failed: '.$imageResult['error']])->withInput();
            }

            $threadData['image_path'] = $imageResult['file_path'];
            $threadData['image_filename'] = pathinfo($imageResult['file_path'], PATHINFO_BASENAME);
            $threadData['image_hash'] = $imageResult['hash'];
            
        } elseif ($request->filled('image_hash')) {
            // Using existing image hash from library
            $existingImage = \App\Models\ImageLibrary::where('hash', $request->image_hash)->first();
            
            if (!$existingImage) {
                return back()->withErrors(['image_hash' => 'Image hash not found in library.'])->withInput();
            }

            $threadData['image_path'] = $existingImage->file_path;
            $threadData['image_filename'] = $existingImage->filename;
            $threadData['image_hash'] = $existingImage->hash;
        }

        try {
            $thread = Thread::create($threadData);

            // Create ProofOfWork record and award points to user
            if ($finalUserId && $request->pow_hash) {
                $powPoints = $this->calculatePoWPoints($request->pow_hash, '21e8');

                $proofOfWork = \App\Models\ProofOfWork::create([
                    'user_id' => $finalUserId,
                    'thread_id' => $thread->id,
                    'hash' => $request->pow_hash,
                    'nonce' => $request->pow_nonce,
                    'data' => $challengeData,
                    'pattern' => '21e8',
                    'points' => $powPoints,
                    'verified_at' => now(),
                    'ip_address' => $request->ip(),
                ]);

                // Award points to user
                $user = \App\Models\BitcoinAuth::find($finalUserId);
                if ($user) {
                    $user->awardMiningPoints($powPoints);
                }
            }

            // Update the library image with thread reference
            $imageHash = $threadData['image_hash'] ?? null;
            if ($imageHash) {
                $imageLibraryRecord = \App\Models\ImageLibrary::where('hash', $imageHash)->first();
                if ($imageLibraryRecord && !$imageLibraryRecord->first_thread_id) {
                    $imageLibraryRecord->update([
                        'first_thread_id' => $thread->id,
                    ]);
                }
            }

            return redirect("/$board/{$thread->id}");

        } catch (\Exception $e) {
            Log::error('Thread creation failed', [
                'board' => $board,
                'error' => $e->getMessage(),
            ]);

            return redirect("/$board")
                ->withErrors(['database' => 'Failed to save thread: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function storeReply(Request $request, $board, $threadId)
    {
        // Debug logging
        Log::info('=== REPLY SUBMISSION DEBUG ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'board' => $board,
            'thread_id' => $threadId,
            'content' => $request->input('content'),
            'pow_nonce' => $request->input('pow_nonce'),
            'pow_hash' => $request->input('pow_hash'),
            'pow_challenge_id' => $request->input('pow_challenge_id'),
            'has_csrf' => $request->hasHeader('X-CSRF-TOKEN') || $request->has('_token'),
            'all_input' => $request->all(),
        ]);

        // Log reply submission
        Log::info('Reply submission received', [
            'board' => $board,
            'thread_id' => $threadId,
            'content_length' => strlen($request->input('content', '')),
            'has_image' => $request->hasFile('image'),
            'pow_nonce' => $request->input('pow_nonce'),
            'pow_hash' => $request->input('pow_hash') ? substr($request->input('pow_hash'), 0, 16).'...' : null,
            'pow_challenge_id' => $request->input('pow_challenge_id'),
            'request_method' => $request->method(),
            'user_agent' => substr($request->userAgent(), 0, 100),
        ]);

        $request->validate([
            'content' => 'required|max:5000',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string',
        ]);

        // Handle both board codes (gen) and board names (General)
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        $thread = Thread::with('bitcoinUser')->findOrFail($threadId);

        // Verify PoW (MANDATORY for all replies)
        $challengeData = "reply:{$boardModel->code}:{$threadId}:{$request->pow_challenge_id}";

        $verification = Thread::verifyProofOfWork(
            $challengeData,
            $request->pow_nonce,
            $request->pow_hash,
            '21e8' // Same difficulty as threads for replies
        );

        if (! $verification['valid']) {
            Log::error('Reply PoW verification failed', [
                'error' => $verification['error'],
                'board' => $board,
                'thread_id' => $threadId,
                'challenge_data' => $challengeData,
                'nonce' => $request->pow_nonce,
                'submitted_hash' => $request->pow_hash,
            ]);

            return back()->withErrors(['pow' => 'Proof of work verification failed: '.$verification['error']])->withInput();
        }

        // Get authenticated user or create anonymous user
        $userId = session('bitcoin_auth_id');
        $postAsAnonymous = $request->boolean('post_anonymous', false);

        if ($userId && ! $postAsAnonymous) {
            $authorName = session('bitcoin_auth_user')->username;
            $finalUserId = $userId;
        } else {
            $authorName = 'Anonymous#'.substr(hash('sha256', $request->ip().time()), 0, 8);
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
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip()),
        ];

        // Handle image upload using new ImageIndexingService
        if ($request->hasFile('image')) {
            $imageIndexingService = new ImageIndexingService;
            $imageResult = $imageIndexingService->processAndIndexImage(
                $request->file('image'),
                $threadId, // thread_id
                null,      // post_id will be set after post creation
                $request->ip()
            );

            if (! $imageResult['success']) {
                return back()->withErrors(['image' => 'Image processing failed: '.$imageResult['error']])->withInput();
            }

            $postData['image_path'] = $imageResult['file_path'];
            $postData['image_filename'] = pathinfo($imageResult['file_path'], PATHINFO_BASENAME);
            $postData['image_hash'] = $imageResult['hash'];
        }

        // Use database transaction to ensure data is committed before redirect
        $post = \DB::transaction(function () use ($postData) {
            $post = Post::create($postData);

            // Force immediate save and ensure relationships are fresh
            $post->refresh();

            return $post;
        });

        // Create ProofOfWork record and award points to user for replies
        if ($finalUserId && $request->pow_hash) {
            $powPoints = $this->calculatePoWPoints($request->pow_hash, '21e');
            $challengeData = "reply:{$boardModel->code}:{$threadId}:{$request->pow_challenge_id}";

            $proofOfWork = \App\Models\ProofOfWork::create([
                'user_id' => $finalUserId,
                'thread_id' => $threadId,
                'hash' => $request->pow_hash,
                'nonce' => $request->pow_nonce,
                'data' => $challengeData,
                'pattern' => '21e',
                'points' => $powPoints,
                'verified_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            // Award points to user
            $user = \App\Models\BitcoinAuth::find($finalUserId);
            if ($user) {
                $user->awardMiningPoints($powPoints);
            }
        }

        // Update the library image with post reference if image was uploaded
        if ($request->hasFile('image') && isset($imageResult)) {
            $imageLibraryRecord = \App\Models\ImageLibrary::where('hash', $imageResult['hash'])->first();
            if ($imageLibraryRecord && ! $imageLibraryRecord->first_post_id) {
                $imageLibraryRecord->update([
                    'first_post_id' => $post->id,
                ]);
            }
        }

        // Log the created post data for debugging
        Log::info('Reply created', [
            'id' => $post->id,
            'parent_id' => $post->parent_id,
            'thread_id' => $post->thread_id,
            'content' => substr($post->content, 0, 50).'...',
        ]);

        return redirect("/$board/$threadId")->with('reply_created', $post->id);
    }

    // User post management
    public function deleteUserPost(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);
        $userId = session('bitcoin_auth_id');

        // Check if user owns this post or is admin
        if ($post->user_id !== $userId && (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin)) {
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
        if ($thread->user_id !== $userId && (! session('bitcoin_auth_user') || ! session('bitcoin_auth_user')->is_admin)) {
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

        if (! $thread->image_path) {
            abort(404);
        }

        $fullPath = public_path($thread->image_path);

        if (! file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    public function servePostImage($id)
    {
        $post = Post::findOrFail($id);

        if (! $post->image_path) {
            abort(404);
        }

        $fullPath = public_path($post->image_path);

        if (! file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    /**
     * Calculate PoW points based on hash pattern difficulty
     */
    private function calculatePoWPoints($hash, $expectedPattern)
    {
        $hash = strtolower($hash);
        $expectedPattern = strtolower($expectedPattern);

        // Base points for different patterns
        $pointMap = [
            '21' => 0.1,      // Easy difficulty
            '21e' => 0.5,     // Medium difficulty
            '21e8' => 100,    // Hard difficulty
            '21e80' => 500,   // Very hard
            '21e800' => 2500, // Extreme
        ];

        // Check for exact pattern match first
        if (isset($pointMap[$expectedPattern])) {
            $basePoints = $pointMap[$expectedPattern];
        } else {
            $basePoints = 0.1; // Default minimum
        }

        // Bonus for exceeding expected difficulty
        if (str_starts_with($hash, '21e800') && $expectedPattern !== '21e800') {
            $basePoints *= 25; // Extreme bonus
        } elseif (str_starts_with($hash, '21e80') && ! in_array($expectedPattern, ['21e80', '21e800'])) {
            $basePoints *= 5; // High bonus
        } elseif (str_starts_with($hash, '21e8') && ! in_array($expectedPattern, ['21e8', '21e80', '21e800'])) {
            $basePoints *= 2; // Moderate bonus
        }

        // Special rare patterns
        if (str_starts_with($hash, '000')) {
            $basePoints *= 10; // Legendary
        } elseif (str_starts_with($hash, '666')) {
            $basePoints *= 15; // Cursed
        } elseif (str_contains($hash, 'dead')) {
            $basePoints *= 8; // Death hash
        }

        return max(0.1, $basePoints); // Minimum 0.1 points
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
