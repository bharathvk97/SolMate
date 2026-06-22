<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\Owner\{HostelOwnerController, MessOwnerController, SubscriptionController};
use App\Http\Controllers\Admin\AdminWebController;

// ── Auth ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login',   [LoginController::class, 'login']);
    Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
    Route::post('/register',[LoginController::class, 'register']);
    Route::get('/verify-otp',  [LoginController::class, 'showVerifyOtp'])->name('verify.otp.page');
    Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('verify.otp');
    Route::post('/resend-otp', [LoginController::class, 'resendOtp'])->name('resend.otp');
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.forgot');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ── Public ────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/hostels', [HomeController::class, 'hostels'])->name('hostels.index');
Route::get('/hostels/{slug}', [HomeController::class, 'hostelDetail'])->name('hostels.show');
Route::get('/messes', [HomeController::class, 'messes'])->name('messes.index');
Route::get('/messes/{slug}', [HomeController::class, 'messDetail'])->name('messes.show');

// ── Authenticated User ─────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile',    [HomeController::class, 'profile'])->name('profile');
    Route::get('/bookings',   [HomeController::class, 'bookings'])->name('user.bookings');
    Route::get('/favourites', [HomeController::class, 'favourites'])->name('user.favourites');
});

// ── Hostel Owner ───────────────────────────────────────────────
Route::middleware(['auth', 'role:hostel_owner'])->prefix('owner/hostel')->name('owner.hostel.')->group(function () {
    Route::get('/',                          [HostelOwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/listings',                  [HostelOwnerController::class, 'dashboard'])->name('listings');
    Route::get('/create',                    [HostelOwnerController::class, 'createForm'])->name('create');
    Route::post('/store',                    [HostelOwnerController::class, 'store'])->name('store');
    Route::get('/{id}/edit',                 [HostelOwnerController::class, 'editForm'])->name('edit');
    Route::post('/{id}/update',              [HostelOwnerController::class, 'update'])->name('update');
    Route::get('/{id}/rooms',                [HostelOwnerController::class, 'roomsPage'])->name('rooms');
    Route::get('/bookings',                  [HostelOwnerController::class, 'bookingsPage'])->name('bookings');
    Route::put('/bookings/{id}/status',      [HostelOwnerController::class, 'updateBookingStatus'])->name('bookings.status');
    Route::get('/reviews',                   fn() => view('owner.hostel.reviews'))->name('reviews');
});

// ── Mess Owner ─────────────────────────────────────────────────
Route::middleware(['auth', 'role:mess_owner'])->prefix('owner/mess')->name('owner.mess.')->group(function () {
    Route::get('/',                          [MessOwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/listings',                  [MessOwnerController::class, 'dashboard'])->name('listings');
    Route::get('/create',                    [MessOwnerController::class, 'createForm'])->name('create');
    Route::post('/store',                    [MessOwnerController::class, 'store'])->name('store');
    Route::get('/{id}/edit',                 [MessOwnerController::class, 'editForm'])->name('edit');
    Route::post('/{id}/update',              [MessOwnerController::class, 'update'])->name('update');
    Route::get('/menus',                     fn() => view('owner.mess.menus'))->name('menus');
    Route::get('/bookings',                  [MessOwnerController::class, 'bookingsPage'])->name('bookings');
    Route::post('/menus/{id}/toggle',        [MessOwnerController::class, 'toggleMenu'])->name('menus.toggle');
    Route::get('/reviews',                   fn() => view('owner.mess.reviews'))->name('reviews');
});

// ── Shared Owner: Subscription ─────────────────────────────────
Route::middleware(['auth', 'role:hostel_owner,mess_owner'])->group(function () {
    Route::get('/owner/subscription', [SubscriptionController::class, 'index'])->name('owner.subscription');
});

// ── Admin ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',                    [AdminWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/users',                        [AdminWebController::class, 'users'])->name('users');
    Route::put('/users/{id}/status',            [AdminWebController::class, 'updateUserStatus'])->name('users.status');
    Route::get('/identity',                     [AdminWebController::class, 'identityVerification'])->name('identity');
    Route::put('/identity/{id}/verify',         [AdminWebController::class, 'verifyIdentity'])->name('identity.verify');
    Route::get('/hostels',                      [AdminWebController::class, 'hostels'])->name('hostels');
    Route::put('/hostels/{id}/status',          [AdminWebController::class, 'updateHostelStatus'])->name('hostels.status');
    Route::get('/messes',                       [AdminWebController::class, 'messes'])->name('messes');
    Route::put('/messes/{id}/status',           [AdminWebController::class, 'updateMessStatus'])->name('messes.status');
    Route::get('/subscriptions',                [AdminWebController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/expire-accounts',             [AdminWebController::class, 'expireAccounts'])->name('expire-accounts');
    Route::get('/reviews',                      [AdminWebController::class, 'reviews'])->name('reviews');
    Route::put('/reviews/{id}/toggle',          [AdminWebController::class, 'toggleReview'])->name('reviews.toggle');
    Route::get('/bookings',                     [AdminWebController::class, 'bookings'])->name('bookings');
    Route::get('/settings',                     fn() => view('admin.settings'))->name('settings');
});
