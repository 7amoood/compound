<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * إحصائيات لوحة التحكم
     * Dashboard statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'pending_users' => User::where('is_active', false)->count(),
            'residents' => User::where('role', 'resident')->count(),
            'providers' => User::where('role', 'provider')->count(),
            
            'total_requests' => ServiceRequest::count(),
            'pending_requests' => ServiceRequest::where('status', 'pending')->count(),
            'in_progress_requests' => ServiceRequest::where('status', 'in_progress')->count(),
            'completed_requests' => ServiceRequest::where('status', 'completed')->count(),
            
            'total_categories' => ServiceCategory::count(),
            'active_categories' => ServiceCategory::where('is_active', true)->count(),
        ];

        // Recent requests
        $recentRequests = ServiceRequest::with(['category:id,name,icon', 'resident:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'role', 'is_active', 'created_at']);

        // Requests by category
        $requestsByCategory = ServiceCategory::withCount('requests')
            ->orderBy('requests_count', 'desc')
            ->get(['id', 'name', 'requests_count']);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_requests' => $recentRequests,
            'recent_users' => $recentUsers,
            'requests_by_category' => $requestsByCategory,
        ]);
    }
}
