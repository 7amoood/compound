<?php

use App\Http\Controllers\AuthController;
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

    Route::get('/dashboard', fn() => match (auth()->user()->role) {
        'admin'    => Inertia::render('Admin/Dashboard'),
        'provider' => Inertia::render('Provider/Dashboard'),
        default    => Inertia::render('Resident/Dashboard'),
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

    Route::get('/help', function () {
        return Inertia::render('Resident/Help');
    })->name('help');
});
