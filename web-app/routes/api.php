<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PowController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('pow')->group(function () {
    Route::get('/params', [PowController::class, 'getPowParams']);
});

Route::prefix('thread')->group(function () {
    Route::post('/begin', [PowController::class, 'beginThread']);
    Route::post('/commit', [PowController::class, 'commitThread']);
});

Route::prefix('reply')->group(function () {
    Route::post('/begin', [PowController::class, 'beginReply']);
    Route::post('/commit', [PowController::class, 'commitReply']);
});