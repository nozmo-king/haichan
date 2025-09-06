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

    public function showBoard($code = null)
    {
        // Get board code from route parameter or use the request URI
        if (!$code) {
            $code = request()->segment(1); // Get first segment of URL path
        }
        $board = Board::where('code', $code)->firstOrFail();
        $threads = Thread::where('board_id', $board->id)
            ->withCount('posts')
            ->withSum('proofOfWork', 'points') // Sum PoW points for sorting
            ->orderByDesc('proof_of_work_sum_points') // Sort by total PoW descending
            ->orderByDesc('created_at') // Secondary sort by creation date
            ->paginate(20);
        
        return view('boards.show', compact('board', 'threads'));
    }
    
    public function showCatalog($boardCode)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        $threads = Thread::where('board_id', $board->id)
            ->withCount('posts')
            ->withSum('proofOfWork', 'points')
            ->orderByDesc('proof_of_work_sum_points')
            ->orderByDesc('created_at')
            ->paginate(100);
        
        return view('boards.catalog', compact('board', 'threads'));
    }

    public function showThread($boardCode, $threadId)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        $thread = Thread::withSum('proofOfWork', 'points')->findOrFail($threadId);
        
        // Return just PoW score for AJAX requests
        if (request()->get('pow_only')) {
            return response()->json(['pow_score' => $thread->proof_of_work_sum_points ?? 0]);
        }
        
        $posts = Post::where('thread_id', $threadId)
                    ->with(['parent'])
                    ->orderBy('created_at', 'asc')
                    ->get();
        
        return view('boards.thread', compact('board', 'thread', 'posts'));
    }

    public function createThread($boardCode)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        return view('forum.create-thread', compact('board'));
    }

    public function storeThread(Request $request, $boardCode)
    {
        // Debug: Log all incoming request data
        Log::info('=== THREAD CREATION DEBUG ===', [
            'all_request_data' => $request->all(),
            'board_code' => $boardCode,
            'method' => $request->method(),
            'has_title' => $request->has('title'),
            'title_value' => $request->input('title'),
            'has_content' => $request->has('content'),
            'content_value' => $request->input('content'),
        ]);

        // Skip authentication for now - create anonymous posts
        $anonymousUser = 'Anonymous#' . substr(hash('sha256', $request->ip() . time()), 0, 8);


        Log::info('=== THREAD CREATION VALIDATION START ===', [
            'anonymous_user' => $anonymousUser,
            'board_code' => $boardCode,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Simple validation - accept either title or subject
        $title = $request->input('title') ?: $request->input('subject');
        if (!$title) {
            Log::error('TITLE/SUBJECT MISSING', ['title' => $request->input('title'), 'subject' => $request->input('subject'), 'all_data' => $request->all()]);
            return back()->withErrors(['title' => 'Title is required'])->withInput();
        }
        
        if (!$request->filled('content')) {
            Log::error('CONTENT MISSING', ['content' => $request->input('content')]);
            return back()->withErrors(['content' => 'Content is required'])->withInput();
        }

        Log::info('=== VALIDATION PASSED ===', [
            'title' => $title,
            'content_length' => strlen($request->input('content'))
        ]);

        Log::info('Thread creation validation passed', [
            'anonymous_user' => $anonymousUser,
            'board_code' => $boardCode,
            'title' => $title,
            'content_length' => strlen($request->content)
        ]);

        // Validate Proof of Work
        $request->validate([
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string|size:32'
        ]);

        $board = Board::where('code', $boardCode)->firstOrFail();
        
        // Generate challenge data and verify PoW
        $challengeData = "thread:{$board->code}:{$title}:{$request->pow_challenge_id}";
        $verification = Thread::verifyProofOfWork(
            $challengeData, 
            $request->pow_nonce, 
            $request->pow_hash, 
            '21e8' // Default pattern
        );
        
        if (!$verification['valid']) {
            Log::error('PoW verification failed', [
                'error' => $verification['error'],
                'challenge_data' => $challengeData,
                'nonce' => $request->pow_nonce,
                'hash' => $request->pow_hash
            ]);
            return back()->withErrors(['pow' => 'Proof of work verification failed: ' . $verification['error']])->withInput();
        }

        Log::info('=== BOARD RETRIEVED SUCCESSFULLY ===', [
            'board_id' => $board->id,
            'board_code' => $board->code,
            'anonymous_user' => $anonymousUser
        ]);

        $threadData = [
            'board_id' => $board->id,
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

        Log::info('=== THREAD DATA PREPARED ===', [
            'thread_data' => $threadData,
            'anonymous_user' => $anonymousUser
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('forum/images', $filename, 'public');
            
            $threadData['image_path'] = $path;
            $threadData['image_filename'] = $image->getClientOriginalName();
        }

        try {
            Log::info('=== ATTEMPTING THREAD CREATION ===', [
                'anonymous_user' => $anonymousUser,
                'thread_data' => $threadData
            ]);

            $thread = Thread::create($threadData);
            
            Log::info('=== THREAD CREATED SUCCESSFULLY ===', [
                'thread_id' => $thread->id,
                'board_code' => $boardCode,
                'title' => $thread->title,
                'author' => $anonymousUser
            ]);

            return redirect("/$boardCode/{$thread->id}");
            
        } catch (\Exception $e) {
            Log::error('=== THREAD CREATION DATABASE ERROR ===', [
                'anonymous_user' => $anonymousUser,
                'board_code' => $boardCode,
                'error' => $e->getMessage(),
                'thread_data' => $threadData
            ]);
            
            return redirect("/$boardCode")
                ->withErrors(['database' => 'Failed to save thread: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function storeReply(Request $request, $boardCode, $threadId)
    {
        $request->validate([
            'content' => 'required|max:2000',
            'parent_id' => 'nullable|exists:posts,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pow_nonce' => 'required|integer',
            'pow_hash' => 'required|string|size:64',
            'pow_challenge_id' => 'required|string|size:32'
        ]);

        $board = Board::where('code', $boardCode)->firstOrFail();
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

        Post::create($postData);

        return redirect("/$boardCode/$threadId");
    }

}
