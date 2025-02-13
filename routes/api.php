<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



#####################  Auth Api ##################################

Route::post('/signup', [AuthController::class, 'signup']);


Route::get('/otp-verification',[AuthController::class,'otpVerification']);


Route::post('/signin', [AuthController::class, 'signin']);


Route::post('/forgot_password',[AuthController::class,'forgotPassword']);

Route::post('/logout',[AuthController::class,'logout']);



// 🔹 Protected Routes (Requires Sanctum Authentication)
Route::middleware([
    EnsureFrontendRequestsAreStateful::class, 
    'auth:sanctum'
])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->middleware(CheckRole::class . ':1'); // Only Admin (role_id = 1)

    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])
        ->middleware(CheckRole::class . ':2'); // Only User (role_id = 2)

    Route::post('/logout', [AuthController::class, 'logout']); // Logout
});