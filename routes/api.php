<?php

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\Auth\OtpVerifyController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FileUpload\UserProfileController;

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
    Route::get('/user/me', function () {
        return Auth::user();
    });

    Route::post('/upload-id', [AuthController::class, 'uploadIdImage']);

    Route::apiResource('/events', EventController::class);
    Route::get('/artist/events', [EventController::class, 'showEventsByArtist']);
});

// Route::prefix('client')->middleware('client')->group(function () {
Route::apiResource('/artists', ArtistProfileController::class);
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{id}', [JobController::class, 'showJobsByClient']);
Route::apiResource('/job/offer', OfferController::class);
// });

Route::controller(OtpVerifyController::class)->group(function () {
    Route::post("/verify-otp", [OtpVerifyController::class, 'verify'])->name('otp.verify');
    Route::post('/resend-otp', 'resendOtp')->name('otp.resend');
});

Route::controller(AuthController::class)->group(function () {
    Route::middleware("admin")->group(function () {
        Route::get("/admin/users", [AdminController::class, 'getUsers']);
        Route::post("/admin/users/{user}/verify", [AdminController::class, 'verifyUser']);
        Route::post("/admin/users/{user}/toggle-is-active", [AdminController::class, 'toggleIsActive']);
        Route::post("/admin/users/{user}/set-verification-status", [AdminController::class, 'setVerificationStatus']);
        Route::post("/admin/users/{user}/get", [AdminController::class, 'getUser']);
    });

    Route::post('/register', 'registerUser')->name('auth.register');
    Route::post('/login', 'loginUser')->name('auth.login');
});


Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/notice', 'notice')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'verify')->name('verification.verify');
    Route::post('/email/resend', 'resend')->middleware(['throttle:6,1'])->name('verification.resend');
});

Route::controller(ResetPasswordController::class)->group(function () {
    Route::post('/forgot-password', 'forgotPassword')->name('password.request');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

Route::post('/upload-image', [UserProfileController::class, 'store']);

Route::post('/google-callback', [GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');