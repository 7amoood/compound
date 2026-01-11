<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    /**
     * قائمة العروض لطلب معين
     * List proposals for a request
     */
    public function index(ServiceRequest $serviceRequest): JsonResponse
    {
        $proposals = $serviceRequest->proposals()
            ->with('provider:id,name,phone,photo')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'   => true,
            'proposals' => $proposals,
        ]);
    }

    /**
     * تقديم عرض سعر
     * Submit price proposal (Provider only)
     */
    public function store(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $user = $request->user();

        // Verify provider's service type matches request
        if ($user->service_type_id !== $serviceRequest->service_category_id) {
            return response()->json([
                'success' => false,
                'message' => 'نوع الخدمة لا يتطابق مع تخصصك',
            ], 403);
        }

        // Check request is still pending
        if ($serviceRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تقديم عرض لهذا الطلب',
            ], 400);
        }

        // Check if provider already submitted
        if ($serviceRequest->proposals()->where('provider_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قدمت عرضاً بالفعل لهذا الطلب',
            ], 400);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $proposal = Proposal::create([
            'request_id'  => $serviceRequest->id,
            'provider_id' => $user->id,
            'price'       => $request->price,
            'notes'       => $request->notes,
            'is_accepted' => false,
        ]);

        $proposal->load('provider:id,name,photo');

        // Notify resident about new proposal
        Notification::send(
            $serviceRequest->resident_id,
            Notification::TYPE_NEW_PROPOSAL,
            'عرض سعر جديد',
            "قدم {$user->name} عرض سعر {$request->price} ج.م",
            ['request_id' => $serviceRequest->id, 'proposal_id' => $proposal->id, 'provider_name' => $user->name]
        );

        return response()->json([
            'success'  => true,
            'message'  => 'تم تقديم العرض بنجاح',
            'proposal' => $proposal,
        ], 201);
    }

    /**
     * تعديل عرض سعر
     * Update price proposal (Provider only)
     */
    public function update(Request $request, Proposal $proposal): JsonResponse
    {
        $user = $request->user();

        // Verify provider owns the proposal
        if ($proposal->provider_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا العرض',
            ], 403);
        }

        // Check request is still pending
        if ($proposal->request->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل العرض لهذا الطلب',
            ], 400);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $proposal->update([
            'price' => $request->price,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم تعديل العرض بنجاح',
            'proposal' => $proposal,
        ]);
    }

    /**
     * قبول العرض
     * Accept a proposal (Resident only)
     */
    public function accept(Proposal $proposal): JsonResponse
    {
        $user           = request()->user();
        $serviceRequest = $proposal->request;

        // Verify resident owns the request
        if ($serviceRequest->resident_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بقبول هذا العرض',
            ], 403);
        }

        // Check request is still pending
        if ($serviceRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن قبول العرض حالياً',
            ], 400);
        }

        // Accept this proposal
        $proposal->is_accepted = true;
        $proposal->save();

        // Update request status
        $serviceRequest->status = 'accepted_offer';
        $serviceRequest->save();

        // Notify provider that their proposal was accepted
        $serviceRequest->load('category:id,name');
        Notification::send(
            $proposal->provider_id,
            Notification::TYPE_PROPOSAL_ACCEPTED,
            'تم قبول عرضك',
            "تم قبول عرضك لخدمة {$serviceRequest->category->name}. تواصل مع العميل الآن.",
            ['request_id' => $serviceRequest->id, 'proposal_id' => $proposal->id]
        );

        return response()->json([
            'success'  => true,
            'message'  => 'تم قبول العرض بنجاح. سيتواصل معك مزود الخدمة قريباً.',
            'provider' => [
                'name'  => $proposal->provider->name,
                'phone' => $proposal->provider->phone,
            ],
        ]);
    }

    /**
     * طلباتي (للمزود)
     * My Jobs - Get provider's accepted jobs (Requests)
     */
    public function myJobs(): JsonResponse
    {
        $user = request()->user();

        // Get requests where I have an accepted proposal
        // This ensures the returned structure matches 'Request' objects,
        // allowing consistent frontend property access (e.g. job.resident.name).
        $requests = ServiceRequest::whereHas('acceptedProposal', function ($q) use ($user) {
            $q->where('provider_id', $user->id);
        })
            ->with(['category:id,name,icon', 'resident:id,name,phone,block_no,floor,apt_no', 'acceptedProposal'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'jobs'    => $requests,
        ]);
    }

    /**
     * بدء العمل على الطلب
     * Start working on request (Provider)
     */
    public function startWork(Proposal $proposal): JsonResponse
    {
        $user = request()->user();

        if ($proposal->provider_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ], 403);
        }

        $serviceRequest = $proposal->request;

        if ($serviceRequest->status !== 'accepted_offer') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن بدء العمل على هذا الطلب',
            ], 400);
        }

        $serviceRequest->status = 'in_progress';
        $serviceRequest->save();

        // Notify resident that work has started
        $serviceRequest->load('category:id,name');
        Notification::send(
            $serviceRequest->resident_id,
            Notification::TYPE_WORK_STARTED,
            'بدأ العمل',
            "بدأ {$user->name} العمل على خدمة {$serviceRequest->category->name}",
            ['request_id' => $serviceRequest->id, 'provider_name' => $user->name]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم بدء العمل على الطلب',
        ]);
    }
}
