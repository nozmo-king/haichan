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
Route::post('/mining/submit-proof', [MiningChallengeController::class, 'submitProof']);
Route::get('/pow.params', [MiningChallengeController::class, 'getParams']);

// Recursive 21e8 Mining Toolbar API
Route::get('/mining/21e8-stats', function(Request $request) {
    $totalProofs = \App\Models\ProofOfWork::where('pattern', 'LIKE', '21e8%')->count();
    $legendaryHashes = \App\Models\ProofOfWork::where('pattern', '21e8')->count();
    
    $userProofs = 0;
    if (session('bitcoin_auth_id')) {
        $userProofs = \App\Models\ProofOfWork::where('user_id', session('bitcoin_auth_id'))
                                            ->where('pattern', 'LIKE', '21e8%')
                                            ->count();
    }
    
    return response()->json([
        'success' => true,
        'total_proofs' => $totalProofs,
        'legendary_hashes' => $legendaryHashes,
        'user_proofs' => $userProofs,
        'last_updated' => now()->toISOString()
    ]);
});

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

// Image Library API endpoints
Route::get('/image-library/shifting', function() {
    // Get images prioritizing less-used ones to encourage reuse and prevent wasteful duplicates
    $images = \App\Models\ImageGallery::orderBy('usage_count', 'asc') // Prioritize less-used images
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get()
        ->map(function($image) {
            return [
                'id' => $image->id,
                'original_name' => $image->original_name,
                'file_path' => $image->file_path,
                'total_pow_earned' => $image->total_pow_earned ?? 0,
                'hash' => $image->hash,
                'type' => 'gallery',
                'usage_count' => $image->usage_count
            ];
        })
        ->shuffle()
        ->take(20);
    
    return response()->json(['arrangement' => $images]);
});

Route::get('/image-library/{id}/full', function($id) {
    if (str_starts_with($id, 'thread_')) {
        $threadId = str_replace('thread_', '', $id);
        $image = \App\Models\Thread::findOrFail($threadId);
        $imagePath = $image->image_path;
    } elseif (str_starts_with($id, 'post_')) {
        $postId = str_replace('post_', '', $id);
        $image = \App\Models\Post::findOrFail($postId);
        $imagePath = $image->image_path;
    } else {
        abort(404, 'Invalid image ID format');
    }
    
    if (!$imagePath) {
        abort(404, 'No image found');
    }
    
    // Images are stored in public directory, not storage
    $path = public_path($imagePath);
    
    if (!file_exists($path)) {
        abort(404, 'Image file not found');
    }
    
    return response()->file($path);
});

Route::get('/image-library/hash/{hash}', function($hash) {
    // Find in image library
    $image = \App\Models\ImageLibrary::where('hash', $hash)->first();
    
    if ($image && $image->file_path && \Storage::disk('local')->exists($image->file_path)) {
        $path = \Storage::disk('local')->path($image->file_path);
        return response()->file($path);
    }
    
    // Fallback: Try to find in threads
    $thread = \App\Models\Thread::whereNotNull('image_path')
        ->get()
        ->first(function($t) use ($hash) {
            return hash('sha256', $t->image_path) === $hash;
        });
    
    if ($thread && \Storage::disk('local')->exists($thread->image_path)) {
        $path = \Storage::disk('local')->path($thread->image_path);
        return response()->file($path);
    }
    
    // Still not found, return JSON response
    if ($thread) {
        return response()->json([
            'found' => true,
            'image' => [
                'id' => 'thread_' . $thread->id,
                'original_name' => basename($thread->image_path),
                'file_path' => $thread->image_path,
                'pow_points' => $thread->pow_points ?? 0
            ]
        ]);
    }
    
    // Try to find in posts
    $post = \App\Models\Post::whereNotNull('image_path')
        ->get()
        ->first(function($p) use ($hash) {
            return md5($p->image_path) === $hash;
        });
    
    if ($post) {
        return response()->json([
            'found' => true,
            'image' => [
                'id' => 'post_' . $post->id,
                'original_name' => basename($post->image_path),
                'file_path' => $post->image_path,
                'pow_points' => $post->pow_points ?? 0
            ]
        ]);
    }
    
    return response()->json(['found' => false], 404);
});