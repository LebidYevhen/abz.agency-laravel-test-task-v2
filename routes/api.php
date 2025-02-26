<?php

use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {
    Route::get('/positions', [PositionController::class, 'index']);

    Route::post('/users', [UserController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    Route::get('/token', [TokenController::class, 'generateToken']);
});
