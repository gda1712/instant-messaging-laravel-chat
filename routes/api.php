<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;

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
//    Route::put('/{id}', [ChatController::class, 'updateStatus']);
    Route::get('/contacts', [ChatController::class, 'getContacts']);
    Route::delete('/{id}', [ChatController::class, 'destroy']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/message', [MessageController::class, 'store']);
    Route::get('/file/{messageId}', [MessageController::class, 'downloadFile']);
});


Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => '/user',
], function() {
    Route::get('/', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'update']);
});
