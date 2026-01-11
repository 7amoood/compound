<?php
namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /**
     * قائمة الخدمات
     * List all service categories
     */
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::withCount(['providers', 'requests'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success'    => true,
            'categories' => $categories,
        ]);
    }

    /**
     * الخدمات النشطة فقط
     * List active categories only, ordered by user's usage frequency
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        $categories = ServiceCategory::active()
            ->withCount(['requests' => function ($query) use ($user) {
                $query->where('resident_id', $user->id);
            }])
            ->orderBy('requests_count', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'icon', 'description', 'delivery_methods']);

        return response()->json([
            'success'    => true,
            'categories' => $categories,
        ]);
    }

    /**
     * إضافة خدمة جديدة
     * Create new category (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'name_en'          => 'nullable|string|max:255',
            'icon'             => 'nullable|string|max:50',
            'delivery_methods' => 'nullable|array',
            'description'      => 'nullable|string',
        ]);

        $category = ServiceCategory::create([
            'name'             => $request->name,
            'name_en'          => $request->name_en,
            'icon'             => $request->icon ?? 'build',
            'delivery_methods' => $request->delivery_methods,
            'description'      => $request->description,
            'is_active'        => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم إضافة الخدمة بنجاح',
            'category' => $category,
        ], 201);
    }

    /**
     * تحديث الخدمة
     * Update category
     */
    public function update(Request $request, ServiceCategory $category): JsonResponse
    {
        $request->validate([
            'name'             => 'sometimes|string|max:255',
            'name_en'          => 'nullable|string|max:255',
            'icon'             => 'nullable|string|max:50',
            'delivery_methods' => 'nullable|array',
            'description'      => 'nullable|string',
            'is_active'        => 'sometimes|boolean',
        ]);

        $category->update($request->only(['name', 'name_en', 'icon', 'delivery_methods', 'description', 'is_active']));

        return response()->json([
            'success'  => true,
            'message'  => 'تم تحديث الخدمة بنجاح',
            'category' => $category,
        ]);
    }

    /**
     * تبديل حالة التفعيل
     * Toggle active status
     */
    public function toggleActive(ServiceCategory $category): JsonResponse
    {
        $category->is_active = ! $category->is_active;
        $category->save();

        $status = $category->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return response()->json([
            'success' => true,
            'message' => "{$status} الخدمة بنجاح",
            'is_active' => $category->is_active,
        ]);
    }

    /**
     * حذف الخدمة
     * Delete category
     */
    public function destroy(ServiceCategory $category): JsonResponse
    {
        // Check if there are active requests
        if ($category->requests()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الخدمة لوجود طلبات نشطة',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الخدمة بنجاح',
        ]);
    }
}
