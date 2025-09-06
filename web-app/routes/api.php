<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\AuthApiController;

// API Authentication routes
Route::post('/auth/challenge', [AuthApiController::class, 'getChallenge'])->middleware('throttle:25,1');
Route::post('/auth/login', [AuthApiController::class, 'login'])->middleware('throttle:25,1');

// Mining/Proof of Work routes (no auth required)
Route::post('/submit-proof', [App\Http\Controllers\ProofOfWorkController::class, 'submitProof']);
Route::post('/post-bump', [App\Http\Controllers\ProofOfWorkController::class, 'postBump']);
Route::post('/{boardName}/thread/{threadId}/bump', [App\Http\Controllers\ProofOfWorkController::class, 'bumpThread']);
Route::get('/mining-stats', [App\Http\Controllers\ProofOfWorkController::class, 'getStats']);
Route::post('/start-mining-session', [App\Http\Controllers\ProofOfWorkController::class, 'startMiningSession']);
Route::post('/end-mining-session', [App\Http\Controllers\ProofOfWorkController::class, 'endMiningSession']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    
    // Forum API endpoints
    Route::get('/boards', [ForumApiController::class, 'getBoards']);
    Route::get('/boards/{code}', [ForumApiController::class, 'getBoard']);
    Route::get('/boards/{code}/threads', [ForumApiController::class, 'getThreads']);
    Route::post('/boards/{code}/threads', [ForumApiController::class, 'createThread']);
    Route::get('/boards/{code}/threads/{thread}', [ForumApiController::class, 'getThread']);
    Route::post('/boards/{code}/threads/{thread}/replies', [ForumApiController::class, 'createReply']);
});