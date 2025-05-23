<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;


Route::middleware('api')->group(function () {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});
Route::middleware('auth:api',CheckAdmin::class)->group(function () {
    // Admin-only 
    Route::get('/users', [UserController::class, 'index']);
});
Route::middleware('auth:api')->group(function () {

    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/user/edit', [UserController::class, 'edit']);
});
