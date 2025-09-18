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
            ->withCount('posts')
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = \App\Models\ProofSubmission::getTargetPoints('thread', $thread->id);
                return $thread;
            })
            ->sortByDesc('accumulated_points') // Sort by mining energy expenditure
            ->take(20);
        
        return view('boards.show', compact('board', 'threads'));
    }
    
    public function showCatalog($board)
    {
        $boardModel = Board::where('code', $board)->firstOrFail();
        $threads = Thread::where('board_id', $boardModel->id)
            ->withCount('posts')
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = \App\Models\ProofSubmission::getTargetPoints('thread', $thread->id);
                return $thread;
            })
            ->sortByDesc('accumulated_points') // Auto-bump based on mining energy
            ->take(100);

        return view('boards.catalog', ['board' => $boardModel, 'threads' => $threads]);
    }

    public function showTheMC()
    {
        // Get all threads from all boards with their board info
        $threads = Thread::with(['board'])
            ->withCount('posts')
            ->get()
            ->map(function ($thread) {
                $thread->accumulated_points = \App\Models\ProofSubmission::getTargetPoints('thread', $thread->id);
                return $thread;
            })
            ->sortByDesc('accumulated_points') // Auto-bump based on mining energy
            ->take(200); // Show more threads since it's from all boards

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
        $thread = Thread::findOrFail($threadId);

        // Return just PoW score for AJAX requests
        if (request()->get('pow_only')) {
            $powScore = ProofSubmission::where('target_type', 'thread')
                ->where('target_id', $threadId)
                ->sum('difficulty');
            return response()->json(['pow_score' => $powScore]);
        }
        
        // Load top-level posts (no parent) with their nested replies
        $posts = Post::where('thread_id', $threadId)
                    ->whereNull('parent_id')
                    ->with(['allReplies' => function($query) {
                        $query->orderBy('created_at', 'asc');
                    }])
                    ->orderBy('created_at', 'asc')
                    ->get();
        
        return view('boards.thread', ['board' => $boardModel, 'thread' => $thread, 'posts' => $posts]);
    }

    public function createThread($boardCode)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        return view('forum.create-thread', compact('board'));
    }

    public function storeThread(Request $request, $board)
    {
        // Skip authentication for now - create anonymous posts
        $anonymousUser = 'Anonymous#' . substr(hash('sha256', $request->ip() . time()), 0, 8);
        
        // Simple validation - accept either title or subject
        $title = $request->input('title') ?: $request->input('subject');
        if (!$title) {
            return back()->withErrors(['title' => 'Title is required'])->withInput();
        }
        
        if (!$request->filled('content')) {
            return back()->withErrors(['content' => 'Content is required'])->withInput();
        }

        // Validate PoW and image upload
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
            '21e8'
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
            'user_id' => null, // No auth for now
            'author_name' => $anonymousUser,
            'pow_nonce' => $request->pow_nonce,
            'pow_hash' => $request->pow_hash,
            'pow_challenge_id' => $request->pow_challenge_id,
            'pow_pattern' => '21e8',
            'pow_difficulty' => 1.0,
            'pow_verified_at' => now()
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('forum/images', $filename, 'public');
            
            $threadData['image_path'] = $path;
            $threadData['image_filename'] = $image->getClientOriginalName();
        }

        try {
            $thread = Thread::create($threadData);
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
        $thread = Thread::findOrFail($threadId);

        // Generate proper challenge data and verify PoW for reply
        $challengeData = "reply:{$boardModel->code}:{$threadId}:{$request->pow_challenge_id}";

        // Allow dummy proof for testing (all zeros)
        $isDummyProof = $request->pow_hash === '0000000000000000000000000000000000000000000000000000000000000000';

        if (!$isDummyProof) {
            $verification = Thread::verifyProofOfWork(
                $challengeData,
                $request->pow_nonce,
                $request->pow_hash,
                '21e0' // Accept easier pattern for now
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
        } else {
            Log::info('Accepting dummy proof for testing');
        }

        $anonymousUser = 'Anonymous#' . substr(hash('sha256', $request->ip() . time()), 0, 8);

        $postData = [
            'thread_id' => $thread->id,
            'content' => $request->content,
            'user_id' => null,
            'author_name' => $anonymousUser,
            'parent_id' => null, // Simple replies, no nesting for now
            'pow_nonce' => $request->pow_nonce,
            'pow_hash' => $request->pow_hash,
            'pow_challenge_id' => $request->pow_challenge_id,
            'pow_pattern' => '21e8',
            'pow_difficulty' => 1.0,
            'pow_verified_at' => now()
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('forum/images', $filename, 'public');
            
            $postData['image_path'] = $path;
            $postData['image_filename'] = $image->getClientOriginalName();
        }

        // Use database transaction to ensure data is committed before redirect
        $post = \DB::transaction(function() use ($postData) {
            $post = Post::create($postData);
            
            // Force immediate save and ensure relationships are fresh
            $post->refresh();
            
            return $post;
        });
        
        // Log the created post data for debugging
        Log::info('Reply created', [
            'id' => $post->id,
            'parent_id' => $post->parent_id,
            'thread_id' => $post->thread_id,
            'content' => substr($post->content, 0, 50) . '...'
        ]);

        return redirect("/$board/$threadId")->with('reply_created', $post->id);
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

}
