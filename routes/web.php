<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/login', function () {
//     return view('auth.login');
// })->name('login');

Route::controller(AuthController::class)->group(function () {
    Route::get('/register', 'registerUser')->name("auth.register");
    Route::get('/login', 'loginUser')->name("auth.login");
});


Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/verify', 'notice')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'verify')->name('verification.verify');
    Route::get('/email/resend', 'resend')->name('verification.resend');
})->middleware(['auth','signed'])->name('verification.verify');
