<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// .well-known routes for deep linking (Android/iOS)
Route::get('/.well-known/assetlinks.json', function () {
    return response()->file(public_path('.well-known/assetlinks.json'), [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/.well-known/apple-app-site-association', function () {
    return response()->file(public_path('.well-known/apple-app-site-association'), [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');

Route::post('/login', [AuthController::class, 'webLogin']);

Route::post('/register', [AuthController::class, 'webRegister']);

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');

    Route::get('/dashboard', function () {
        $user = auth()->user();

        return match ($user->role) {
            'admin'    => Inertia::render('Admin/Dashboard'),
            'provider' => Inertia::render('Provider/Dashboard'),
            default    => Inertia::render('Resident/Dashboard'),
        };
    })->name('dashboard');

    Route::get('/admin', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/resident', function () {
        return Inertia::render('Resident/Dashboard');
    })->name('resident.dashboard');

    Route::get('/provider', function () {
        return Inertia::render('Provider/Dashboard');
    })->name('provider.dashboard');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');

    // Help Page (Resident)
    Route::get('/help', [HelpController::class, 'index'])->name('help');

    // --- Internal API Routes (Session Based) ---
    Route::prefix('api')->group(function () {
        // Shared & Resident
        Route::get('/me', function (Request $request) {return $request->user();});
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
});
