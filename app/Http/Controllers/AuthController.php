<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * تسجيل الدخول
     * Login with phone and password
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير مفعل. يرجى انتظار موافقة المشرف.',
            ], 403);
        }

        // Login user into session for web routes
        Auth::login($user);
        $request->session()->regenerate();

        // Create token for API
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role,
                'photo' => $user->photo,
                'address' => $user->full_address,
            ],
            'token' => $token,
            'redirect' => match($user->role) {
                'admin' => '/admin',
                'provider' => '/provider',
                default => '/resident',
            },
        ]);
    }

    /**
     * تسجيل مستخدم جديد
     * Register new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => 'required|in:resident,provider',
            'block_no' => 'nullable|string',
            'floor' => 'nullable|string',
            'apt_no' => 'nullable|string',
            'service_type_id' => 'required_if:role,provider|nullable|exists:service_categories,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'block_no' => $request->block_no,
            'floor' => $request->floor,
            'apt_no' => $request->apt_no,
            'service_type_id' => $request->service_type_id,
            'is_active' => false, // Requires admin activation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الحساب بنجاح. يرجى انتظار تفعيل المشرف.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * تسجيل الخروج
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /**
     * Get current user info
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role,
                'photo' => $user->photo,
                'address' => $user->full_address,
                'block_no' => $user->block_no,
                'floor' => $user->floor,
                'apt_no' => $user->apt_no,
                'service_type' => $user->serviceType?->name,
            ],
        ]);
    }

    /**
     * Get service categories for registration form
     */
    public function serviceCategories(): JsonResponse
    {
        $categories = ServiceCategory::active()->get(['id', 'name', 'icon']);
        
        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }
}
