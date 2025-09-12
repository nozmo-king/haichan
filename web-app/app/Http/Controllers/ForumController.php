<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;
use App\Models\ProofOfWork;
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
        $board = Board::where('code', $board)->firstOrFail();
        $threads = Thread::where('board_id', $board->id)
            ->with(['proofOfWork']) // Eager load to prevent N+1 queries
            ->withCount('posts')
            ->withSum('proofOfWork', 'points') // Sum PoW points for sorting
            ->orderByDesc('proof_of_work_sum_points') // Sort by total PoW descending
            ->orderByDesc('created_at') // Secondary sort by creation date
            ->paginate(20);
        
        return view('boards.show', compact('board', 'threads'));
    }
    
    public function showCatalog($board)
    {
        $boardModel = Board::where('code', $board)->firstOrFail();
        $threads = Thread::where('board_id', $boardModel->id)
            ->with(['proofOfWork']) // Eager load
            ->withCount('posts')
            ->withSum('proofOfWork', 'points')
            ->orderByDesc('proof_of_work_sum_points')
            ->orderByDesc('created_at')
            ->paginate(100);
        
        return view('boards.catalog', ['board' => $boardModel, 'threads' => $threads]);
    }

    public function showThread($board, $threadId)
    {
        $boardModel = Board::where('code', $board)->firstOrFail();
        $thread = Thread::with(['proofOfWork'])
                       ->withSum('proofOfWork', 'points')
                       ->findOrFail($threadId);
        
        // Return just PoW score for AJAX requests
        if (request()->get('pow_only')) {
            return response()->json(['pow_score' => $thread->proof_of_work_sum_points ?? 0]);
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

        // Validate Proof of Work and image
        $request->validate([
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string|size:32',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192'
        ]);

        $boardModel = Board::where('code', $board)->firstOrFail();
        
        // Generate challenge data and verify PoW
        $challengeData = "thread:{$boardModel->code}:{$title}:{$request->pow_challenge_id}";
        $verification = Thread::verifyProofOfWork(
            $challengeData, 
            $request->pow_nonce, 
            $request->pow_hash, 
            '21e8' // Default pattern
        );
        
        if (!$verification['valid']) {
            Log::error('PoW verification failed', [
                'error' => $verification['error'],
                'board' => $board,
                'title' => $title
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
        // Debug logging for form submission
        Log::info('Reply form submission received', [
            'parent_id' => $request->input('parent_id'),
            'content_preview' => substr($request->input('content', ''), 0, 30) . '...',
            'form_data_keys' => array_keys($request->all())
        ]);

        $request->validate([
            'content' => 'required|max:2000',
            'parent_id' => 'nullable|exists:posts,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string|size:32'
        ]);

        $boardModel = Board::where('code', $board)->firstOrFail();
        $thread = Thread::findOrFail($threadId);
        
        // Validate Proof of Work for reply
        $challengeData = "post:{$threadId}:{$request->content}:{$request->pow_challenge_id}";
        $verification = Post::verifyProofOfWork(
            $challengeData, 
            $request->pow_nonce, 
            $request->pow_hash, 
            '21e8' // Default pattern
        );
        
        if (!$verification['valid']) {
            Log::error('Reply PoW verification failed', [
                'error' => $verification['error'],
                'thread_id' => $threadId,
                'challenge_data' => $challengeData
            ]);
            return back()->withErrors(['pow' => 'Proof of work verification failed: ' . $verification['error']])->withInput();
        }
        
        $anonymousUser = 'Anonymous#' . substr(hash('sha256', $request->ip() . time()), 0, 8);

        $postData = [
            'thread_id' => $thread->id,
            'content' => $request->content,
            'user_id' => null,
            'author_name' => $anonymousUser,
            'parent_id' => $request->parent_id,
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
