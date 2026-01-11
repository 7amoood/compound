<?php
namespace App\Http\Controllers;

use App\Models\Compound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompoundController extends Controller
{
    /**
     * قائمة الكمبوندات
     * List all compounds
     */
    public function index(): JsonResponse
    {
        $compounds = Compound::withCount('residents')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success'   => true,
            'compounds' => $compounds,
        ]);
    }

    /**
     * الكمبوندات النشطة فقط
     * List active compounds only
     */
    public function active(): JsonResponse
    {
        $compounds = Compound::active()
            ->orderBy('name')
            ->get(['id', 'name', 'location_url']);

        return response()->json([
            'success'   => true,
            'compounds' => $compounds,
        ]);
    }

    /**
     * إضافة كمبوند جديد
     * Create new compound (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'name_en'      => 'nullable|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'address'      => 'nullable|string|max:500',
        ]);

        $compound = Compound::create([
            'name'         => $validated['name'],
            'name_en'      => $validated['name_en'] ?? null,
            'location_url' => $validated['location_url'] ?? null,
            'address'      => $validated['address'] ?? null,
            'is_active'    => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم إضافة الكمبوند بنجاح',
            'compound' => $compound,
        ], 201);
    }

    /**
     * تحديث الكمبوند
     * Update compound
     */
    public function update(Request $request, Compound $compound): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'name_en'      => 'nullable|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'address'      => 'nullable|string|max:500',
            'is_active'    => 'sometimes|boolean',
        ]);

        $compound->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'تم تحديث الكمبوند بنجاح',
            'compound' => $compound,
        ]);
    }

    /**
     * تبديل حالة التفعيل
     * Toggle active status
     */
    public function toggleActive(Compound $compound): JsonResponse
    {
        $compound->is_active = ! $compound->is_active;
        $compound->save();

        $status = $compound->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return response()->json([
            'success' => true,
            'message' => "{$status} الكمبوند بنجاح",
            'is_active' => $compound->is_active,
        ]);
    }

    /**
     * حذف الكمبوند
     * Delete compound
     */
    public function destroy(Compound $compound): JsonResponse
    {
        // Check if there are residents
        if ($compound->residents()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الكمبوند لوجود سكان مسجلين',
            ], 400);
        }

        $compound->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الكمبوند بنجاح',
        ]);
    }
}
