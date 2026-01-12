<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * قائمة المستخدمين
     * List users with optional role filter (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role') && in_array($request->role, ['resident', 'provider', 'admin'])) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['serviceType:id,name', 'compound:id,name,location_url'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'users'   => $users,
        ]);
    }

    /**
     * تفاصيل المستخدم
     * Get user details
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['serviceType:id,name', 'compound:id,name,location_url']);

        return response()->json([
            'success' => true,
            'user'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'phone'        => $user->phone,
                'role'         => $user->role,
                'compound_id'  => $user->compound_id,
                'compound'     => $user->compound,
                'block_no'     => $user->block_no,
                'floor'        => $user->floor,
                'apt_no'       => $user->apt_no,
                'address'      => $user->full_address,
                'service_type' => $user->serviceType?->name,
                'photo'        => $user->photo,
                'is_active'    => $user->is_active,
                'created_at'   => $user->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * تفعيل/إلغاء تفعيل المستخدم
     * Toggle user active status
     */
    public function toggleActive(User $user): JsonResponse
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        $status = $user->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return response()->json([
            'success' => true,
            'message' => "{$status} المستخدم بنجاح",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * تحديث بيانات المستخدم
     * Update user data (Admin only)
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'phone'       => 'sometimes|string|max:20',
            'compound_id' => 'nullable|exists:compounds,id',
            'block_no'    => 'nullable|string|max:10',
            'floor'       => 'nullable|string|max:10',
            'apt_no'      => 'nullable|string|max:10',
            'role'        => 'sometimes|in:resident,provider,admin',
            'market_id'   => 'nullable|exists:markets,id',
        ]);

        $user->fill($validated);
        $user->save();

        // Load the compound relationship for the response
        $user->load('compound:id,name,location_url');

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات المستخدم بنجاح',
            'user'    => $user,
        ]);
    }

    /**
     * حذف المستخدم
     * Delete user (soft)
     */
    public function destroy(User $user): JsonResponse
    {
        // Don't allow deleting admins
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف حساب المشرف',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح',
        ]);
    }

    /**
     * تغيير كلمة مرور المستخدم (Admin only)
     * Change user password
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = bcrypt($validated['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }
}
