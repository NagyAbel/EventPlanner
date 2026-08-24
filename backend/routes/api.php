<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventTypeController;

Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::post('/signup', 'signup')->middleware('throttle:20,1');
    Route::post('/auth', 'auth')->middleware('throttle:20,1');
    

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
         Route::post('/validate-email', 'validateEmail')->middleware('throttle:30,1');
        Route::post('/name', 'update')->middleware('throttle:30,1');
        
        Route::get('/', function (Request $request) {
            return response()->json([
                'user' => $request->user(),
            ]);
        });
    });
});

Route::get('/event-types', [EventTypeController::class, 'index'])->middleware('throttle:30,1');

Route::prefix('event')->controller(EventController::class)->group(function () {
    Route::get('/', 'index')->middleware('throttle:60,1');    
    Route::get('/show/{event}', 'show')->middleware('throttle:60,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/create', 'create')->middleware('throttle:20,1');
        Route::put('/update/{event}', 'update')->middleware('throttle:30,1');
        Route::delete('/delete/{event}', 'delete')->middleware('throttle:20,1');
        Route::post('/attend/{event}', 'attend')->middleware('throttle:30,1');
    });
});