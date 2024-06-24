<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EncryptionController;
use App\Http\Middleware\ValidateUserHasPublicKey;

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
    Route::group([
        // Here we validate that the user has a public key saved
        'middleware' => ValidateUserHasPublicKey::class,
    ], function() {
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/message', [MessageController::class, 'store']);
        Route::get('/file/{messageId}', [MessageController::class, 'downloadFile']);
    });
});


Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => '/user',
], function() {
    Route::get('/', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'update']);
});

Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => '/encryption',
], function() {
    Route::get('/server/public-key', [EncryptionController::class, 'getServerPublicKey']);
    Route::put('/user/public-key', [EncryptionController::class, 'updateUserPublicKey']);
});
