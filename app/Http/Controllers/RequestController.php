<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * قائمة الطلبات
     * List requests based on user role
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = ServiceRequest::with(['category:id,name,icon', 'resident:id,name,phone,block_no,floor,apt_no,compound_id', 'resident.compound:id,name,location_url', 'acceptedProposal', 'review']);

        // Role-based filtering
        if ($user->isResident()) {
            // Residents see only their own requests
            $query->where('resident_id', $user->id);
        } elseif ($user->isProvider()) {
            // Providers see pending requests for their service type
            $query->where('service_category_id', $user->service_type_id)
                ->where('status', 'pending')
                ->with(['proposals' => function ($q) use ($user) {
                    $q->where('provider_id', $user->id);
                }]);
        }
        // Admins see all

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->withCount('proposals')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success'  => true,
            'requests' => $requests,
        ]);
    }

    /**
     * إنشاء طلب جديد
     * Create new service request (Resident only)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'notes'               => 'nullable|string|max:1000',
            'delivery_method'     => 'nullable|string|max:100',
            'location'            => 'nullable|string|max:100',
        ]);

        $serviceRequest = ServiceRequest::create([
            'resident_id'         => $request->user()->id,
            'service_category_id' => $request->service_category_id,
            'notes'               => $request->notes,
            'delivery_method'     => $request->delivery_method,
            'location'            => $request->location,
            'status'              => 'pending',
        ]);

        $serviceRequest->load('category:id,name,icon');

        // Notify providers of this category about new request
        $providerIds = User::where('role', 'provider')
            ->where('service_type_id', $request->service_category_id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (! empty($providerIds)) {
            Notification::sendToMany(
                $providerIds,
                Notification::TYPE_NEW_REQUEST,
                'طلب خدمة جديد',
                "طلب جديد في انتظارك: {$serviceRequest->category->name}",
                ['request_id' => $serviceRequest->id]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الطلب بنجاح',
            'request' => $serviceRequest,
        ], 201);
    }

    /**
     * تفاصيل الطلب
     * Get request details
     */
    public function show(ServiceRequest $serviceRequest): JsonResponse
    {
        $user = request()->user();

        // Load relationships
        $serviceRequest->load([
            'category:id,name,icon',
            'resident:id,name,phone,block_no,floor,apt_no',
            'proposals.provider:id,name,phone,photo,rating_average',
            'acceptedProposal.provider:id,name,phone,photo,rating_average',
            'review',
        ]);

        // Only show resident details to accepted provider
        $residentDetails = null;
        if ($user->isProvider()) {
            $acceptedProposal = $serviceRequest->acceptedProposal;
            if ($acceptedProposal && $acceptedProposal->provider_id === $user->id) {
                $residentDetails = [
                    'name'    => $serviceRequest->resident->name,
                    'phone'   => $serviceRequest->resident->phone,
                    'address' => $serviceRequest->resident->full_address,
                ];
            }
        } elseif ($user->isResident() && $serviceRequest->resident_id === $user->id) {
            $residentDetails = null; // Resident viewing own request
        } elseif ($user->isAdmin()) {
            $residentDetails = [
                'name'    => $serviceRequest->resident->name,
                'phone'   => $serviceRequest->resident->phone,
                'address' => $serviceRequest->resident->full_address,
            ];
        }

        return response()->json([
            'success'          => true,
            'request'          => $serviceRequest,
            'resident_details' => $residentDetails,
            'status_label'     => $serviceRequest->status_label,
        ]);
    }

    /**
     * تحديث حالة الطلب
     * Update request status
     */
    public function updateStatus(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,accepted_offer,in_progress,completed,cancelled',
        ]);

        $oldStatus = $serviceRequest->status;
        $newStatus = $request->status;

        // Validate status transitions
        $validTransitions = [
            'pending'        => ['accepted_offer', 'cancelled'],
            'accepted_offer' => ['in_progress', 'cancelled'],
            'in_progress'    => ['completed', 'cancelled'],
            'completed'      => [],
            'cancelled'      => [],
        ];

        if (! in_array($newStatus, $validTransitions[$oldStatus])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تغيير الحالة إلى هذه القيمة',
            ], 400);
        }

        $serviceRequest->status = $newStatus;
        $serviceRequest->save();

        return response()->json([
            'success'      => true,
            'message'      => 'تم تحديث حالة الطلب بنجاح',
            'status'       => $newStatus,
            'status_label' => $serviceRequest->status_label,
        ]);
    }

    /**
     * إلغاء الطلب
     * Cancel request
     */
    public function cancel(ServiceRequest $serviceRequest): JsonResponse
    {
        $user = request()->user();

        // Only resident can cancel their own request or admin
        if (! $user->isAdmin() && $serviceRequest->resident_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإلغاء هذا الطلب',
            ], 403);
        }

        if (in_array($serviceRequest->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء هذا الطلب',
            ], 400);
        }

        $serviceRequest->status = 'cancelled';
        $serviceRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الطلب بنجاح',
        ]);
    }

    /**
     * إكمال الطلب (عن طريق مقدم الخدمة)
     * Complete request
     */
    public function complete(ServiceRequest $serviceRequest): JsonResponse
    {
        $user = request()->user();

        // Verify provider owns the accepted proposal
        $acceptedProposal = $serviceRequest->acceptedProposal;

        if (! $acceptedProposal || $acceptedProposal->provider_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإكمال هذا الطلب',
            ], 403);
        }

        if ($serviceRequest->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'الطلب ليس قيد التنفيذ حالياً',
            ], 400);
        }

        $serviceRequest->status = 'completed';
        $serviceRequest->save();

        // Notify resident
        Notification::send(
            $serviceRequest->resident_id,
            Notification::TYPE_WORK_COMPLETED,
            'تم إكمال الطلب',
            "قام {$user->name} بإكمال طلبك. يمكنك الآن تقييم الخدمة.",
            ['request_id' => $serviceRequest->id]
        );

        return response()->json(['success' => true]);
    }
}
