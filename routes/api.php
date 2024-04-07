<?php

use App\Http\Controllers\PaymentInfoController;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\Auth\OtpVerifyController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FileUpload\UserProfileController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\CustomerController;

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
<<<<<<< Updated upstream
    Route::get('/artist/profile/{id}', [ArtistProfileController::class, 'getArtistProfile']);
=======
>>>>>>> Stashed changes

    Route::apiResource('/artists', ArtistProfileController::class)->only('store');

    Route::prefix("notification/manager")->middleware('manager')->group(function () {
        Route::post('/send-request/{userId}', [ManagerController::class, 'sendRequest']);
        Route::post('/response/{notificationId}', [ManagerController::class, 'handleResponse']);
        Route::get('/', [ManagerController::class, 'getNotifications']);
    });

    Route::prefix("notification/artist")->middleware('artist')->group(function () {

        Route::post('/send-request/{userId}', [ArtistProfileController::class, 'sendRequest']);
        Route::post('/response/{notificationId}', [ArtistProfileController::class, 'handleResponse']);
        Route::get('/', [ArtistProfileController::class, 'getNotifications']);
    });

    Route::prefix('customer')->middleware('customer')->group(function () {
        Route::apiResource('/artists', ArtistProfileController::class)->only('index', 'show');
        Route::get('/jobs', [JobController::class, 'index']);
        Route::get('/jobs/{id}', [JobController::class, 'showJobsByClient']);
        Route::apiResource('/job/offer', OfferController::class);
        Route::post("favorites/add/{userId}", [CustomerController::class, 'addArtistToFavorites']);
        Route::get("favorites", [CustomerController::class, 'getFavorites']);
        Route::delete("favorites/remove/{userId}", [CustomerController::class, 'removeArtistFromFavorites']);
        Route::post("reviews/add/{userId}", [CustomerController::class, 'addReview']);
        Route::delete("reviews/remove/{userId}", [CustomerController::class, 'removeReview']);
        Route::get("reviews", [CustomerController::class, 'getReviews']);

<<<<<<< Updated upstream
        Route::get('/payment-info/get', [PaymentInfoController::class, 'getPaymentInfo']);
        Route::apiResource('/payment-info', PaymentInfoController::class);
    });

});

=======
        Route::get('/artist/profile/{id}/{auth}', [ArtistProfileController::class, 'getArtistProfileWithAuth']);
    });

    // payment
    Route::get('/payment-info/get', [PaymentInfoController::class, 'getPaymentInfo']);
    Route::apiResource('/payment-info', PaymentInfoController::class);

    Route::post("/deposit", [BalanceController::class, 'store']);
    Route::get('/balance', [BalanceController::class, 'getBalance']);
});


Route::get("/callback/{reference}", [BalanceController::class, 'callback'])->name('callback');
Route::get('/random-artists', [CustomerController::class, 'getRandomAritsts']);
Route::get('/random-categories', [CustomerController::class, 'getRandomCategories']);
Route::get('/popular-artists', [CustomerController::class, 'getPopularArtistsByRating']);
>>>>>>> Stashed changes
Route::get('/get-random-artists', [CustomerController::class, 'getRandomAritsts']);
Route::get('/get-random-categories', [CustomerController::class, 'getRandomCategories']);
Route::get('/get-popular-artists', [CustomerController::class, 'getPopularArtistsByRating']);
Route::get("reviews", [ArtistProfileController::class, 'getReviews']);
Route::get("/search-artists", [CustomerController::class, 'getArtistByParams']);

Route::controller(OtpVerifyController::class)->group(function () {
    Route::post("/verify-otp", [OtpVerifyController::class, 'verify'])->name('otp.verify');
    Route::post('/resend-otp', 'resendOtp')->name('otp.resend');
});

Route::controller(AuthController::class)->group(function () {
    Route::prefix("/admin/users")->middleware("admin")->group(function () {
        Route::get("/", [AdminController::class, 'getUsers']);
        Route::post("/{user}/verify", [AdminController::class, 'verifyUser']);
        Route::patch("{user}/toggle-is-active", [AdminController::class, 'toggleIsActive']);
        Route::post("/{user}/set-verification-status", [AdminController::class, 'setVerificationStatus']);
        Route::post("/{user}/get", [AdminController::class, 'getUser']);
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
