<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * إضافة تقييم
     * Submit review for completed request
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'request_id' => 'required|exists:requests,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:500',
        ]);

        $user           = $request->user();
        $serviceRequest = ServiceRequest::findOrFail($request->request_id);

        // Verify resident owns the request
        if ($serviceRequest->resident_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتقييم هذا الطلب',
            ], 403);
        }

        // Check request is completed
        if ($serviceRequest->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تقييم طلب غير مكتمل',
            ], 400);
        }

        // Check if already reviewed
        if ($serviceRequest->review()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'تم تقييم هذا الطلب بالفعل',
            ], 400);
        }

        $review = Review::create([
            'request_id' => $serviceRequest->id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        // Update provider's denormalized rating
        $acceptedProposal = Proposal::where('request_id', $serviceRequest->id)
            ->where('is_accepted', true)
            ->first();

        if ($acceptedProposal && $acceptedProposal->provider) {
            $acceptedProposal->provider->updateRating($request->rating);

            // Notify provider about new review
            $stars = str_repeat('★', $request->rating) . str_repeat('☆', 5 - $request->rating);
            Notification::send(
                $acceptedProposal->provider_id,
                Notification::TYPE_NEW_REVIEW,
                'تقييم جديد',
                "حصلت على تقييم {$stars} من {$user->name}",
                ['request_id' => $serviceRequest->id, 'rating' => $request->rating]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'شكراً لك! تم إرسال التقييم بنجاح',
            'review'  => $review,
        ], 201);
    }

    /**
     * Get provider statistics (rating, reviews count, earnings)
     * Uses denormalized fields for fast access
     */
    public function providerStats(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'provider') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الإحصائيات متاحة فقط لمزودي الخدمة',
            ], 403);
        }

        // Calculate today's earnings from completed jobs
        $today         = now()->startOfDay();
        $todayEarnings = $user->proposals()
            ->whereHas('request', function ($q) use ($today) {
                $q->where('status', 'completed')
                    ->whereDate('updated_at', '>=', $today);
            })
            ->sum('price');

        // Get total completed jobs
        $completedJobsQuery = $user->proposals()
            ->whereHas('request', function ($q) {
                $q->where('status', 'completed');
            });

        $completedJobs      = (clone $completedJobsQuery)->count();
        $todayCompletedJobs = $completedJobsQuery->whereDate('updated_at', '>=', $today)->count();
        return response()->json([
            'success' => true,
            'stats'   => [
                'average_rating'       => $user->rating_average, // Denormalized field
                'reviews_count'        => $user->rating_count,   // Denormalized field
                'total_earnings'       => $user->total_earnings, // Denormalized field
                'today_earnings'       => $todayEarnings,
                'completed_jobs'       => $completedJobs,
                'today_completed_jobs' => $todayCompletedJobs,
            ],
        ]);
    }

    /**
     * Get provider reviews with pagination
     */
    public function providerReviews(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'provider') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الميزة متاحة فقط لمزودي الخدمة',
            ], 403);
        }

        $reviews = Review::whereHas('request.acceptedProposal', function ($query) use ($user) {
            $query->where('provider_id', $user->id);
        })
            ->with(['request.category:id,name', 'request.resident:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }
}
