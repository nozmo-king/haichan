<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    try {
        $boards = \App\Models\Board::getActiveBoards();
        return view('boards.index', compact('boards'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Public authentication routes - both mobile and web support
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::get('/auth/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/auth/generate-keys', [AuthController::class, 'generateKeys']);
Route::get('/auth/invite-status', function() {
    return response()->json(\App\Models\InviteCode::getInviteStatus());
});

// Challenge endpoint for mobile cryptographic auth
Route::post('/challenge', [AuthController::class, 'getChallenge'])->middleware('throttle:25,1')->name('auth.challenge');

// Login routes - supporting both mobile cryptographic and web auth
Route::post('/login/cryptographic', [AuthController::class, 'cryptographicLogin'])->middleware('throttle:25,1')->name('auth.cryptographic.login');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:25,1');
Route::post('/auth/login-backup', [AuthController::class, 'backupLogin'])->middleware('throttle:10,1');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

// Logout routes - supporting both paths
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('bitcoin.auth');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout.alt');

// Registration routes (public)
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('auth.register.form');
Route::post('/register/validate-friend-code', [AuthController::class, 'validateFriendCode'])->name('auth.validate.friend.code');
Route::get('/register/{friendCode}', [AuthController::class, 'showRegister'])->name('auth.register')->middleware('validate.friend.code');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.store');

// Anonymous browsing route
Route::get('/anonymous', function () {
    $boards = \App\Models\Board::all();
    return view('anonymous.index', compact('boards'));
})->name('anonymous.index');

// Protected routes - require authentication
Route::middleware('bitcoin.auth')->group(function () {
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
    })->name('dashboard');

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

    // The MC - shows all threads from all boards
    Route::get('/catalog', [App\Http\Controllers\ForumController::class, 'showTheMC'])->name('the.mc');
    Route::get('/forum/board/{code}', [App\Http\Controllers\ForumController::class, 'showBoard'])->name('forum.board');
    Route::get('/forum/board/{code}/thread/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])->name('forum.thread.alt');
    Route::get('/forum/board/{code}/create', [App\Http\Controllers\ForumController::class, 'createThread'])->name('forum.create');
    Route::post('/forum/board/{code}/create', [App\Http\Controllers\ForumController::class, 'storeThread'])->name('forum.store');

    // User post management
    Route::delete('/posts/{postId}/delete', [App\Http\Controllers\ForumController::class, 'deleteUserPost'])
         ->name('posts.delete.user')
         ->where('postId', '[0-9]+');
    Route::delete('/threads/{threadId}/delete', [App\Http\Controllers\ForumController::class, 'deleteUserThread'])
         ->name('threads.delete.user')
         ->where('threadId', '[0-9]+');

    // Board catalog (specific path, must come before {board})
    Route::get('/{board}/catalog', [App\Http\Controllers\ForumController::class, 'showCatalog'])
         ->name('board.catalog')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');

    // Thread creation (specific path, must come before {board})
    Route::get('/{board}/create', [App\Http\Controllers\ForumController::class, 'createThread'])
         ->name('board.create')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');

    // Thread view (specific path, must come before {board})
    Route::get('/{board}/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])
         ->name('forum.thread')
         ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music', 'threadId' => '[0-9]+']);

    // Thread creation POST (specific path)
    Route::post('/{board}/create', [App\Http\Controllers\ForumController::class, 'storeThread'])
         ->name('board.store')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');

    // Thread creation (less specific, comes after specific paths)
    Route::post('/{board}', [App\Http\Controllers\ForumController::class, 'storeThread'])
         ->name('board.store.alt')
         ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|General|Technology|Business|Meta|Film|Random|Literature|Music');

    // Dynamic board routes - supports all boards: gen, tech, biz, film, x, lit, meta, mu
    Route::group([], function () {
        // Reply to thread (MUST come before {board} routes to avoid conflicts)
        Route::post('/{board}/{threadId}/reply', [App\Http\Controllers\ForumController::class, 'storeReply'])
             ->name('forum.reply')
             ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|ddl|General|Technology|Business|Meta|Film|Random|Literature|Music', 'threadId' => '[0-9]+']);

        // Board catalog (specific path, must come before {board})
        Route::get('/{board}/catalog', [App\Http\Controllers\ForumController::class, 'showCatalog'])
             ->name('board.catalog')
             ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|ddl|General|Technology|Business|Meta|Film|Random|Literature|Music');

        // Thread view (specific path, must come before {board})
        Route::get('/{board}/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])
             ->name('forum.thread')
             ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|ddl|General|Technology|Business|Meta|Film|Random|Literature|Music', 'threadId' => '[0-9]+']);

        // Thread creation (less specific, comes after specific paths)
        Route::post('/{board}', [App\Http\Controllers\ForumController::class, 'storeThread'])
             ->name('board.store')
             ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|ddl|General|Technology|Business|Meta|Film|Random|Literature|Music');

        // Board main page (least specific, comes last)
        Route::get('/{board}', [App\Http\Controllers\ForumController::class, 'showBoard'])
             ->name('board.show')
             ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|ddl|General|Technology|Business|Meta|Film|Random|Literature|Music');
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

    // Mining routes
    Route::get('/mining', [App\Http\Controllers\MiningController::class, 'dashboard']);
    Route::get('/mining/stats', [App\Http\Controllers\MiningController::class, 'stats']);

    // Static pages
    Route::get('/rules', function() {
        return view('static.rules');
    });

    Route::get('/faq', function() {
        return view('static.faq');
    });

    // Admin routes (requires Bitcoin auth)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');

        // User Management
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::post('/users/{id}/ban', [App\Http\Controllers\AdminController::class, 'banUser'])->name('users.ban');
        Route::post('/users/{id}/unban', [App\Http\Controllers\AdminController::class, 'unbanUser'])->name('users.unban');
        Route::post('/users/{id}/promote', [App\Http\Controllers\AdminController::class, 'promoteUser'])->name('users.promote');
        Route::post('/users/{id}/demote', [App\Http\Controllers\AdminController::class, 'demoteUser'])->name('users.demote');

        // Forum Moderation
        Route::get('/forum', [App\Http\Controllers\AdminController::class, 'forum'])->name('forum');
        Route::post('/threads/{id}/pin', [App\Http\Controllers\AdminController::class, 'pinThread'])->name('threads.pin');
        Route::post('/threads/{id}/lock', [App\Http\Controllers\AdminController::class, 'lockThread'])->name('threads.lock');
        Route::delete('/threads/{id}/delete', [App\Http\Controllers\AdminController::class, 'deleteThread'])->name('threads.delete');
        Route::delete('/posts/{id}/delete', [App\Http\Controllers\AdminController::class, 'deletePost'])->name('posts.delete');

        // Genesis Code Management
        Route::post('/genesis-codes', [App\Http\Controllers\AdminController::class, 'createGenesisCode'])->name('genesis-codes.store');

        // API endpoints
        Route::get('/api/activity', [App\Http\Controllers\AdminController::class, 'getActivity'])->name('api.activity');

        // Legacy keys management
        Route::get('/keys', [App\Http\Controllers\AdminController::class, 'keys'])->name('keys');
        Route::resource('keys', App\Http\Controllers\AdminController::class, [
            'except' => ['show'],
            'names' => [
                'keys.index' => 'keys.index',
                'create' => 'keys.create',
                'store' => 'keys.store',
                'edit' => 'keys.edit',
                'update' => 'keys.update',
                'destroy' => 'keys.destroy'
            ]
        ]);
    });
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
