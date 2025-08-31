<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FriendCodeController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/challenge', [AuthController::class, 'getChallenge'])->middleware('throttle:25,1')->name('auth.challenge');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:25,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Admin routes (protected by auth)
Route::middleware('require.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('keys', AdminController::class)->names([
        'index' => 'keys.index',
        'create' => 'keys.create', 
        'store' => 'keys.store',
        'edit' => 'keys.edit',
        'update' => 'keys.update',
        'destroy' => 'keys.destroy'
    ]);
});

// Subscription routes (public access for viewing plans)
Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');

// Registration routes
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('auth.register.form');
Route::get('/register/{friend_code}', [AuthController::class, 'showRegister'])
    ->middleware('validate.friend.code')
    ->name('auth.register');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.submit');

// Protected subscription and friend code routes
Route::middleware('require.auth')->group(function () {
    // Subscription management
    Route::post('/subscription/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('/subscription/dashboard', [SubscriptionController::class, 'dashboard'])->name('subscription.dashboard');
    Route::post('/subscription/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/api/subscription/status', [SubscriptionController::class, 'status'])->name('api.subscription.status');
    
    // Friend code management
    Route::get('/friend-codes', [FriendCodeController::class, 'index'])->name('friend-codes.index');
    Route::post('/friend-codes/generate', [FriendCodeController::class, 'generate'])->name('friend-codes.generate');
    Route::get('/api/friend-codes/stats', [FriendCodeController::class, 'stats'])->name('api.friend-codes.stats');
    
    // Payment management
    Route::get('/payment/{payment}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payment.confirm');
    Route::get('/payment/{payment}/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    Route::get('/api/payment/{payment}/status', [PaymentController::class, 'status'])->name('api.payment.status');
});

// Landing page for non-authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->hasActiveSubscription()) {
            return app(ForumController::class)->index();
        } else {
            return redirect()->route('subscription.plans')
                ->with('info', 'Welcome! You need an active subscription to access the forum.');
        }
    } else {
        return redirect()->route('subscription.plans')
            ->with('info', 'Welcome to our exclusive forum community! Please register or login to continue.');
    }
})->name('home');

// CSRF token refresh endpoint for long-running operations
Route::middleware('require.auth')->get('/api/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('api.csrf-token');

// Debug logs page (temporary for debugging)
Route::middleware('require.auth')->get('/debug-logs', function () {
    return view('debug-logs');
})->name('debug.logs');

// Session keep-alive endpoint for mining
Route::middleware('require.auth')->post('/api/keep-alive', function (Illuminate\Http\Request $request) {
    // Log client-side messages if provided
    if ($request->has('client_log')) {
        \Log::info('=== CLIENT LOG ===', [
            'client_message' => $request->input('client_log'),
            'client_data' => $request->input('client_data'),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toDateTimeString(),
            'url' => $request->header('Referer')
        ]);
    }
    
    return response()->json([
        'status' => 'alive',
        'timestamp' => now()->toDateTimeString(),
        'session_id' => session()->getId(),
        'user_id' => auth()->id()
    ]);
})->name('api.keep-alive');


// Protected forum routes (with subscription requirement)
Route::middleware(['require.auth', 'require.subscription'])->group(function () {
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/{code}', [ForumController::class, 'showBoard'])->name('forum.board');
    Route::get('/{code}/create', [ForumController::class, 'createThread'])->name('forum.create');
    Route::post('/{code}/create', [ForumController::class, 'storeThread'])->name('forum.store');
    Route::get('/{code}/thread/{thread}', [ForumController::class, 'showThread'])->name('forum.thread');
    Route::post('/{code}/thread/{thread}/reply', [ForumController::class, 'storeReply'])->name('forum.reply');
});

// Public API endpoints for friend code validation
Route::post('/api/friend-codes/validate', [FriendCodeController::class, 'validate'])->name('api.friend-codes.validate');

// Stripe webhook (public endpoint, no auth required)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');
