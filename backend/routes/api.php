<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventTypeController;

Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/auth', 'auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/name', 'update');
        Route::get('/', function (Request $request) {
            return response()->json([
                'user' => $request->user(),
            ]);
        });
    });
});
Route::get('/event-types', [EventTypeController::class, 'index']);
Route::prefix('event')->controller(EventController::class)->group(function () {
    Route::get('/', 'index');    
    Route::get('/show/{event}', 'show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/create', 'create');
        Route::put('/update/{event}', 'update');
        Route::delete('/delete/{event}', 'delete');
        Route::post('/attend/{event}', 'attend');
    });
});