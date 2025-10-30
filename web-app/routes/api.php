<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PowController;
use App\Http\Controllers\MiningChallengeController;
use App\Http\Controllers\SelfMiningController;
use App\Http\Controllers\Api\UserToolbarController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API login routes (no CSRF required for API)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/backup-login', [AuthController::class, 'backupLogin']);
Route::post('/cryptographic-login', [AuthController::class, 'cryptographicLogin']);

Route::prefix('pow')->group(function () {
    Route::get('/params', [PowController::class, 'getParams']);
    
    // Remove auth:sanctum - these endpoints will check session auth internally
    Route::post('/thread/begin', [PowController::class, 'threadBegin']);
    Route::post('/thread/commit', [PowController::class, 'threadCommit']);
    Route::post('/reply/begin', [PowController::class, 'replyBegin']);
    Route::post('/reply/commit', [PowController::class, 'replyCommit']);
});

// User toolbar data endpoints (with session support for web integration)
Route::prefix('user')->middleware(['web'])->group(function () {
    Route::get('/toolbar-data', [UserToolbarController::class, 'getToolbarData']);
    Route::get('/recent-threads', [UserToolbarController::class, 'getRecentThreads']);
});

// Legacy mining routes (kept for backward compatibility)
// Use /api/pow/* routes for new v1 PoW system
Route::post('/mining/challenges', [MiningChallengeController::class, 'issue']);
Route::get('/pow.params', [MiningChallengeController::class, 'getParams']);

// Friend code validation
Route::post('/friend-codes/validate', function(\Illuminate\Http\Request $request) {
    $code = $request->input('code');
    
    if (!$code) {
        return response()->json(['valid' => false, 'message' => 'Friend code is required']);
    }
    
    // Check for genesis codes
    $genesisCodes = ['GENESIS2025', 'SUPERADMIN-3FuiKyZDg28GWoBcaKMCCgUK'];
    if (in_array($code, $genesisCodes)) {
        return response()->json(['valid' => true, 'message' => 'Genesis code accepted']);
    }
    
    // Check new invite codes table
    $inviteCode = \App\Models\InviteCode::where('code', $code)
        ->where('uses_remaining', '>', 0)
        ->where(function($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        })
        ->first();
    
    if ($inviteCode) {
        return response()->json(['valid' => true, 'message' => 'Valid invite code']);
    }
    
    // Check legacy friend_codes table
    $friendCode = \DB::table('friend_codes')
        ->where('code', $code)
        ->where('is_used', 0)
        ->first();
    
    if ($friendCode) {
        return response()->json(['valid' => true, 'message' => 'Valid friend code']);
    }
    
    return response()->json(['valid' => false, 'message' => 'Invalid or expired friend code']);
});

// Temporary alternative approach - test direct calls
Route::post('/threads/{thread}/posts', function(\Illuminate\Http\Request $request, \App\Models\Thread $thread) {
    $validated = $request->validate([
        'body' => ['nullable','string','max:8000'],
        'parent_id' => ['nullable','integer'],
        'image' => ['nullable','file','image','max:8192'],
    ]);

    if (empty($validated['body']) && !$request->hasFile('image')) {
        return response()->json(['message' => 'Post must include text or an image'], 422);
    }

    $path = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('posts', 'public');
    }

    $post = \App\Models\Post::create([
        'thread_id' => $thread->id,
        'parent_id' => $validated['parent_id'] ?? null,
        'user_id' => null,
        'content' => $validated['body'] ?? null,
        'image_path' => $path,
        'author_name' => 'TestUser',
    ]);

    return response()->json(['data' => $post], 201);
});

Route::get('/threads/{thread}/posts', function(\App\Models\Thread $thread) {
    $posts = \App\Models\Post::where('thread_id', $thread->id)
        ->orderBy('created_at')
        ->get(['id','thread_id','parent_id','user_id','content as body','image_path','created_at']);

    return response()->json(['data' => $posts]);
});

// Thread Order API (public endpoint for real-time updates)
Route::get('/boards/{board}/thread-order', function($board) {
    $boardModel = \App\Models\Board::where('code', $board)->first();
    
    if (!$boardModel) {
        return response()->json(['threads' => []]);
    }
    
    $threads = \App\Models\Thread::where('board_id', $boardModel->id)
        ->select('id', 'bump_score')
        ->get()
        ->map(function($thread) {
            // Calculate accumulated points same way as ForumController
            $powPoints = \App\Models\ProofOfWork::where('thread_id', $thread->id)->sum('points');
            $accumulatedPoints = $thread->bump_score + $powPoints;
            
            return [
                'id' => $thread->id,
                'total_pow' => $accumulatedPoints,
                'accumulated_points' => $accumulatedPoints
            ];
        })
        ->sortByDesc('accumulated_points')
        ->values();
    
    return response()->json(['threads' => $threads]);
});

// Personal 21e8 self-mining endpoint
Route::post('/self-mining/submit', [SelfMiningController::class, 'submitPersonal21e8']);