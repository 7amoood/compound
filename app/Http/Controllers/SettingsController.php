<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * تحديث البيانات الشخصية
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'name'  => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $user->id,
        ];

        // Resident-specific fields
        if ($user->role === 'resident') {
            $rules['block_no'] = 'sometimes|nullable|string|max:10';
            $rules['floor']    = 'sometimes|nullable|string|max:10';
            $rules['apt_no']   = 'sometimes|nullable|string|max:10';
        }

        $request->validate($rules);

        $updateData = $request->only(['name', 'phone']);

        if ($user->role === 'resident') {
            $updateData = array_merge($updateData, $request->only(['block_no', 'floor', 'apt_no']));
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث البيانات بنجاح',
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * اختيار صورة من المعرض
     * Set a predefined avatar
     */
    public function setAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|string',
        ]);

        $user = $request->user();

        // Delete old photo if exists and it's NOT a predefined avatar
        if ($user->photo && ! str_starts_with($user->photo, 'avatars/') && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update(['photo' => $request->avatar]);

        return response()->json([
            'success'   => true,
            'message'   => 'تم تحديث الصورة من المعرض',
            'photo_url' => asset($request->avatar),
        ]);
    }

    /**
     * تغيير كلمة المرور
     * Change password with old password confirmation
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة',
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }

    /**
     * حذف الصورة الشخصية
     * Delete profile photo
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update(['photo' => null]);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصورة بنجاح',
        ]);
    }
}
