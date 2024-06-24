<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;

Route::group([
    'prefix' => '/auth',
], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group([
    'prefix' => '/chat',
    'middleware' => 'auth:sanctum',
], function() {
    Route::post('/', [ChatController::class, 'store']);
    Route::get('/', [ChatController::class, 'index']);
    Route::put('/{id}', [ChatController::class, 'updateStatus']);
    Route::get('/contacts', [ChatController::class, 'getContacts']);

    // Messages
    Route::get('/messages/{chatId}', [MessageController::class, 'indexByChat']);
    Route::post('/messages', [MessageController::class, 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
