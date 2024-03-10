<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\FileUpload\UserProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\OtpVerifyController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json(['user' => "Alemu SISAY IT WORKS"], 200);
       });

});

Route::controller(OtpVerifyController::class)->group(function(){
    Route::post("/verify-otp", [OtpVerifyController::class, 'verify'])->name('otp.verify');
    Route::post('/resend-otp', 'resendOtp')->name('otp.resend');
});

Route::controller(AuthController::class)->group(function(){
    Route::post('/register', 'registerUser')->name('auth.register');
    Route::post('/login', 'loginUser')->name('auth.login');
});


Route::controller(VerificationController::class)->group(function(){
    Route::get('/email/notice', 'notice')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'verify')->name('verification.verify');
    Route::post('/email/resend', 'resend')->middleware(['throttle:6,1'])->name('verification.resend');
});

Route::controller(ResetPasswordController::class)->group(function(){
    Route::post('/forgot-password', 'forgotPassword')->name('password.request');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

Route::post('/upload-image',  [UserProfileController::class,'store']);

Route::post('/google-callback',[GoogleLoginController::class,'handleGoogleCallback'])->name('google.callback');