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


Route::post('/otp-verification',[AuthController::class,'otpVerification']);



Route::post('/signin', [AuthController::class, 'signin']);

Route::post('/forgot_password',[AuthController::class,'forgotPassword']);






// Authenticated Routes (using Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
  ############### Admin Routes ##################
    Route::middleware('role_id:1')->group(function () {
        Route::get('admin/dashboard', function() {
            return response()->json(['message' => 'Admin Dashboard']);
        });
    });

   ################ Vendor Routes ######################3
    Route::middleware('role_id:2')->group(function () {
        Route::get('vendor/dashboard', function() {
            return response()->json(['message' => 'Vendor Dashboard']);
        });
    });

   ###################### User Routes #########################3
    Route::middleware('role_id:3')->group(function () {
        Route::get('user/dashboard', function() {
            return response()->json(['message' => 'User Dashboard']);
        });
    });

    ################ Logout Route ############################33
    Route::post('/logout',[AuthController::class,'logout']);
});