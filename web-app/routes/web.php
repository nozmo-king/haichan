<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $userCount = \App\Models\User::count();
    $userCap = 256; // Define the user cap
    $boards = \App\Models\Board::all();
    
    // Calculate global hashrate from recent proofs (last 5 minutes)
    $recentProofs = \App\Models\ProofOfWork::where('verified_at', '>', now()->subMinutes(5))->count();
    $globalHashrate = $recentProofs * 100000; // Estimate based on proof difficulty
    
    $activeSessions = max(1, floor($recentProofs / 3)); // Estimate active miners
    
    // Total network stats
    $totalHashes = \App\Models\ProofOfWork::count() * 500000; // Estimate total hashes
    $totalProofs = \App\Models\ProofOfWork::count();
    
    return view('welcome', compact('userCount', 'userCap', 'boards', 'globalHashrate', 'activeSessions', 'totalHashes', 'totalProofs'));
});

Route::get('/test', function () {
    try {
        $boardCount = \App\Models\Board::count();
        $boards = \App\Models\Board::all()->pluck('name');
        $activeBoards = \App\Models\Board::getActiveBoards()->pluck('name');
        
        return response()->json([
            'status' => 'Routes working!',
            'total_boards' => $boardCount,
            'all_boards' => $boards,
            'active_boards' => $activeBoards
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

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

// Skip login for now - redirect to forum
Route::get('/login', function() { return redirect('/'); })->name('login');

// Dynamic board routes - supports all boards: gen, tech, biz, film, x, lit, meta, mu
Route::group(['where' => ['board' => 'gen|tech|biz|film|x|lit|meta|mu']], function () {
    // Board main page
    Route::get('/{board}', [App\Http\Controllers\ForumController::class, 'showBoard'])->name('board.show');
    
    // Thread creation
    Route::post('/{board}', [App\Http\Controllers\ForumController::class, 'storeThread'])->name('board.store');
    
    // Board catalog
    Route::get('/{board}/catalog', [App\Http\Controllers\ForumController::class, 'showCatalog'])->name('board.catalog');
    
    // Thread view
    Route::get('/{board}/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])
         ->name('forum.thread')
         ->where('threadId', '[0-9]+');
    
    // Reply to thread
    Route::post('/{board}/{threadId}/reply', [App\Http\Controllers\ForumController::class, 'storeReply'])
         ->name('forum.reply')
         ->where('threadId', '[0-9]+');
});

// Image serving routes
Route::get('/image/thread/{id}', [App\Http\Controllers\ForumController::class, 'serveThreadImage'])->name('thread.image');
Route::get('/image/post/{id}', [App\Http\Controllers\ForumController::class, 'servePostImage'])->name('post.image');

Route::get('/mining', function() {
    return view('mining.dashboard');
});

Route::get('/rules', function() {
    return view('static.rules');
});

Route::get('/faq', function() {
    return view('static.faq');
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
