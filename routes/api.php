<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

#####################  Auth Api ##################################

Route::post('/signup', [AuthController::class, 'signup']);                    //done //pending
Route::post('/otp-verification', [AuthController::class, 'otpVerification']); //done
Route::post('/signin', [AuthController::class, 'signin']);                    //done
Route::post('/forgot_password', [AuthController::class, 'forgotPassword']);   //NT


#####################  Category  ##################################
Route::get('/get-category', [CategoryController::class, 'index']); //NT
// Route::get()

// Route::post('profile-update',[U])

// Authenticated Routes (using Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    ############### Admin Routes For Web developement ##################
    // Route::middleware('role_id:1')->group(function () {
    //     Route::get('admin/dashboard', function() {
    //         return response()->json(['message' => 'Admin Dashboard']);
    //     });
    // });

    ################ Vendor Routes ######################
    Route::middleware('role_id:2')->group(function () {
        Route::get('vendor/dashboard', function () {
            return response()->json(['message' => 'Vendor Dashboard']);
        });
    });

    ###################### User Routes #########################3
    Route::middleware('role_id:3')->group(function () {
        Route::get('user/dashboard', function () {
            return response()->json(['message' => 'User Dashboard']);
        });
    });

    ################ Logout Route ############################33
    Route::post('/logout', [AuthController::class, 'logout']);
});
