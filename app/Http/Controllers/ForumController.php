<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('threads')->get();
        return view('forum.index', compact('boards'));
    }

    public function showBoard($code)
    {
        $board = Board::where('code', $code)->firstOrFail();
        $threads = Thread::where('board_id', $board->id)
            ->withCount('posts')
            ->with(['posts' => function($query) {
                $query->whereNull('parent_id')
                      ->orderBy('created_at', 'asc')
                      ->limit(7)
                      ->with(['replies' => function($subQuery) {
                          $subQuery->limit(3)->with(['replies' => function($nestedQuery) {
                              $nestedQuery->limit(2);
                          }]);
                      }]);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('forum.board', compact('board', 'threads'));
    }

    public function showThread($boardCode, $threadId)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        $thread = Thread::with(['posts' => function($query) {
            $query->whereNull('parent_id')
                  ->orderBy('created_at', 'asc')
                  ->with(['allReplies']);
        }])->findOrFail($threadId);
        
        return view('forum.thread', compact('board', 'thread'));
    }

    public function createThread($boardCode)
    {
        $board = Board::where('code', $boardCode)->firstOrFail();
        return view('forum.create-thread', compact('board'));
    }

    public function storeThread(Request $request, $boardCode)
    {
        Log::info('=== THREAD CREATION POST REQUEST START ===', [
            'route_name' => $request->route()->getName(),
            'route_parameters' => $request->route()->parameters(),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'board_code' => $boardCode,
            'session_id' => session()->getId(),
            'csrf_token' => $request->input('_token'),
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'has_subscription' => auth()->check() ? auth()->user()->hasActiveSubscription() : false,
            'request_headers' => [
                'accept' => $request->header('Accept'),
                'content-type' => $request->header('Content-Type'),
                'x-csrf-token' => $request->header('X-CSRF-Token'),
                'referer' => $request->header('Referer'),
                'origin' => $request->header('Origin'),
                'cookie' => $request->header('Cookie') ? 'present' : 'missing'
            ],
            'all_request_data' => $request->except(['_token', 'content']),
            'content_length' => strlen($request->input('content', '')),
            'middleware_stack' => $request->route() ? $request->route()->middleware() : [],
            'session_data' => [
                'session_driver' => config('session.driver'),
                'session_lifetime' => config('session.lifetime'),
                'session_exists' => session()->has('_token'),
                'session_csrf_token' => session()->token(),
                'session_regenerated' => session()->get('_previous'),
                'auth_guard' => auth()->getDefaultDriver(),
                'remember_token' => auth()->check() ? auth()->user()->remember_token : null
            ]
        ]);

        // Critical authentication check with detailed logging
        if (!auth()->check()) {
            Log::error('=== AUTHENTICATION FAILED - USER NOT LOGGED IN ===', [
                'session_id' => session()->getId(),
                'csrf_token_match' => $request->input('_token') === session()->token(),
                'session_has_user_id' => session()->has('user_id'),
                'session_user_id' => session()->get('user_id'),
                'auth_id' => auth()->id(),
                'request_time' => $request->server('REQUEST_TIME'),
                'session_last_activity' => session()->get('last_activity'),
                'redirect_to' => route('login')
            ]);
            
            return redirect()->route('login')->with('error', 'You must be logged in to create threads.');
        }

        // Additional user verification
        try {
            $user = auth()->user();
            if (!$user) {
                Log::error('=== USER OBJECT NULL DESPITE AUTH CHECK ===', [
                    'auth_check' => auth()->check(),
                    'auth_id' => auth()->id(),
                    'session_id' => session()->getId(),
                    'redirect_to' => route('login')
                ]);
                return redirect()->route('login')->with('error', 'Authentication error. Please log in again.');
            }

            Log::info('=== USER AUTHENTICATION VERIFIED ===', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_created_at' => $user->created_at,
                'has_public_key' => $user->allowedPublicKey ? true : false,
                'public_key_id' => $user->allowedPublicKey ? $user->allowedPublicKey->id : null
            ]);

        } catch (\Exception $e) {
            Log::error('=== USER VERIFICATION EXCEPTION ===', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'auth_check' => auth()->check(),
                'auth_id' => auth()->id(),
                'redirect_to' => route('login')
            ]);
            return redirect()->route('login')->with('error', 'Authentication error. Please log in again.');
        }


        Log::info('=== THREAD CREATION VALIDATION START ===', [
            'user_id' => auth()->id(),
            'board_code' => $boardCode,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        try {
            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required|max:2000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            Log::info('=== THREAD CREATION VALIDATION PASSED ===', [
                'user_id' => auth()->id(),
                'board_code' => $boardCode,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('=== THREAD CREATION VALIDATION FAILED ===', [
                'user_id' => auth()->id(),
                'board_code' => $boardCode,
                'validation_errors' => $e->errors(),
                'request_data' => $request->except(['_token', 'content']),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }

        Log::info('Thread creation validation passed', [
            'user_id' => auth()->id(),
            'board_code' => $boardCode,
            'title' => $request->title,
            'content_length' => strlen($request->content)
        ]);


        Log::info('=== DATABASE OPERATIONS START ===', [
            'user_id' => auth()->id(),
            'board_code' => $boardCode,
            'session_id' => session()->getId(),
            'auth_check_before_db' => auth()->check()
        ]);

        $board = Board::where('code', $boardCode)->firstOrFail();

        Log::info('=== BOARD RETRIEVED SUCCESSFULLY ===', [
            'board_id' => $board->id,
            'board_code' => $board->code,
            'user_id' => auth()->id(),
            'auth_check_after_board' => auth()->check()
        ]);

        // Verify user still has public key access
        $user = auth()->user();
        if (!$user->allowedPublicKey) {
            Log::error('=== USER MISSING PUBLIC KEY ===', [
                'user_id' => $user->id,
                'board_code' => $boardCode,
                'session_id' => session()->getId(),
                'redirect_to' => route('login')
            ]);
            return redirect()->route('login')->with('error', 'Public key access required.');
        }

        $threadData = [
            'board_id' => $board->id,
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id(),
            'author_name' => substr($user->allowedPublicKey->public_key, 0, 12) . '...'
        ];

        Log::info('=== THREAD DATA PREPARED ===', [
            'thread_data' => $threadData,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'auth_check_before_create' => auth()->check()
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
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'auth_check_immediate_before_create' => auth()->check(),
                'user_exists_before_create' => auth()->user() ? true : false
            ]);

            $thread = Thread::create($threadData);
            
            Log::info('=== THREAD CREATED SUCCESSFULLY ===', [
                'thread_id' => $thread->id,
                'user_id' => auth()->id(),
                'board_code' => $boardCode,
                'title' => $thread->title,
                'thread_data_saved' => $threadData,
                'redirect_route' => route('forum.thread', [$boardCode, $thread->id]),
                'session_id' => session()->getId(),
                'auth_check_after_create' => auth()->check()
            ]);

            // Pre-redirect authentication check
            if (!auth()->check()) {
                Log::error('=== AUTHENTICATION LOST AFTER THREAD CREATION ===', [
                    'thread_id' => $thread->id,
                    'session_id' => session()->getId(),
                    'user_was' => $threadData['user_id'],
                    'redirect_to' => route('login'),
                    'critical_error' => 'User became unauthenticated after successful thread creation'
                ]);
                return redirect()->route('login')->with('error', 'Session expired after creating thread. Your thread was saved.');
            }

            Log::info('=== THREAD CREATION REDIRECT ===', [
                'from_url' => $request->fullUrl(),
                'to_route' => 'forum.thread',
                'to_params' => [$boardCode, $thread->id],
                'to_url' => route('forum.thread', [$boardCode, $thread->id]),
                'user_id' => auth()->id(),
                'thread_id' => $thread->id,
                'session_id' => session()->getId(),
                'final_auth_check' => auth()->check(),
                'final_user_exists' => auth()->user() ? true : false
            ]);

            return redirect()->route('forum.thread', [$boardCode, $thread->id]);
            
        } catch (\Exception $e) {
            Log::error('=== THREAD CREATION DATABASE ERROR ===', [
                'user_id' => auth()->id(),
                'board_code' => $boardCode,
                'error' => $e->getMessage(),
                'thread_data' => $threadData,
                'stack_trace' => $e->getTraceAsString(),
                'redirect_to' => route('forum.board', $boardCode),
                'session_id' => session()->getId(),
                'auth_check_during_error' => auth()->check()
            ]);
            
            return redirect()->route('forum.board', $boardCode)
                ->withErrors(['database' => 'Failed to save thread: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function storeReply(Request $request, $boardCode, $threadId)
    {
        $request->validate([
            'content' => 'required|max:2000',
            'parent_id' => 'nullable|exists:posts,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $board = Board::where('code', $boardCode)->firstOrFail();
        $thread = Thread::findOrFail($threadId);

        $postData = [
            'thread_id' => $thread->id,
            'content' => $request->content,
            'user_id' => auth()->id(),
            'author_name' => substr(auth()->user()->allowedPublicKey->public_key, 0, 12) . '...',
            'parent_id' => $request->parent_id
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

        return redirect()->route('forum.thread', [$boardCode, $threadId]);
    }

}
