<?php

use App\Http\Controllers\UserController;
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