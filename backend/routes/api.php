<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/auth', 'auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/name','update');
        Route::get('/', function (Request $request) {
            return response()->json([
                'user' => $request->user(),
            ]);
        });
    });
});

Route::prefix('event')->controller(EventController::class)->group(function () {
    Route::get('/show/{event}', 'show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/own','own');
        Route::post('/create','store');
        Route::put('/update/{event}', 'update');
    });
});