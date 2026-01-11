<?php

use App\Http\Controllers\ServiceCategoryController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
