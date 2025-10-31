<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;
use App\Models\ProofOfWork;
use App\Models\Thread;
use App\Services\ChallengeVerifier;
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
                ->orderBy('bumped_at', 'desc')
                ->take(20)
                ->get()
                ->map(function ($thread) {
                    // Use consistent real-time calculation
                    $thread->accumulated_points = $thread->bump_score + \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');

                    return $thread;
                });
        });

        return view('boards.show', compact('board', 'threads'));
    }

    public function showCatalog($board)
    {
        // Handle both board codes (gen) and board names (General)
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        $threads = Thread::where('board_id', $boardModel->id)
            ->with(['bitcoinUser', 'proofOfWork'])
            ->withCount('posts')
            ->orderBy('bumped_at', 'desc')
            ->take(100)
            ->get()
            ->map(function ($thread) {
                // Use same calculation as thread view for consistency
                $thread->accumulated_points = $thread->bump_score + \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');

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
                // Use consistent real-time calculation
                $threadPoW = \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');
                $thread->accumulated_points = $thread->bump_score + $threadPoW + ($thread->pow_difficulty ?? 0);

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
            $powScore = ProofOfWork::where('thread_id', $thread->id)
                ->sum('points');

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

        // Note: Posts now have getAccumulatedPointsAttribute() accessor that calculates real PoW points

        return view('boards.thread', ['board' => $boardModel, 'thread' => $thread, 'posts' => $posts]);
    }

    public function createThread($boardCode)
    {
        // Handle both board codes (gen) and board names (General)
        $board = Board::where('code', $boardCode)
            ->orWhere('name', $boardCode)
            ->firstOrFail();

        // Use doodle-specific view for /ddl/
        if ($board->code === 'ddl') {
            return view('forum.create-doodle', compact('board'));
        }

        return view('forum.create-thread', compact('board'));
    }

    // Simplified thread creation without POW
    public function storeThreadNoPOW(Request $request, $board)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:200|min:3',
                'content' => 'required|string|max:5000|min:5',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                'image_hash' => 'nullable|string|size:64|regex:/^[a-f0-9]{64}$/',
                'post_anonymous' => 'boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        // Require thread image
        if (!$request->hasFile('image') && !$request->filled('image_hash')) {
            return back()->withErrors(['image' => 'Thread image is required.'])->withInput();
        }

        if ($request->hasFile('image') && $request->filled('image_hash')) {
            return back()->withErrors(['image' => 'Please provide either an image upload OR an image hash, not both.'])->withInput();
        }

        // Handle both board codes (gen) and board names (General)
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();

        // Get authenticated user or create anonymous user
        $userId = session('bitcoin_auth_id');
        $postAsAnonymous = $validated['post_anonymous'] ?? false;

        if ($userId && !$postAsAnonymous) {
            $authorName = e(session('bitcoin_auth_user')->username ?? 'User');
            $finalUserId = $userId;
        } else {
            $authorName = 'Anonymous#'.substr(hash('sha256', $request->ip().time()), 0, 8);
            $finalUserId = null;
        }

        $threadData = [
            'board_id' => $boardModel->id,
            'title' => e($validated['title']),
            'content' => $request->content,
            'user_id' => $finalUserId,
            'author_name' => $authorName,
            'ip_address' => $request->ip(),
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip()),
        ];

        // Handle image upload or existing hash
        if ($request->hasFile('image')) {
            $imageIndexingService = new ImageIndexingService;
            $imageResult = $imageIndexingService->processAndIndexImage(
                $request->file('image'),
                null,
                null,
                $request->ip()
            );

            if (!$imageResult['success']) {
                return back()->withErrors(['image' => 'Image processing failed: '.$imageResult['error']])->withInput();
            }

            $threadData['image_path'] = $imageResult['file_path'];
            $threadData['image_filename'] = pathinfo($imageResult['file_path'], PATHINFO_BASENAME);
            $threadData['image_hash'] = $imageResult['hash'];
            
        } elseif ($request->filled('image_hash')) {
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

            // Update board activity
            $boardModel->touch();
            $boardModel->updateActivityOrder($thread->id);

            // Update user stats if logged in
            if ($finalUserId) {
                $user = session('bitcoin_auth_user');
                if ($user) {
                    $user->increment('total_posts');
                    $user->increment('weekly_posts');
                }
            }

            return redirect("/{$boardModel->code}/thread/{$thread->id}")
                ->with('success', 'Thread created successfully!');
        } catch (\Exception $e) {
            Log::error('Thread creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to create thread. Please try again.'])->withInput();
        }
    }

    public function storeThread(Request $request, $board)
    {
        Log::info('storeThread: Request received.', ['request' => $request->all()]);

        Log::info('storeThread: Entered method.');

        // Rate limiting check for thread creation
        $userId = session('bitcoin_auth_id');
        $ipAddress = $request->ip();
        
        if ($userId) {
            $recentThreads = Thread::where('user_id', $userId)
                ->where('created_at', '>', now()->subMinutes(5))
                ->count();
                
            if ($recentThreads >= 3) {
                Log::warning('THREAD CREATION RATE LIMIT EXCEEDED', [
                    'user_id' => $userId,
                    'count' => $recentThreads
                ]);
                return back()->withErrors(['error' => 'Rate limit: Maximum 3 threads per 5 minutes.'])->withInput();
            }
        }
        
        // IP-based rate limiting
        $ipThreads = Thread::where('ip_address', $ipAddress)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();
            
        if ($ipThreads >= 5) {
            Log::warning('IP THREAD CREATION RATE LIMIT EXCEEDED', [
                'ip' => $ipAddress,
                'count' => $ipThreads
            ]);
            return back()->withErrors(['error' => 'Rate limit: Maximum 5 threads per 5 minutes per IP.'])->withInput();
        }

        // Log thread creation for monitoring (production-safe)
        Log::info('Thread creation attempt', [
            'board' => $board,
            'has_required_fields' => $request->has(['title', 'content', 'pow_hash']),
            'user_authenticated' => (bool) session('bitcoin_auth_id')
        ]);

        // Get board model for special board rules
        $boardModel = Board::where('code', $board)->firstOrFail();
        
        // Special validation for /i/ Images board - images required, no text content
        if ($boardModel->code === 'i') {
            $validated = $request->validate([
                'title' => 'nullable|string|max:200',
                'image' => 'required_without:image_hash|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                'image_hash' => 'required_without:image|string|size:64|regex:/^[a-f0-9]{64}$/',
                'pow_nonce' => 'required|integer|min:0',
                'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
                'pow_challenge_id' => 'required|string',
                'post_anonymous' => 'boolean',
            ]);
            
            // Set defaults for /i/ board - title or default, empty content
            $validated['title'] = $validated['title'] ?? '[Image]';
            $validated['content'] = ''; // No text content allowed on /i/
            
            Log::info('storeThread: /i/ board validation successful - image only mode.');
        } elseif ($boardModel->code === 'ddl') {
            // Special validation for /ddl/ Doodles board - subjects and doodles only
            $validated = $request->validate([
                'title' => 'required|string|max:200|min:3',
                'image' => 'required_without:image_hash|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                'image_hash' => 'required_without:image|string|size:64|regex:/^[a-f0-9]{64}$/',
                'pow_nonce' => 'required|integer|min:0',
                'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
                'pow_challenge_id' => 'required|string',
                'post_anonymous' => 'boolean',
            ]);
            
            // Set empty content for doodles - only subject (title) and doodle image
            $validated['content'] = ''; // No text content, just subject and doodle
            
            Log::info('storeThread: /ddl/ doodles board validation successful - subject and doodle only mode.');
        } else {
            // Comprehensive input validation - image OR image_hash required
            Log::info('storeThread: Validating request...');
            $validated = $request->validate([
                'title' => 'required|string|max:200|min:3',
                'content' => 'required|string|max:5000|min:5',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                'image_hash' => 'nullable|string|size:64|regex:/^[a-f0-9]{64}$/',
                'pow_nonce' => 'required|integer|min:0',
                'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
                'pow_challenge_id' => 'required|string',
                'post_anonymous' => 'boolean',
            ]);
            Log::info('storeThread: Validation successful.');
        }
        
        // Validation successful - continue with thread creation

        // Image validation check

        // Image is optional - removed mandatory image requirement
        // Legacy validation commented out to make images optional
        /*
        if (!$request->hasFile('image') && !$request->filled('image_hash')) {
            Log::error('Image validation failed: no image or hash provided');
            return back()->withErrors(['image' => 'Either upload an image or provide an image hash from the library.'])->withInput();
        }
        */

        // Validate that both are not provided simultaneously
        if ($request->hasFile('image') && $request->filled('image_hash')) {
            Log::error('Image validation failed: both image and hash provided');
            return back()->withErrors(['image' => 'Please provide either an image upload OR an image hash, not both.'])->withInput();
        }

        // Handle both board codes (gen) and board names (General)
        Log::info('storeThread: Finding board model...');
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        Log::info('storeThread: Board model found.', ['board_id' => $boardModel->id]);

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
        Log::info('storeThread: User identified.', ['user_id' => $finalUserId, 'author_name' => $authorName]);

        $title = $validated['title']; // Use raw title for PoW verification, escape later for storage
        $content = e($validated['content']);

        // Use new challenge-based verification system
        Log::info('storeThread: Verifying challenge...');
        $verifier = new ChallengeVerifier();
        $verificationResult = $verifier->verifyChallenge(
            $validated['pow_challenge_id'],
            $validated['pow_nonce'],
            $validated['pow_hash']
        );

        if (!$verificationResult['valid']) {
            Log::error('Thread challenge verification failed', [
                'error' => $verificationResult['error'],
                'board' => $board,
                'title' => $title,
                'challenge_id' => $validated['pow_challenge_id'],
                'nonce' => $validated['pow_nonce'],
                'submitted_hash' => $validated['pow_hash'],
            ]);

            return back()->withErrors(['pow' => 'Proof of work verification failed: '.$verificationResult['error']])->withInput();
        }
        Log::info('storeThread: Challenge verification successful.');

        // Store challenge reference but don't mark as used yet
        $challenge = $verificationResult['challenge'];

        Log::info('storeThread: Preparing thread data...');
        $threadData = [
            'board_id' => $boardModel->id,
            'title' => e($title), // Escape title for database storage
            'content' => $request->content,
            'user_id' => $finalUserId,
            'author_name' => $authorName,
            'pow_nonce' => $request->pow_nonce,
            'pow_hash' => $request->pow_hash,
            'pow_challenge_id' => $request->pow_challenge_id,
            'pow_pattern' => $challenge->difficulty,
            'pow_difficulty' => $this->calculatePoWPoints($request->pow_hash, $challenge->difficulty),
            'pow_verified_at' => now(),
            'ip_address' => $request->ip(),
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip()),
        ];
        Log::info('storeThread: Thread data prepared.', ['thread_data' => $threadData]);

        // Handle image upload or existing hash
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            Log::info('storeThread: Processing image upload...');
            // New image upload
            $imageIndexingService = new ImageIndexingService;
            $imageResult = $imageIndexingService->processAndIndexImage(
                $request->file('image'),
                null, // thread_id will be set after thread creation
                null, // post_id
                $request->ip()
            );

            if (! $imageResult['success']) {
                Log::error('storeThread: Image processing failed.', ['error' => $imageResult['error']]);
                return back()->withErrors(['image' => 'Image processing failed: '.$imageResult['error']])->withInput();
            }

            $threadData['image_path'] = $imageResult['file_path'];
            $threadData['image_filename'] = pathinfo($imageResult['file_path'], PATHINFO_BASENAME);
            $threadData['image_hash'] = $imageResult['hash'];
            Log::info('storeThread: Image processing successful.');
            
        } elseif ($request->filled('image_hash')) {
            Log::info('storeThread: Using existing image hash...');
            // Using existing image hash from library
            $existingImage = \App\Models\ImageLibrary::where('hash', $request->image_hash)->first();
            
            if (!$existingImage) {
                Log::error('storeThread: Image hash not found in library.');
                return back()->withErrors(['image_hash' => 'Image hash not found in library.'])->withInput();
            }

            $threadData['image_path'] = $existingImage->file_path;
            $threadData['image_filename'] = $existingImage->filename;
            $threadData['image_hash'] = $existingImage->hash;
            Log::info('storeThread: Existing image hash found.');
        }

        Log::info('Challenge verification passed, creating thread', [
            'challenge_id' => $challenge->id,
            'challenge_token' => $challenge->token,
            'submitted_hash' => $validated['pow_hash'],
            'difficulty' => $challenge->difficulty,
            'pattern_check' => str_starts_with(strtolower($validated['pow_hash']), strtolower($challenge->difficulty))
        ]);

try {
            $thread = Thread::create($threadData);
            Log::info('storeThread: Thread created successfully in database.', ['thread_id' => $thread->id]);

            // Create ProofOfWork record regardless of user status
            if ($request->pow_hash) {
                Log::info('storeThread: Creating ProofOfWork record...');
                $powPoints = $this->calculatePoWPoints($request->pow_hash, $challenge->difficulty);

                $proofOfWork = \App\Models\ProofOfWork::create([
                    'challenge_id' => $challenge->id,
                    'user_id' => $finalUserId, // This can be null for anonymous users
                    'thread_id' => $thread->id,
                    'hash' => $request->pow_hash,
                    'nonce' => $request->pow_nonce,
                    'data' => json_encode($challenge->canonical_payload),
                    'pattern' => $challenge->difficulty,
                    'points' => $powPoints,
                    'verified_at' => now(),
                    'ip_address' => $request->ip(),
                ]);
                Log::info('storeThread: ProofOfWork record created.', ['pow_id' => $proofOfWork->id]);

                // Award points to user only if they're logged in
                if ($finalUserId) {
                    $user = \App\Models\BitcoinAuth::find($finalUserId);
                    if ($user) {
                        $user->awardMiningPoints($powPoints);
                        Log::info('storeThread: Awarded points to user.', ['user_id' => $user->id, 'points' => $powPoints]);
                        
                        // Store points data in session for frontend to pick up
                        session()->flash('points_awarded', $powPoints);
                        session()->flash('total_points', $user->fresh()->total_pow_points);
                    }
                }
            }

            // Update the library image with thread reference
            $imageHash = $threadData['image_hash'] ?? null;
            if ($imageHash) {
                Log::info('storeThread: Updating image library with thread reference...');
                $imageLibraryRecord = \App\Models\ImageLibrary::where('hash', $imageHash)->first();
                if ($imageLibraryRecord && !$imageLibraryRecord->first_thread_id) {
                    $imageLibraryRecord->update([
                        'first_thread_id' => $thread->id,
                    ]);
                    Log::info('storeThread: Image library updated.');
                }
            }

            // Clear board cache so new thread appears immediately
            $cacheKey = "board_threads_{$boardModel->id}";
            Log::info('storeThread: Clearing cache...', ['cache_key' => $cacheKey]);
            \Cache::forget($cacheKey);
            Log::info('storeThread: Cache cleared.');
            
            Log::info('Thread created successfully, cache cleared', [
                'thread_id' => $thread->id,
                'cache_key_cleared' => $cacheKey
            ]);

            // Only mark challenge as used after successful thread creation
            $challenge->markAsUsed();
            Log::info('storeThread: Challenge marked as used.');

            Log::info('storeThread: Redirecting to thread page.');
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

    // Simplified reply without POW
    public function storeReplyNoPOW(Request $request, $board, $threadId)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:5000|min:5',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                'image_hash' => 'nullable|string|size:64|regex:/^[a-f0-9]{64}$/',
                'post_anonymous' => 'boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        if ($request->hasFile('image') && $request->filled('image_hash')) {
            return back()->withErrors(['image' => 'Please provide either an image upload OR an image hash, not both.'])->withInput();
        }

        // Find thread
        $thread = Thread::findOrFail($threadId);
        
        if ($thread->is_locked) {
            return back()->withErrors(['thread' => 'This thread is locked and cannot accept new replies.']);
        }

        // Get authenticated user or create anonymous user
        $userId = session('bitcoin_auth_id');
        $postAsAnonymous = $validated['post_anonymous'] ?? false;

        if ($userId && !$postAsAnonymous) {
            $authorName = e(session('bitcoin_auth_user')->username ?? 'User');
            $finalUserId = $userId;
        } else {
            $authorName = 'Anonymous#'.substr(hash('sha256', $request->ip().time()), 0, 8);
            $finalUserId = null;
        }

        $postData = [
            'thread_id' => $threadId,
            'content' => $validated['content'],
            'user_id' => $finalUserId,
            'author_name' => $authorName,
            'parent_id' => null,
            'ip_address' => $request->ip(),
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip()),
        ];

        // Handle image upload or existing hash
        if ($request->hasFile('image')) {
            $imageIndexingService = new ImageIndexingService;
            $imageResult = $imageIndexingService->processAndIndexImage(
                $request->file('image'),
                null,
                null,
                $request->ip()
            );

            if (!$imageResult['success']) {
                return back()->withErrors(['image' => 'Image processing failed: '.$imageResult['error']])->withInput();
            }

            $postData['image_path'] = $imageResult['file_path'];
            $postData['image_filename'] = pathinfo($imageResult['file_path'], PATHINFO_BASENAME);
            $postData['image_hash'] = $imageResult['hash'];
            
        } elseif ($request->filled('image_hash')) {
            $existingImage = \App\Models\ImageLibrary::where('hash', $request->image_hash)->first();
            
            if (!$existingImage) {
                return back()->withErrors(['image_hash' => 'Image hash not found in library.'])->withInput();
            }

            $postData['image_path'] = $existingImage->file_path;
            $postData['image_filename'] = $existingImage->filename;
            $postData['image_hash'] = $existingImage->hash;
        }

        try {
            $post = Post::create($postData);

            // Bump thread
            $thread->bumped_at = now();
            $thread->bump_score = min(10, $thread->bump_score + 0.5);
            $thread->reply_count++;
            $thread->save();

            // Update user stats if logged in
            if ($finalUserId) {
                $user = session('bitcoin_auth_user');
                if ($user) {
                    $user->increment('total_posts');
                    $user->increment('weekly_posts');
                }
            }

            return redirect("/{$board}/thread/{$threadId}#post-{$post->id}")
                ->with('success', 'Reply posted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Reply creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to post reply. Please try again.'])->withInput();
        }
    }

    public function storeReply(Request $request, $board, $threadId)
    {
        // Use the new PoW system for replies - redirect to API-based flow
        return $this->storeReplyWithNewPoW($request, $board, $threadId);
    }
    
    private function storeReplyWithNewPoW(Request $request, $board, $threadId)
    {
        // Rate limiting check for replies
        $userId = session('bitcoin_auth_id');
        $ipAddress = $request->ip();
        
        if ($userId) {
            $recentReplies = Post::where('user_id', $userId)
                ->where('created_at', '>', now()->subMinute())
                ->count();
                
            if ($recentReplies >= 10) {
                Log::warning('REPLY RATE LIMIT EXCEEDED', [
                    'user_id' => $userId,
                    'count' => $recentReplies
                ]);
                return back()->withErrors(['error' => 'Rate limit: Maximum 10 replies per minute.'])->withInput();
            }
        }
        
        // IP-based rate limiting
        $ipReplies = Post::where('ip_address', $ipAddress)
            ->where('created_at', '>', now()->subMinute())
            ->count();
            
        if ($ipReplies >= 15) {
            Log::warning('IP REPLY RATE LIMIT EXCEEDED', [
                'ip' => $ipAddress,
                'count' => $ipReplies
            ]);
            return back()->withErrors(['error' => 'Rate limit: Maximum 15 replies per minute per IP.'])->withInput();
        }

        // Log reply creation attempt
        Log::info('Reply creation attempt (New PoW)', [
            'board' => $board,
            'thread_id' => $threadId,
            'user_authenticated' => (bool) session('bitcoin_auth_id')
        ]);

        // Get board model for special board rules
        $boardModel = Board::where('code', $board)->firstOrFail();
        
        // Full validation including PoW fields
        try {
            // Special validation for /i/ Images board - images required, no text content
            if ($boardModel->code === 'i') {
                $validated = $request->validate([
                    'reply_content' => 'nullable|max:0', // No content allowed on /i/
                    'image' => 'required_without:image_hash|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                    'image_hash' => 'required_without:image|string|size:64|regex:/^[a-f0-9]{64}$/',
                    'pow_nonce' => 'required|integer|min:0',
                    'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
                    'pow_challenge_id' => 'required|string',
                    'post_anonymous' => 'boolean',
                ]);
                
                // Override content to be empty for /i/ board
                $validated['reply_content'] = '';
                
                Log::info('Reply: /i/ board validation successful - image required, no content.');
            } elseif ($boardModel->code === 'ddl') {
                // Special validation for /ddl/ Doodles board - doodles required, no text content
                $validated = $request->validate([
                    'reply_content' => 'nullable|max:0', // No text content allowed on /ddl/
                    'image' => 'required_without:image_hash|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                    'image_hash' => 'required_without:image|string|size:64|regex:/^[a-f0-9]{64}$/',
                    'pow_nonce' => 'required|integer|min:0',
                    'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
                    'pow_challenge_id' => 'required|string',
                    'post_anonymous' => 'boolean',
                ]);
                
                // Override content to be empty for doodles board
                $validated['reply_content'] = '';
                
                Log::info('Reply: /ddl/ doodles board validation successful - doodle required, no text content.');
            } else {
                $validated = $request->validate([
                    'reply_content' => 'required|string|max:5000|min:5',
                    'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,webm,mp4,mov,avi,svg,bmp,tiff,avif,heic,heif|max:25600',
                    'image_hash' => 'nullable|string|size:64|regex:/^[a-f0-9]{64}$/',
                    'pow_nonce' => 'required|integer|min:0',
                    'pow_hash' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
                    'pow_challenge_id' => 'required|string',
                    'post_anonymous' => 'boolean',
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Reply validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        // Validate that both image and hash are not provided simultaneously
        if ($request->hasFile('image') && $request->filled('image_hash')) {
            Log::error('Image validation failed: both image and hash provided');
            return back()->withErrors(['image' => 'Please provide either an image upload OR an image hash, not both.'])->withInput();
        }

        // Handle both board codes (gen) and board names (General)
        $boardModel = Board::where('code', $board)
            ->orWhere('name', $board)
            ->firstOrFail();
        $thread = Thread::with('bitcoinUser')->findOrFail($threadId);

        // Verify PoW challenge
        Log::info('Reply: Verifying challenge...');
        $verifier = new ChallengeVerifier();
        $verificationResult = $verifier->verifyChallenge(
            $validated['pow_challenge_id'],
            $validated['pow_nonce'],
            $validated['pow_hash']
        );

        if (!$verificationResult['valid']) {
            Log::error('Reply challenge verification failed', [
                'error' => $verificationResult['error'],
                'board' => $board,
                'thread_id' => $threadId,
                'challenge_id' => $validated['pow_challenge_id'],
                'nonce' => $validated['pow_nonce'],
                'submitted_hash' => $validated['pow_hash'],
            ]);

            return back()->withErrors(['pow' => 'Proof of work verification failed: '.$verificationResult['error']])->withInput();
        }
        Log::info('Reply: Challenge verification successful.');
        
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

        $postData = [
            'thread_id' => $thread->id,
            'content' => $validated['reply_content'],
            'user_id' => $finalUserId,
            'author_name' => $authorName,
            'parent_id' => null, // Simple replies, no nesting for now
            'ip_address' => $request->ip(),
            'country_flag' => \App\Helpers\GeoHelper::getCountryFlag($request->ip()),
            'pow_nonce' => $validated['pow_nonce'],
            'pow_hash' => $validated['pow_hash'],
            'pow_challenge_id' => $validated['pow_challenge_id'],
            'pow_pattern' => $verificationResult['challenge']->difficulty,
            'pow_difficulty' => $this->calculatePoWPoints($validated['pow_hash'], $verificationResult['challenge']->difficulty),
            'pow_verified_at' => now(),
        ];

        // Handle image upload or existing hash
        if ($request->hasFile('image')) {
            // New image upload
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
            
        } elseif ($request->filled('image_hash')) {
            // Using existing image hash from library
            $existingImage = \App\Models\ImageLibrary::where('hash', $request->image_hash)->first();
            
            if (!$existingImage) {
                return back()->withErrors(['image_hash' => 'Image hash not found in library.'])->withInput();
            }

            $postData['image_path'] = $existingImage->file_path;
            $postData['image_filename'] = $existingImage->filename;
            $postData['image_hash'] = $existingImage->hash;
        }

        // Mark challenge as used
        $challenge = $verificationResult['challenge'];
        $challenge->markAsUsed();

        // Use database transaction to ensure data is committed before redirect
        try {
            $post = \DB::transaction(function () use ($postData, $challenge, $finalUserId, $validated) {
                $post = Post::create($postData);

                // Create ProofOfWork record for reply
                $powPoints = $this->calculatePoWPoints($validated['pow_hash'], $challenge->difficulty);

                $proofOfWork = \App\Models\ProofOfWork::create([
                    'challenge_id' => $challenge->id,
                    'user_id' => $finalUserId, // Can be null for anonymous users
                    'post_id' => $post->id, // Associate with the reply post
                    'hash' => $validated['pow_hash'],
                    'nonce' => $validated['pow_nonce'],
                    'data' => json_encode($challenge->canonical_payload),
                    'pattern' => $challenge->difficulty,
                    'points' => $powPoints,
                    'verified_at' => now(),
                    'ip_address' => request()->ip(),
                ]);

                // Award points to user if logged in
                if ($finalUserId) {
                    $user = \App\Models\BitcoinAuth::find($finalUserId);
                    if ($user) {
                        $user->awardMiningPoints($powPoints);
                        
                        // Store points data in session for frontend to pick up
                        session()->flash('points_awarded', $powPoints);
                        session()->flash('total_points', $user->fresh()->total_pow_points);
                    }
                }

                // Force immediate save and ensure relationships are fresh
                $post->refresh();

                return $post;
            });

            // Update thread bump score and dispatch event
            $powPoints = $this->calculatePoWPoints($validated['pow_hash'], $verificationResult['challenge']->difficulty);
            $thread->increment('posts_count');
            $thread->increment('bump_score', $powPoints);
            $thread->touch('bumped_at');

            event(new \App\Events\ThreadBumped($thread->fresh()));


            // Log the created post data for debugging
            Log::info('Reply created successfully with PoW', [
                'id' => $post->id,
                'parent_id' => $post->parent_id,
                'thread_id' => $post->thread_id,
                'content' => substr($post->content, 0, 50).'...',
            ]);

            return redirect("/$board/thread/$threadId#post-{$post->id}")
                ->with('success', 'Reply posted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Reply creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to post reply: '.$e->getMessage()])->withInput();
        }
    }

    // User post management
    public function deleteUserPost(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);
        $userId = session('bitcoin_auth_id');
        $authUser = session('bitcoin_auth_user');
        $canModerate = $authUser && ($authUser->is_admin || $authUser->is_moderator);

        // Check if user owns this post or has moderation privileges
        if ($post->user_id !== $userId && ! $canModerate) {
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
        $authUser = session('bitcoin_auth_user');
        $canModerate = $authUser && ($authUser->is_admin || $authUser->is_moderator);

        // Check if user owns this thread or has moderation privileges
        if ($thread->user_id !== $userId && ! $canModerate) {
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

        // Detect fallback/dummy values and give zero points
        if ($hash === '21e8000000000000000000000000000000000000000000000000000000000000') {
            return 0;
        }
        
        // Detect suspiciously regular hashes (too many zeros)
        $zeroCount = substr_count($hash, '0');
        if ($zeroCount > 50) {
            Log::warning('SUSPICIOUS HASH IN THREAD CREATION', [
                'hash' => $hash,
                'zero_count' => $zeroCount
            ]);
            return 0;
        }

        // Base points for different patterns
        $pointMap = [
            '21' => 0.1,
            '21e' => 0.5,
            '21e8' => 100,
            '21e80' => 500,
            '21e800' => 2500,
        ];

        if (isset($pointMap[$expectedPattern])) {
            $basePoints = $pointMap[$expectedPattern];
        } else {
            $basePoints = 0.1;
        }

        // Bonus for exceeding expected difficulty
        if (str_starts_with($hash, '21e800') && $expectedPattern !== '21e800') {
            $basePoints *= 25;
        } elseif (str_starts_with($hash, '21e80') && ! in_array($expectedPattern, ['21e80', '21e800'])) {
            $basePoints *= 5;
        } elseif (str_starts_with($hash, '21e8') && ! in_array($expectedPattern, ['21e8', '21e80', '21e800'])) {
            $basePoints *= 2;
        }

        // Special rare patterns
        if (str_starts_with($hash, '000')) {
            $basePoints *= 10;
        } elseif (str_starts_with($hash, '666')) {
            $basePoints *= 15;
        } elseif (str_contains($hash, 'dead')) {
            $basePoints *= 8;
        }

        return max(0.1, $basePoints);
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
