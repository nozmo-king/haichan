<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        // Check if user is authenticated
        if (session('bitcoin_auth_id')) {
            // Show full welcome dashboard for authenticated users
            $userCount = \App\Models\BitcoinAuth::count();
            $userCap = 256;
            $boards = \App\Models\Board::all();

            // Calculate real stats from ProofOfWork table
            $recentProofs = \App\Models\ProofOfWork::where('verified_at', '>', now()->subMinutes(5))->count();
            
            // Count unique active miners
            $activeSessions = \App\Models\ProofOfWork::where('verified_at', '>', now()->subMinutes(15))
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');
            
            // Add anonymous miners
            $anonymousEstimate = \App\Models\ProofOfWork::where('verified_at', '>', now()->subMinutes(15))
                ->whereNull('user_id')
                ->count();
            $activeSessions = $activeSessions + max(1, floor($anonymousEstimate / 5));

            // Real computational stats
            $totalProofs = \App\Models\ProofOfWork::count();
            
            // Calculate total hashes
            $totalHashes = 0;
            $powRecords = \App\Models\ProofOfWork::selectRaw('
                COUNT(*) as count,
                pattern
            ')->groupBy('pattern')->get();
            
            foreach ($powRecords as $record) {
                $hashesPerProof = match($record->pattern) {
                    '21' => 256,
                    '21e' => 4096,
                    '21e8' => 65536,
                    '21e80' => 1048576,
                    '21e800' => 16777216,
                    default => 1000
                };
                $totalHashes += $record->count * $hashesPerProof;
            }
            
            // Calculate hashrate
            $recentHashCount = \App\Models\ProofOfWork::where('verified_at', '>', now()->subHour())
                ->selectRaw('COUNT(*) as count, pattern')
                ->groupBy('pattern')
                ->get();
                
            $hourlyHashes = 0;
            foreach ($recentHashCount as $recent) {
                $hashesPerProof = match($recent->pattern) {
                    '21' => 256,
                    '21e' => 4096,
                    '21e8' => 65536,
                    '21e80' => 1048576,
                    '21e800' => 16777216,
                    default => 1000
                };
                $hourlyHashes += $recent->count * $hashesPerProof;
            }
            $globalHashrate = $hourlyHashes;

            return view('welcome', compact('userCount', 'userCap', 'boards', 'recentProofs', 'activeSessions', 'totalProofs', 'totalHashes', 'globalHashrate'));
        }
        
        // Show slot machine landing page for non-authenticated users
        $userCount = \App\Models\BitcoinAuth::count();
        $userCap = 256;
        $totalProofs = \App\Models\ProofOfWork::count();
        $boardCount = \App\Models\Board::count();
        
        return view('landing', compact('userCount', 'userCap', 'totalProofs', 'boardCount'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

// Preview landing page route (for testing while logged in)
Route::get('/preview-landing', function () {
    $userCount = \App\Models\BitcoinAuth::count();
    $userCap = 256;
    $totalProofs = \App\Models\ProofOfWork::count();
    $boardCount = \App\Models\Board::count();
    
    return view('landing', compact('userCount', 'userCap', 'totalProofs', 'boardCount'));
})->name('preview.landing');

// Stats page
Route::get('/stats', [StatsController::class, 'index'])->name('stats');

// Anonymous access route
Route::get('/anon', function () {
    try {
        $boards = \App\Models\Board::getActiveBoards();

        return view('boards.anon', compact('boards'));
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('anon');

// Public authentication routes - both mobile and web support
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('auth.login');

// Invite status endpoint for login page
Route::get('/auth/invite-status', function() {
    $currentUsers = \App\Models\BitcoinAuth::count();
    $maxUsers = 256;
    $remainingSlots = max(0, $maxUsers - $currentUsers);
    
    return response()->json([
        'current_users' => $currentUsers,
        'max_users' => $maxUsers,
        'remaining_slots' => $remainingSlots,
        'registration_open' => $remainingSlots > 0
    ]);
});

// Login routes - supporting both mobile cryptographic and web auth
Route::post('/login/cryptographic', [AuthController::class, 'cryptographicLogin'])->middleware('throttle:25,1')->name('auth.cryptographic.login');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:25,1');
Route::post('/auth/login-backup', [AuthController::class, 'backupLogin'])->middleware('throttle:10,1');


// Username check API endpoint  
Route::post('/auth/check-username', [AuthController::class, 'checkUsername'])->middleware('throttle:20,1');


// Logout routes - supporting both paths
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout.alt');

// Registration routes (public)
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register/validate-friend-code', [AuthController::class, 'validateFriendCode'])->name('auth.validate.friend.code');
Route::post('/api/friend-codes/validate', [AuthController::class, 'validateFriendCode'])->name('api.friend-codes.validate');
Route::get('/register/{friendCode}', [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.store');

// Image serving routes - public access
Route::get('/image/thread/{id}', [App\Http\Controllers\ForumController::class, 'serveThreadImage'])->name('thread.image');
Route::get('/image/post/{id}', [App\Http\Controllers\ForumController::class, 'servePostImage'])->name('post.image');

// Mining dashboard - public access (shooting range)
Route::get('/mining', [App\Http\Controllers\MiningController::class, 'dashboard'])->name('mining.dashboard');

Route::post('/api/mining/submit-proof', [App\Http\Controllers\MiningController::class, 'submitMiningProof'])->name('mining.submit');
Route::get('/api/mining/stats', [App\Http\Controllers\MiningController::class, 'getStats'])->name('mining.stats');

// Bug bounty page
Route::get('/bounty', function () {
    return view('bounty');
})->name('bounty');


// Admin Updates API routes
Route::prefix('api/updates')->group(function () {
    Route::get('/global', [App\Http\Controllers\UpdatesController::class, 'getGlobalUpdates']);
    Route::get('/board/{boardCode}', [App\Http\Controllers\UpdatesController::class, 'getBoardUpdates']);
    Route::post('/post', [App\Http\Controllers\UpdatesController::class, 'postUpdate']);
    Route::delete('/{id}', [App\Http\Controllers\UpdatesController::class, 'deleteUpdate']);
    Route::get('/unread-count', [App\Http\Controllers\UpdatesController::class, 'getUnreadCount']);
});

// Self Mining API routes
Route::prefix('api/self-mining')->group(function () {
    Route::post('/submit', [App\Http\Controllers\SelfMiningController::class, 'submitPersonal21e8']);
    Route::get('/leaderboard', [App\Http\Controllers\SelfMiningController::class, 'getLeaderboard']);
});

// Doodle board routes - PUBLIC ACCESS (no authentication required)
Route::get('/ddl', function() {
    $board = \App\Models\Board::where('code', 'ddl')->firstOrFail();
    $controller = new \App\Http\Controllers\ForumController();
    return $controller->showBoard('ddl');
})->name('forum.board.ddl');
Route::get('/ddl/create', function() {
    $controller = new \App\Http\Controllers\ForumController();
    return $controller->createThread('ddl');
})->name('board.create.ddl');
Route::post('/ddl/create', function(\Illuminate\Http\Request $request) {
    $controller = new \App\Http\Controllers\ForumController();
    return $controller->storeThread($request, 'ddl');
})->name('board.create.store.ddl');
Route::get('/ddl/{threadId}', function($threadId) {
    $controller = new \App\Http\Controllers\ForumController();
    return $controller->showThread('ddl', $threadId);
})->name('forum.thread.ddl')->where('threadId', '[0-9]+');
Route::post('/ddl/{threadId}/reply', function(\Illuminate\Http\Request $request, $threadId) {
    $controller = new \App\Http\Controllers\ForumController();
    return $controller->storeReply($request, 'ddl', $threadId);
})->name('forum.reply.ddl')->where('threadId', '[0-9]+');


// Protected routes - require authentication
Route::middleware('bitcoin.auth')->group(function () {

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


    // Dynamic board routes - supports all boards: gen, tech, biz, film, x, lit, meta, mu, pol
    Route::group([], function () {
        // Reply to thread (MUST come before {board} routes to avoid conflicts)
        Route::post('/{board}/{threadId}/reply', [App\Http\Controllers\ForumController::class, 'storeReply'])
            ->name('forum.reply')
            ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images', 'threadId' => '[0-9]+']);

        // Thread creation (specific path, must come before {board})
        Route::get('/{board}/create', [App\Http\Controllers\ForumController::class, 'createThread'])
            ->name('board.create')
            ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images');

        // Thread creation POST (specific path)
        Route::post('/{board}/create', [App\Http\Controllers\ForumController::class, 'storeThread'])
            ->name('board.create.store')
            ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images');

        // Board catalog (specific path, must come before {board})
        Route::get('/{board}/catalog', [App\Http\Controllers\ForumController::class, 'showCatalog'])
            ->name('board.catalog')
            ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images');

        // Thread view (specific path, must come before {board})
        Route::get('/{board}/{threadId}', [App\Http\Controllers\ForumController::class, 'showThread'])
            ->name('forum.thread')
            ->where(['board' => 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images', 'threadId' => '[0-9]+']);

        // Thread creation fallback POST (less specific, comes after specific paths)
        Route::post('/{board}', [App\Http\Controllers\ForumController::class, 'storeThread'])
            ->name('board.thread.store')
            ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images');

        // Board main page (least specific, comes last)
        Route::get('/{board}', [App\Http\Controllers\ForumController::class, 'showBoard'])
            ->name('board.show')
            ->where('board', 'gen|tech|biz|film|x|lit|meta|mu|pol|ddl|i|General|Technology|Business|Meta|Film|Random|Literature|Music|Political|Doodles|Images');
    });

    // Point Shop routes (protected)
    Route::prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [App\Http\Controllers\PointShopController::class, 'index'])->name('index');
        Route::post('/purchase', [App\Http\Controllers\PointShopController::class, 'purchase'])->name('purchase');
    });

    // PoW Chat System routes (protected)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('index');
        Route::get('/{room}', [App\Http\Controllers\ChatController::class, 'show'])->name('room');
        Route::post('/{room}/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        Route::get('/{room}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('messages');
        Route::post('/{room}/join', [App\Http\Controllers\ChatController::class, 'joinRoom'])->name('join');
        Route::post('/{room}/leave', [App\Http\Controllers\ChatController::class, 'leaveRoom'])->name('leave');
        Route::delete('/{room}/messages/{message}', [App\Http\Controllers\ChatController::class, 'deleteMessage'])->name('delete-message');
        Route::get('/{room}/stats', [App\Http\Controllers\ChatController::class, 'getRoomStats'])->name('stats');
        Route::post('/{room}/set-nickname', [App\Http\Controllers\ChatController::class, 'setNickname'])->name('set-nickname');
        Route::get('/{room}/users', [App\Http\Controllers\ChatController::class, 'getOnlineUsers'])->name('users');
        Route::post('/{room}/command', [App\Http\Controllers\ChatController::class, 'executeCommand'])->name('command');
    });

    // User Profile routes (protected)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\UserProfileController::class, 'show'])->name('show');
        Route::post('/upload-favicon', [App\Http\Controllers\UserProfileController::class, 'uploadFavicon'])->name('upload-favicon');
    });
    
    // Default avatar generation (public)
    Route::get('/default-avatar/{hash}.png', [App\Http\Controllers\UserProfileController::class, 'generateIdenticon'])->name('default-avatar');

    // Image Library routes (protected)
    Route::get('/library', [App\Http\Controllers\ImageLibraryController::class, 'index'])->name('image-library.index');
    Route::post('/api/image-library/mine', [App\Http\Controllers\ImageLibraryController::class, 'mine']);
    Route::post('/api/image-library/upload', [App\Http\Controllers\ImageLibraryController::class, 'upload']);
    Route::get('/api/image-library/{id}/full', [App\Http\Controllers\ImageLibraryController::class, 'fullImage']);
    Route::get('/api/image-library/{id}/download', [App\Http\Controllers\ImageLibraryController::class, 'download']);
    Route::get('/api/image-library/hash/{hash}', [App\Http\Controllers\ImageLibraryController::class, 'getByHash']);
    Route::get('/api/image-library/stats', [App\Http\Controllers\ImageLibraryController::class, 'getStats']);
    Route::get('/api/image-library/search', [App\Http\Controllers\ImageLibraryController::class, 'search']);
    Route::get('/api/image-library/shifting', [App\Http\Controllers\ImageLibraryController::class, 'getShiftingArrangement']);

    // User profile routes
    Route::get('/user/dashboard', [App\Http\Controllers\UserController::class, 'showDashboard'])->name('user.dashboard');
    Route::get('/user/profile/edit', [App\Http\Controllers\UserController::class, 'showEditProfile'])->name('user.profile.edit');
    Route::post('/user/profile/update', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/user/{userId}', [App\Http\Controllers\UserController::class, 'showUserProfile'])->name('user.profile');

    // Static pages
    Route::get('/rules', function () {
        return view('static.rules');
    });

    Route::get('/faq', function () {
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
        Route::post('/threads/{id}/unpin', [App\Http\Controllers\AdminController::class, 'unpinThread'])->name('threads.unpin');
        Route::post('/threads/{id}/lock', [App\Http\Controllers\AdminController::class, 'lockThread'])->name('threads.lock');
        Route::delete('/threads/{id}/delete', [App\Http\Controllers\AdminController::class, 'deleteThread'])->name('threads.delete');
        Route::delete('/posts/{id}/delete', [App\Http\Controllers\AdminController::class, 'deletePost'])->name('posts.delete');

        // Genesis Code Management
        Route::post('/genesis-codes', [App\Http\Controllers\AdminController::class, 'createGenesisCode'])->name('genesis-codes.store');

        // API endpoints
        Route::get('/api/activity', [App\Http\Controllers\AdminController::class, 'getActivity'])->name('api.activity');
        Route::get('/api/invite-codes', [App\Http\Controllers\AdminController::class, 'getInviteCodes'])->name('api.invite-codes');
        Route::post('/api/invite-codes/{code}/deactivate', [App\Http\Controllers\AdminController::class, 'deactivateInviteCode'])->name('api.invite-codes.deactivate');
        Route::delete('/api/invite-codes/{code}', [App\Http\Controllers\AdminController::class, 'deleteInviteCode'])->name('api.invite-codes.delete');

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
                'destroy' => 'keys.destroy',
            ],
        ]);
    });
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
