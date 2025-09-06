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

// Individual board routes
Route::get('/gen', function () {
    try {
        $board = \App\Models\Board::where('code', 'gen')->first();
        if (!$board) {
            throw new Exception('Board "gen" not found');
        }
        $threads = $board->threads()->orderBy('created_at', 'desc')->paginate(20);
        return view('boards.show', compact('board', 'threads'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Board routes with POST support for thread creation and replies
Route::get('/gen', function() { 
    try {
        return app(\App\Http\Controllers\ForumController::class)->showBoard('gen'); 
    } catch (Exception $e) {
        return response('<h1>Error</h1><p>' . $e->getMessage() . '</p><pre>' . $e->getTraceAsString() . '</pre>', 500);
    }
});
Route::get('/gen/catalog', function() { return app(\App\Http\Controllers\ForumController::class)->showCatalog('gen'); });
Route::post('/gen', function() { return app(\App\Http\Controllers\ForumController::class)->storeThread(request(), 'gen'); });
Route::get('/gen/{threadId}', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->showThread('gen', $threadId); })->name('forum.thread');
Route::post('/gen/{threadId}/reply', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->storeReply(request(), 'gen', $threadId); });

Route::get('/tech', function() { return app(\App\Http\Controllers\ForumController::class)->showBoard('tech'); });
Route::get('/tech/catalog', function() { return app(\App\Http\Controllers\ForumController::class)->showCatalog('tech'); });
Route::post('/tech', function() { return app(\App\Http\Controllers\ForumController::class)->storeThread(request(), 'tech'); });
Route::get('/tech/{threadId}', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->showThread('tech', $threadId); });
Route::post('/tech/{threadId}/reply', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->storeReply(request(), 'tech', $threadId); });

Route::get('/biz', function() { return app(\App\Http\Controllers\ForumController::class)->showBoard('biz'); });
Route::get('/biz/catalog', function() { return app(\App\Http\Controllers\ForumController::class)->showCatalog('biz'); });
Route::post('/biz', function() { return app(\App\Http\Controllers\ForumController::class)->storeThread(request(), 'biz'); });
Route::get('/biz/{threadId}', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->showThread('biz', $threadId); });
Route::post('/biz/{threadId}/reply', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->storeReply(request(), 'biz', $threadId); });

Route::get('/film', function() { return app(\App\Http\Controllers\ForumController::class)->showBoard('film'); });
Route::get('/film/catalog', function() { return app(\App\Http\Controllers\ForumController::class)->showCatalog('film'); });
Route::post('/film', function() { return app(\App\Http\Controllers\ForumController::class)->storeThread(request(), 'film'); });
Route::get('/film/{threadId}', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->showThread('film', $threadId); });
Route::post('/film/{threadId}/reply', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->storeReply(request(), 'film', $threadId); });

Route::get('/x', function() { return app(\App\Http\Controllers\ForumController::class)->showBoard('x'); });
Route::get('/x/catalog', function() { return app(\App\Http\Controllers\ForumController::class)->showCatalog('x'); });
Route::post('/x', function() { return app(\App\Http\Controllers\ForumController::class)->storeThread(request(), 'x'); });
Route::get('/x/{threadId}', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->showThread('x', $threadId); });
Route::post('/x/{threadId}/reply', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->storeReply(request(), 'x', $threadId); });

Route::get('/lit', function() { return app(\App\Http\Controllers\ForumController::class)->showBoard('lit'); });
Route::get('/lit/catalog', function() { return app(\App\Http\Controllers\ForumController::class)->showCatalog('lit'); });
Route::post('/lit', function() { return app(\App\Http\Controllers\ForumController::class)->storeThread(request(), 'lit'); });
Route::get('/lit/{threadId}', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->showThread('lit', $threadId); });
Route::post('/lit/{threadId}/reply', function($threadId) { return app(\App\Http\Controllers\ForumController::class)->storeReply(request(), 'lit', $threadId); });

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
