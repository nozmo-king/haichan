<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\AuthApiController;

// API Authentication routes
Route::post('/auth/challenge', [AuthApiController::class, 'getChallenge'])->middleware('throttle:25,1');
Route::post('/auth/login', [AuthApiController::class, 'login'])->middleware('throttle:25,1');

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