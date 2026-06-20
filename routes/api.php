<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\{
    AuthController,
    SearchController,
    HostelController,
    MessController,
    PaymentController,
    ReviewController,
    FavouriteController,
    OwnerHostelController,
    OwnerMessController,
    AdminController,
};

/*
|--------------------------------------------------------------------------
| API Routes v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public Auth ───────────────────────────────────────────
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/verify-otp',      [AuthController::class, 'verifyEmailOtp']);
    Route::post('/resend-otp',      [AuthController::class, 'resendOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

    // ── Public Listings ───────────────────────────────────────
    Route::get('/search/nearby', [SearchController::class, 'nearby']);
    Route::get('/hostels',       [HostelController::class, 'index']);
    Route::get('/hostels/{slug}',[HostelController::class, 'show']);
    Route::get('/messes',        [MessController::class, 'index']);
    Route::get('/messes/{slug}', [MessController::class, 'show']);

    // ── Authenticated Routes ──────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Profile
        Route::get('/me',               [AuthController::class, 'me']);
        Route::post('/profile',         [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout',          [AuthController::class, 'logout']);

        // Plans & Subscription (owners)
        Route::get('/plans',                           [PaymentController::class, 'plans']);
        Route::post('/subscription/create-order',      [PaymentController::class, 'createSubscriptionOrder']);
        Route::post('/subscription/verify',            [PaymentController::class, 'verifySubscriptionPayment']);

        // Bookings (users)
        Route::get('/bookings',                        [PaymentController::class, 'myBookings']);
        Route::post('/bookings/hostel/create-order',   [PaymentController::class, 'createHostelBookingOrder']);
        Route::post('/bookings/hostel/verify',         [PaymentController::class, 'verifyHostelBooking']);
        Route::delete('/bookings/hostel/{id}',         [PaymentController::class, 'cancelHostelBooking']);
        Route::post('/bookings/mess/create-order',     [PaymentController::class, 'createMessBookingOrder']);
        Route::post('/bookings/mess/verify',           [PaymentController::class, 'verifyMessBooking']);

        // Reviews
        Route::post('/reviews',             [ReviewController::class, 'store']);
        Route::post('/reviews/{id}/helpful',[ReviewController::class, 'markHelpful']);

        // Favourites
        Route::post('/favourites',  [FavouriteController::class, 'toggle']);
        Route::get('/favourites',   [FavouriteController::class, 'index']);

        // ── Hostel Owner Routes ───────────────────────────────
        Route::middleware('role:hostel_owner')->prefix('owner/hostels')->group(function () {
            Route::get('/',                             [OwnerHostelController::class, 'index']);
            Route::post('/',                            [OwnerHostelController::class, 'store']);
            Route::put('/{id}',                        [OwnerHostelController::class, 'update']);
            Route::delete('/{id}',                     [OwnerHostelController::class, 'destroy']);
            // Images
            Route::post('/{id}/images',                [OwnerHostelController::class, 'uploadImages']);
            Route::delete('/images/{imageId}',         [OwnerHostelController::class, 'deleteImage']);
            // Rooms
            Route::post('/{id}/rooms',                 [OwnerHostelController::class, 'storeRoom']);
            Route::put('/rooms/{roomId}',              [OwnerHostelController::class, 'updateRoom']);
            Route::delete('/rooms/{roomId}',           [OwnerHostelController::class, 'deleteRoom']);
            Route::post('/rooms/{roomId}/images',      [OwnerHostelController::class, 'uploadRoomImages']);
            // Bookings
            Route::get('/bookings',                    [OwnerHostelController::class, 'myBookings']);
            Route::put('/bookings/{id}/status',        [OwnerHostelController::class, 'updateBookingStatus']);
            // Reviews
            Route::post('/reviews/{reviewId}/reply',   [OwnerHostelController::class, 'replyToReview']);
        });

        // ── Mess Owner Routes ─────────────────────────────────
        Route::middleware('role:mess_owner')->prefix('owner/messes')->group(function () {
            Route::get('/',                            [OwnerMessController::class, 'index']);
            Route::post('/',                           [OwnerMessController::class, 'store']);
            Route::put('/{id}',                       [OwnerMessController::class, 'update']);
            // Images
            Route::post('/{id}/images',               [OwnerMessController::class, 'uploadImages']);
            // Menus
            Route::post('/{id}/menus',                [OwnerMessController::class, 'storeMenu']);
            Route::put('/menus/{menuId}',             [OwnerMessController::class, 'updateMenu']);
            Route::post('/menus/{menuId}/toggle',     [OwnerMessController::class, 'toggleMenuStatus']);
            Route::post('/menus/{menuId}/image',      [OwnerMessController::class, 'uploadMenuImage']);
            // Plans
            Route::post('/{id}/plans',                [OwnerMessController::class, 'storePlan']);
            // Bookings
            Route::get('/bookings',                   [OwnerMessController::class, 'myBookings']);
            // Reviews
            Route::post('/reviews/{reviewId}/reply',  [OwnerMessController::class, 'replyToReview']);
        });

        // ── Admin Routes ──────────────────────────────────────
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard',                  [AdminController::class, 'dashboard']);
            // Users
            Route::get('/users',                      [AdminController::class, 'users']);
            Route::put('/users/{id}/status',          [AdminController::class, 'updateUserStatus']);
            Route::put('/users/{id}/verify-identity', [AdminController::class, 'verifyIdentity']);
            Route::delete('/users/{id}',              [AdminController::class, 'deleteUser']);
            // Hostels
            Route::get('/hostels',                    [AdminController::class, 'hostels']);
            Route::put('/hostels/{id}/status',        [AdminController::class, 'updateHostelStatus']);
            // Messes
            Route::get('/messes',                     [AdminController::class, 'messes']);
            Route::put('/messes/{id}/status',         [AdminController::class, 'updateMessStatus']);
            // Reviews
            Route::get('/reviews',                    [AdminController::class, 'reviews']);
            Route::put('/reviews/{id}/visibility',    [AdminController::class, 'toggleReviewVisibility']);
            // Subscriptions
            Route::get('/subscriptions',              [AdminController::class, 'subscriptions']);
            Route::post('/expire-accounts',           [AdminController::class, 'expireOwnerAccounts']);
            // Bookings
            Route::get('/bookings',                   [AdminController::class, 'bookings']);
        });
    });
});
