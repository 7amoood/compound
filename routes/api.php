<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompoundController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RequestItemController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAuthnController;
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

// Note: Most internal SPA routes have been moved to routes/web.php
// to leverage the web session/cookie authentication (Inertia Monolith pattern).

// Public API routes (Example)
Route::get('/public/services', [ServiceCategoryController::class, 'active']);

// WebAuthn (Biometric) Routes
Route::prefix('webauthn')->group(function () {
    Route::post('/register/options', [WebAuthnController::class, 'registerOptions']);
    Route::post('/register/store', [WebAuthnController::class, 'registerStore']);
    Route::post('/auth/options', [WebAuthnController::class, 'authOptions']);
    Route::post('/auth/verify', [WebAuthnController::class, 'authVerify']);
    Route::post('/check', [WebAuthnController::class, 'checkBiometric']);
});

Route::middleware('auth')->group(function () {
                                                                                    // Shared & Resident
    Route::get('/service-categories', [ServiceCategoryController::class, 'index']); // Public/Auth Read
    Route::get('/active-categories', [ServiceCategoryController::class, 'active']); // Helper

    // Requests
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{serviceRequest}', [RequestController::class, 'show']);
    Route::post('/requests/{serviceRequest}/cancel', [RequestController::class, 'cancel']);
    Route::post('/requests/{serviceRequest}/complete', [RequestController::class, 'complete']);
    Route::post('/requests/{serviceRequest}/start-market-order', [RequestController::class, 'startMarketOrder']);

    // Proposals
    Route::get('/requests/{serviceRequest}/proposals', [ProposalController::class, 'index']);
    Route::post('/requests/{serviceRequest}/proposals', [ProposalController::class, 'store']);
    Route::put('/proposals/{proposal}', [ProposalController::class, 'update']);
    Route::post('/proposals/{proposal}/accept', [ProposalController::class, 'accept']);
    Route::post('/proposals/{proposal}/start', [ProposalController::class, 'startWork']);
    Route::get('/provider/my-jobs', [ProposalController::class, 'myJobs']);
    Route::get('/provider/available-requests', [RequestController::class, 'index']); // Map to index acting as available
    Route::get('/provider/reviews', [ReviewController::class, 'providerReviews']);

    // Provider specific (re-mapping from api.php analysis)
    Route::get('/provider/my-jobs', [ProposalController::class, 'myJobs']);
    Route::get('/provider/stats', [ReviewController::class, 'providerStats']);
    Route::get('/provider/reviews', [ReviewController::class, 'providerReviews']);

    Route::get('/provider/available-requests', [RequestController::class, 'index']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/save-token', [NotificationController::class, 'saveToken']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // Settings (API endpoints called by Vue)
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/settings/password', [SettingsController::class, 'changePassword']);
    Route::post('/settings/avatar', [SettingsController::class, 'setAvatar'])->name('settings.avatar');

    // Markets
    Route::get('/markets', [MarketController::class, 'index']);

    // Request Items
    Route::put('/request-items/{item}/toggle', [RequestItemController::class, 'toggle']);

    // Help (مساعدة) - Resident feature
    Route::get('/help/available', [HelpController::class, 'available']);
    Route::get('/help/my-requests', [HelpController::class, 'myRequests']);
    Route::get('/help/my-helps', [HelpController::class, 'myHelps']);
    Route::post('/help', [HelpController::class, 'store']);
    Route::get('/help/{helpRequest}', [HelpController::class, 'show']);
    Route::post('/help/{helpRequest}/pick', [HelpController::class, 'pick']);
    Route::post('/help/{helpRequest}/cancel', [HelpController::class, 'cancel']);
    Route::post('/help/{helpRequest}/comment', [HelpController::class, 'addComment']);
    Route::get('/help/{helpRequest}/comments', [HelpController::class, 'comments']);

    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/stats', [AdminController::class, 'stats']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/admin/users/{user}', [UserController::class, 'update']);
        Route::post('/admin/users/{user}/toggle-status', [UserController::class, 'toggleActive']);
        Route::post('/admin/users/{user}/change-password', [UserController::class, 'changePassword']);

        // Services CRUD
        Route::get('/services', [ServiceCategoryController::class, 'index']);
        Route::post('/services', [ServiceCategoryController::class, 'store']);
        Route::put('/admin/services/{category}', [ServiceCategoryController::class, 'update']);
        Route::post('/admin/services/{category}/toggle-status', [ServiceCategoryController::class, 'toggleActive']);
        Route::delete('/admin/services/{category}', [ServiceCategoryController::class, 'destroy']);

        // Compounds CRUD
        Route::get('/compounds', [CompoundController::class, 'index']);
        Route::get('/compounds/active', [CompoundController::class, 'active']);
        Route::post('/compounds', [CompoundController::class, 'store']);
        Route::put('/admin/compounds/{compound}', [CompoundController::class, 'update']);
        Route::post('/admin/compounds/{compound}/toggle-status', [CompoundController::class, 'toggleActive']);
        Route::delete('/admin/compounds/{compound}', [CompoundController::class, 'destroy']);

        // Markets CRUD
        Route::get('/admin/markets', [MarketController::class, 'all']);
        Route::post('/markets', [MarketController::class, 'store']);
        Route::put('/admin/markets/{market}', [MarketController::class, 'update']);
        Route::delete('/admin/markets/{market}', [MarketController::class, 'destroy']);
    });
});
