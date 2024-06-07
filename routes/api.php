<?php

use App\Http\Middleware\SubscriptionMiddleware;

use App\Http\Controllers\PaymentInfoController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TxnHistoryController;
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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\ReportController;
use App\Events\SendNotificationTry;
use App\Events\TryMessage;
use App\Http\Controllers\EventBlogsController;
use App\Jobs\SendNotification;
use App\Jobs\HandleMessage;
use App\Models\ArtistProfile;
use App\Models\EventBlogs;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    Route::get('user/me', [AuthController::class, 'me']);
    Route::post('/upload-id', [AuthController::class, 'uploadIdImage']);
    Route::apiResource('/events', EventController::class);
    Route::get('/artist/events', [EventController::class, 'showEventsByArtist']);
    Route::post('/profile', [UserProfileController::class, 'update']);
    Route::delete('/profile', [UserProfileController::class, 'destroy']);

    Route::apiResource('/artists', ArtistProfileController::class)->only('store');

    Route::prefix("manager")->middleware('manager')->group(function () {
        Route::post('/send-request/{userId}', [ManagerController::class, 'sendRequest']);
        Route::post('/response/{notificationId}', [ManagerController::class, 'handleResponse']);
        Route::get('/', [ManagerController::class, 'getNotifications']);
    });

    Route::prefix("artist")->middleware('artist')->group(function () {
        Route::post('/send-request/{userId}', [ArtistProfileController::class, 'sendRequest']);
        Route::post('/response/{notificationId}', [ArtistProfileController::class, 'handleResponse']);
        Route::get('/', [ArtistProfileController::class, 'getNotifications']);
    });
    Route::prefix('customer')->middleware('customer')->group(function () {
        Route::apiResource('/artists', ArtistProfileController::class)->only('index', 'show');
        Route::get('/jobs', [JobController::class, 'index']);
        Route::get('/job/{id}', [JobController::class, 'getJob']);
        Route::post("/job", [JobController::class, 'store']);
        Route::delete('/job/{id}', [JobController::class, 'destroy']);
        Route::get('/jobs/{id}', [JobController::class, 'showJobsByClient']);
        Route::post('/job/offer', [OfferController::class, 'store']);
        Route::post("favorites/{userId}", [CustomerController::class, 'addArtistToFavorites']);
        Route::get("favorites", [CustomerController::class, 'getFavorites']);
        Route::delete("favorites/{userId}", [CustomerController::class, 'removeArtistFromFavorites']);
        Route::post("reviews/{userId}", [CustomerController::class, 'addReview']);
        Route::delete("reviews/{userId}", [CustomerController::class, 'removeReview']);
        Route::get("reviews", [CustomerController::class, 'getReviews']);
        Route::get('/artist/profile/{id}/{auth}', [ArtistProfileController::class, 'getArtistProfileWithAuth']);
    Route::get("/offers/{id}", [OfferController::class, "showOffersByClient"]);
    Route::delete("/offers/{id}", [OfferController::class, "destroy"]);
    Route::put("/jobs/{id}/completed", [OfferController::class, "jobIsOver"]);

    });

    // payment
    Route::get('/payment-info/get', [PaymentInfoController::class, 'getPaymentInfo']);
    Route::post('/payment-info', [PaymentInfoController::class, 'store']);
    Route::patch('/payment-info', [PaymentInfoController::class, 'update']);
    Route::delete('/payment-info', [PaymentInfoController::class, 'destroy']);
    Route::get('/txn-history', [TxnHistoryController::class, 'index']);

    Route::post("/deposit", [BalanceController::class, 'store']);
    Route::get('/balance', [BalanceController::class, 'getBalance']);
    Route::post('/withdraw', [BalanceController::class, 'withdraw']);
    Route::get('/banks', [BalanceController::class, 'getBanks']);
    Route::get('/txn-history', [TxnHistoryController::class, 'index']);

    // subscription
    Route::post("/subscribe/{id}", [SubscriptionController::class, "subscribe"]);
    Route::post("/subscribe/manager/{number_of_people}", [SubscriptionController::class, "managerSubscription"]);
    Route::post("/buyOffer", [SubscriptionController::class, "buyOffer"]);


    Route::get("/artist/offers", [OfferController::class, "showOffersByArtist"]);
    Route::put("/artist/offers/{id}/{status}", [OfferController::class, "acceptOffer"]);


    //manager
    Route::get('/manager/offers', [OfferController::class, 'showOffersByManager']);
    Route::delete("/manager/remove/{id}", [ManagerController::class , "removeManager"]);
    Route::delete("/artist/remove/{id}", [ManagerController::class, "removeArtist"]);
    Route::get('/manager/profile/{id}', [ManagerController::class, 'getManagerProfile']);
    Route::post("/manager", [ManagerController::class, "store"]);
    Route::get("manager/pendingRequest", [ManagerController::class, "pendingRequests"]);

    //notification
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::put('/notifications/{id}/{status}', [NotificationController::class, 'update']);



    //conversations
    Route::get('/conversations/{id}', [ConversationsController::class, 'index']);
    Route::delete('/conversations/{id}', [ConversationsController::class, 'destroy']);
    Route::get('/conversations', [ConversationsController::class, 'show']);
    Route::post('/conversations/messages/{participentId}', [ConversationsController::class, 'getConversationData']);

    //messages

    Route::get("/messages/{conversationId}", [MessagesController::class, 'index']);
    Route::post("message", [MessagesController::class, 'store']);
    Route::put("/messages/{id}", [MessagesController::class, 'update']);

    //attachments

    Route::delete("/attachments/{attachmentName}", [ArtistProfileController::class, 'deleteAttachment']);

    //reports

    Route::post("/report", [ReportController::class, 'create']);
    Route::get("/reports", [ReportController::class, 'show']);
    Route::post("/report/{id}", [ReportController::class, 'reportReviewed']);

    //test subscription

    Route::get("/premFeature", function () {

        return response()->json([
            'message' => 'success'
        ]);
    })->middleware(SubscriptionMiddleware::class);

});

Route::get("/plans", [PlansController::class, "index"]);
Route::post("/subscribe/callback/{reference}", [SubscriptionController::class, "callback"])->name("subscription_callback");

Route::get("/callback/{reference}", [BalanceController::class, 'callback'])->name('callback');
Route::get('/random-artists', [CustomerController::class, 'getRandomAritsts']);
Route::get('/random-categories', [CustomerController::class, 'getRandomCategories']);
Route::get('/popular-artists', [CustomerController::class, 'getVerifiedArtists']);
Route::get('/get-random-artists', [CustomerController::class, 'getRandomAritsts']);
Route::get('/get-random-categories', [CustomerController::class, 'getRandomCategories']);
Route::get('/get-popular-artists', [CustomerController::class, 'getPopularArtistsByRating']);
Route::get("reviews", [ArtistProfileController::class, 'getReviews']);
Route::get("/artists", [CustomerController::class, 'getArtistByParams']);
Route::get("/findArtists", [CustomerController::class, 'searchArtists']);
Route::get('/artist/profile/{id}', [ArtistProfileController::class, 'getArtistProfile']);

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

    Route::prefix("admin")->middleware("admin")->group(function () {
        Route::get("main_transactions", [AdminController::class, 'getMainTransactionsAndBalance']);
    });

    Route::post('/register', 'registerUser')->name('auth.register');
    Route::post('/login', 'loginUser')->name('auth.login');
});

Route::get("/notification_try", function () {
    // broadcast(new SendNotificationTry());
    // SendNotification::dispatch();
    // HandleMessage::dispatch()
    broadcast(new TryMessage());
    return response()->json([
        'a' => 'b'
    ]);
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


Route::apiResource('/eventblogs', EventBlogsController::class);
