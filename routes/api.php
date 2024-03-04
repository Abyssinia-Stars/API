<?php

use App\Http\Controllers\ArtistProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', 'App\Http\Controllers\Auth\AuthController@registerUser')->name('auth.register');
Route::post('/login', 'App\Http\Controllers\Auth\AuthController@loginUser')->name('auth.login');
Route::get('/login', 'App\Http\Controllers\Auth\AuthController@loginView')->name('auth.login');
Route::get('/email/notice', 'App\Http\Controllers\Auth\VerificationController@notice')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', 'App\Http\Controllers\Auth\VerificationController@verify')->name('verification.verify');
Route::post('/email/resend', 'App\Http\Controllers\Auth\VerificationController@resend')->middleware(['throttle:6,1'])->name('verification.resend');
Route::post('/upload-image',  'App\Http\Controllers\FileUpload\UserProfileController@store');
Route::post('/forgot-password', 'App\Http\Controllers\Auth\ResetPasswordController@forgotPassword')->name('password.request');
Route::post('/reset-password', 'App\Http\Controllers\Auth\ResetPasswordController@resetPassword')->name('password.update');

Route::get('/artist-profile', [ArtistProfileController::class, 'index']);
Route::post('/artist-profile', [ArtistProfileController::class, 'store']);
Route::get('/artist-profile/{id}', [ArtistProfileController::class, 'show']);