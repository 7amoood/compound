<?php
namespace App\Http\Controllers;

use App\Models\Market;
use Illuminate\Http\JsonResponse;

class MarketController extends Controller
{
    /**
     * Get all active markets
     */
    public function index(): JsonResponse
    {
        $userId = auth()->id();
        $query  = Market::active();

        if ($userId) {
            // Order by popularity for this resident (request count)
            $query->withCount(['requests' => function ($q) use ($userId) {
                $q->where('resident_id', $userId);
            }])->orderBy('requests_count', 'desc');
        }

        // Secondary sort by name
        $query->orderBy('name');

        $markets = $query->get(['id', 'name', 'description', 'logo', 'phone']);

        return response()->json([
            'success' => true,
            'markets' => $markets,
        ]);
    }
    /**
     * Get all markets (Admin)
     */
    public function all(): JsonResponse
    {
        $markets = Market::withCount('users')->get();
        return response()->json([
            'success' => true,
            'markets' => $markets,
        ]);
    }

    /**
     * Create a new market
     */
    public function store(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string',
            'logo'        => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $market = Market::create($validated);

        return response()->json([
            'success' => true,
            'market'  => $market,
        ]);
    }

    /**
     * Update a market
     */
    public function update(\Illuminate\Http\Request $request, Market $market): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string',
            'logo'        => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $market->update($validated);

        return response()->json([
            'success' => true,
            'market'  => $market,
        ]);
    }

    /**
     * Delete a market
     */
    public function destroy(Market $market): JsonResponse
    {
        $market->delete();
        return response()->json([
            'success' => true,
        ]);
    }
}
