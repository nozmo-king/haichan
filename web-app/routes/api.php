<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProofController;

// API Authentication routes
Route::post('/auth/challenge', [AuthApiController::class, 'getChallenge'])->middleware('throttle:25,1');
Route::post('/auth/login', [AuthApiController::class, 'login'])->middleware('throttle:25,1');

// Unified Proof of Work system (no auth required)
Route::post('/proof', [ProofController::class, 'submit']);
Route::get('/proof/stats', [ProofController::class, 'stats']);

// Public subscription routes (requires public key but not auth token)
Route::post('/subscription/activate-for-key', [App\Http\Controllers\Api\SubscriptionApiController::class, 'activateForPublicKey'])->middleware('throttle:25,1');

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    
    // Subscription API endpoints
    Route::get('/subscription/status', [App\Http\Controllers\Api\SubscriptionApiController::class, 'getStatus']);
    Route::post('/subscription/activate', [App\Http\Controllers\Api\SubscriptionApiController::class, 'activate']);
    Route::post('/subscription/cancel', [App\Http\Controllers\Api\SubscriptionApiController::class, 'cancel']);
    
    // Forum API endpoints
    Route::get('/boards', [ForumApiController::class, 'getBoards']);
    Route::get('/boards/{code}', [ForumApiController::class, 'getBoard']);
    Route::get('/boards/{code}/threads', [ForumApiController::class, 'getThreads']);
    Route::post('/boards/{code}/threads', [ForumApiController::class, 'createThread']);
    Route::get('/boards/{code}/threads/{thread}', [ForumApiController::class, 'getThread']);
    Route::post('/boards/{code}/threads/{thread}/replies', [ForumApiController::class, 'createReply']);
});