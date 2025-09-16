<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    $userCount = \App\Models\User::count();
    $userCap = 256; // Define the user cap
    $boards = \App\Models\Board::all();

    // Calculate real stats from actual proof submissions
    $recentProofs = \App\Models\ProofSubmission::where('created_at', '>', now()->subMinutes(5))->count();
    $activeSessions = \App\Models\ProofSubmission::where('created_at', '>', now()->subMinutes(5))
        ->distinct('user_session')->count('user_session');

    // Real computational stats - no dummy multipliers
    $totalProofs = \App\Models\ProofSubmission::count();
    $totalHashes = \App\Models\ProofSubmission::getTotalHashes();
    $globalHashrate = $recentProofs > 0 ? ($recentProofs * 12) : 0; // Proofs per 5min * 12 = proofs per hour

    return view('welcome', compact('userCount', 'userCap', 'boards', 'globalHashrate', 'activeSessions', 'totalHashes', 'totalProofs'));
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/challenge', [AuthController::class, 'getChallenge'])->middleware('throttle:25,1')->name('auth.challenge');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:25,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/boards', function () {
    try {
        $boards = \App\Models\Board::getActiveBoards();
        return view('boards.index', compact('boards'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('boards.index');

// Forum routes
Route::get('/forum', [App\Http\Controllers\ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/board/{code}', [App\Http\Controllers\ForumController::class, 'showBoard'])->name('forum.board');
Route::get('/forum/board/{code}/thread/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])->name('forum.thread.alt');
Route::get('/forum/board/{code}/create', [App\Http\Controllers\ForumController::class, 'createThread'])->name('forum.create');
Route::post('/forum/board/{code}/create', [App\Http\Controllers\ForumController::class, 'storeThread'])->name('forum.store');

// Login route is handled above by AuthController

// Dynamic board routes - supports all boards: gen, tech, biz, film, x, lit, meta, mu
Route::group([], function () {
    // Reply to thread (MUST come before {board} routes to avoid conflicts)
    Route::post('/{board}/{threadId}/reply', [App\Http\Controllers\ForumController::class, 'storeReply'])
         ->name('forum.reply')
         ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music', 'threadId' => '[0-9]+']);

    // Board catalog (specific path, must come before {board})
    Route::get('/{board}/catalog', [App\Http\Controllers\ForumController::class, 'showCatalog'])
         ->name('board.catalog')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');

    // Thread view (specific path, must come before {board})
    Route::get('/{board}/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])
         ->name('forum.thread')
         ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music', 'threadId' => '[0-9]+']);

    // Thread creation (less specific, comes after specific paths)
    Route::post('/{board}', [App\Http\Controllers\ForumController::class, 'storeThread'])
         ->name('board.store')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');

    // Board main page (least specific, comes last)
    Route::get('/{board}', [App\Http\Controllers\ForumController::class, 'showBoard'])
         ->name('board.show')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');
});

// Image serving routes
Route::get('/image/thread/{id}', [App\Http\Controllers\ForumController::class, 'serveThreadImage'])->name('thread.image');
Route::get('/image/post/{id}', [App\Http\Controllers\ForumController::class, 'servePostImage'])->name('post.image');

// Image Library routes
Route::get('/library', [App\Http\Controllers\ImageLibraryController::class, 'index'])->name('image-library.index');
Route::post('/api/image-library/mine', [App\Http\Controllers\ImageLibraryController::class, 'mine']);
Route::post('/api/image-library/upload', [App\Http\Controllers\ImageLibraryController::class, 'upload']);
Route::get('/api/image-library/{id}/full', [App\Http\Controllers\ImageLibraryController::class, 'fullImage']);
Route::get('/api/image-library/{id}/download', [App\Http\Controllers\ImageLibraryController::class, 'download']);
Route::get('/api/image-library/hash/{hash}', [App\Http\Controllers\ImageLibraryController::class, 'getByHash']);
Route::get('/api/image-library/stats', [App\Http\Controllers\ImageLibraryController::class, 'getStats']);
Route::get('/api/image-library/search', [App\Http\Controllers\ImageLibraryController::class, 'search']);
Route::get('/api/image-library/shifting', [App\Http\Controllers\ImageLibraryController::class, 'getShiftingArrangement']);

// All PoW functionality now handled by unified /api/proof endpoint

Route::get('/mining', [App\Http\Controllers\MiningController::class, 'dashboard']);
Route::get('/mining/stats', [App\Http\Controllers\MiningController::class, 'stats']);

Route::get('/rules', function() {
    return view('static.rules');
});

Route::get('/faq', function() {
    return view('static.faq');
});

// Admin routes (requires authentication)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
    Route::resource('keys', App\Http\Controllers\AdminController::class, [
        'except' => ['show'],
        'names' => [
            'index' => 'keys.index',
            'create' => 'keys.create',
            'store' => 'keys.store',
            'edit' => 'keys.edit',
            'update' => 'keys.update',
            'destroy' => 'keys.destroy'
        ]
    ]);
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
