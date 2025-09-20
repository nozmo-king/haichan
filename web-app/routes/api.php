<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProofController;

// API Authentication routes
Route::post('/auth/challenge', [AuthApiController::class, 'getChallenge'])->middleware('throttle:25,1');
Route::post('/auth/login', [AuthApiController::class, 'login'])->middleware('throttle:25,1');

// Public API routes (minimal, no auth required)
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Development/testing endpoint to add public keys (remove in production)
Route::post('/dev/add-public-key', function (Illuminate\Http\Request $request) {
    $request->validate([
        'public_key' => 'required|string|size:66'
    ]);

    $existingKey = App\Models\AllowedPublicKey::where('public_key', $request->public_key)->first();

    if ($existingKey) {
        return response()->json(['message' => 'Public key already exists', 'id' => $existingKey->id]);
    }

    $allowedKey = App\Models\AllowedPublicKey::create([
        'public_key' => $request->public_key,
        'created_by' => 1, // Admin user
        'is_active' => true
    ]);

    return response()->json(['message' => 'Public key added successfully', 'id' => $allowedKey->id]);
})->middleware('throttle:10,1');

// Friend Code validation endpoint (public - for registration)
Route::post('/friend-codes/validate', [AuthApiController::class, 'validateFriendCode'])->middleware('throttle:10,1');

// Debug endpoints (remove in production)
Route::post('/debug/signature', [AuthApiController::class, 'debugSignature'])->middleware('throttle:20,1');
Route::get('/debug/test-challenge', [AuthApiController::class, 'createTestChallenge'])->middleware('throttle:20,1');
Route::post('/debug/compare', [AuthApiController::class, 'compareImplementations'])->middleware('throttle:20,1');

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication endpoints
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    // Forum API endpoints
    Route::get('/boards', [ForumApiController::class, 'getBoards']);
    Route::get('/boards/metadata', [ForumApiController::class, 'getBoardsMetadata']);
    Route::get('/boards/{code}', [ForumApiController::class, 'getBoard']);
    Route::get('/boards/{code}/threads', [ForumApiController::class, 'getThreads']);
    Route::post('/boards/{code}/threads', [ForumApiController::class, 'createThread']);
    Route::get('/boards/{code}/threads/{thread}', [ForumApiController::class, 'getThread']);
    Route::post('/boards/{code}/threads/{thread}/replies', [ForumApiController::class, 'createReply']);

    // Proof of Work system
    Route::post('/proof', [ProofController::class, 'submit']);
    Route::get('/proof/stats', [ProofController::class, 'stats']);

    // Image Library endpoints
    Route::post('/image-library/mine', [App\Http\Controllers\ImageLibraryController::class, 'mine']);
    Route::post('/image-library/upload', [App\Http\Controllers\ImageLibraryController::class, 'upload']);
    Route::get('/image-library/{id}/full', [App\Http\Controllers\ImageLibraryController::class, 'fullImage']);
    Route::get('/image-library/{id}/download', [App\Http\Controllers\ImageLibraryController::class, 'download']);
    Route::get('/image-library/hash/{hash}', [App\Http\Controllers\ImageLibraryController::class, 'getByHash']);
    Route::get('/image-library/stats', [App\Http\Controllers\ImageLibraryController::class, 'getStats']);
    Route::get('/image-library/search', [App\Http\Controllers\ImageLibraryController::class, 'search']);
    Route::get('/image-library/shifting', [App\Http\Controllers\ImageLibraryController::class, 'getShiftingArrangement']);
});