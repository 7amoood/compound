<?php
namespace App\Http\Controllers;

use App\Models\HelpComment;
use App\Models\HelpRequest;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HelpController extends Controller
{
    /**
     * Get open help requests (excluding user's own, same compound only)
     */
    public function available(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = HelpRequest::open()
            ->where('requester_id', '!=', $user->id)
            ->whereHas('requester', function ($q) use ($user) {
                $q->where('compound_id', $user->compound_id);
            })
            ->with(['requester:id,name,photo,block_no,floor,apt_no', 'comments' => function ($q) {
                $q->latest()->limit(1);
            }, 'comments.user:id,name,photo'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success'  => true,
            'requests' => $requests,
        ]);
    }

    /**
     * Get my help requests
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user   = $request->user();
        $status = $request->get('status', 'all');

        $query = HelpRequest::where('requester_id', $user->id)
            ->with(['helper:id,name,photo,phone,block_no,floor,apt_no', 'comments' => function ($q) {
                $q->latest()->limit(1);
            }, 'comments.user:id,name,photo']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(10);

        return response()->json([
            'success'  => true,
            'requests' => $requests,
        ]);
    }

    /**
     * Get requests I helped with
     */
    public function myHelps(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = HelpRequest::where('helper_id', $user->id)
            ->with(['requester:id,name,photo,phone,block_no,floor,apt_no', 'comments' => function ($q) {
                $q->latest()->limit(1);
            }, 'comments.user:id,name,photo'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success'  => true,
            'requests' => $requests,
        ]);
    }

    /**
     * Create new help request
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        $helpRequest = HelpRequest::create([
            'requester_id' => $user->id,
            'description'  => $request->description,
            'status'       => HelpRequest::STATUS_OPEN,
        ]);

        // Send FCM topic notification to residents in the same compound
        // Send FCM topic notification to residents in the same compound
        $this->sendTopicNotification($user->compound_id, $user->id, $user->name, $request->description, $helpRequest->id);

        $helpRequest->load('requester:id,name,photo');

        return response()->json([
            'success' => true,
            'message' => 'تم نشر طلب المساعدة',
            'request' => $helpRequest,
        ], 201);
    }

    /**
     * Pick up a help request (offer to help)
     */
    public function pick(HelpRequest $helpRequest): JsonResponse
    {
        $user = request()->user();

        // Can't pick your own request
        if ($helpRequest->requester_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك التقاط طلبك الخاص',
            ], 400);
        }

        // Must be open
        if ($helpRequest->status !== HelpRequest::STATUS_OPEN) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب لم يعد متاحاً',
            ], 400);
        }

        $helpRequest->update([
            'status'    => HelpRequest::STATUS_PICKED,
            'helper_id' => $user->id,
            'picked_at' => now(),
        ]);

        // Notify the requester that someone picked up their request
        Notification::send(
            $helpRequest->requester_id,
            Notification::TYPE_HELP_PICKED,
            '🙋 جارك قدّم لك المساعدة!',
            "{$user->name} تطوّع لمساعدتك",
            ['help_request_id' => $helpRequest->id, 'helper_name' => $user->name]
        );

        $helpRequest->load(['requester:id,name,photo,phone', 'helper:id,name,photo,phone']);

        return response()->json([
            'success' => true,
            'message' => 'تم التقاط الطلب بنجاح',
            'request' => $helpRequest,
        ]);
    }

    /**
     * Cancel help request (only if still open)
     */
    public function cancel(HelpRequest $helpRequest): JsonResponse
    {
        $user = request()->user();

        // Must be owner
        if ($helpRequest->requester_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 403);
        }

        // Must be open
        if ($helpRequest->status !== HelpRequest::STATUS_OPEN) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء طلب تم التقاطه',
            ], 400);
        }

        $helpRequest->update([
            'status' => HelpRequest::STATUS_CANCELLED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الطلب',
        ]);
    }

    /**
     * Get single help request with all comments
     */
    public function show(HelpRequest $helpRequest): JsonResponse
    {
        $user = request()->user();

        // Only requester, helper, or admin can view full details
        $isOwner  = $helpRequest->requester_id === $user->id;
        $isHelper = $helpRequest->helper_id === $user->id;
        $isAdmin  = $user->role === 'admin';
        $isOpen   = $helpRequest->status === HelpRequest::STATUS_OPEN;

        if (! $isOwner && ! $isHelper && ! $isAdmin && ! $isOpen) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 403);
        }

        $helpRequest->load([
            'requester:id,name,photo,phone,block_no,floor,apt_no',
            'helper:id,name,photo,phone,block_no,floor,apt_no',
        ]);

        // Get paginated comments (Simplified user data)
        $comments = $helpRequest->comments()
            ->select('id', 'help_request_id', 'user_id', 'comment', 'created_at')
            ->cursorPaginate(5)
            ->withPath(url("/api/help/{$helpRequest->id}/comments"));

        return response()->json([
            'success'  => true,
            'request'  => $helpRequest,
            'comments' => $comments,
        ]);
    }

    /**
     * Get paginated comments for a request
     */
    public function comments(HelpRequest $helpRequest): JsonResponse
    {
        $user      = request()->user();
        $isRelated = $helpRequest->requester_id === $user->id || $helpRequest->helper_id === $user->id || $user->role === 'admin';

        if (! $isRelated && $helpRequest->status !== HelpRequest::STATUS_OPEN) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $comments = $helpRequest->comments()
            ->select('id', 'help_request_id', 'user_id', 'comment', 'created_at')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate(5);

        return response()->json([
            'success'  => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Add comment to help request
     */
    public function addComment(Request $request, HelpRequest $helpRequest): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $user = $request->user();

        // Only requester or helper can comment
        $isOwner  = $helpRequest->requester_id === $user->id;
        $isHelper = $helpRequest->helper_id === $user->id;

        if (! $isOwner && ! $isHelper) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك التعليق على هذا الطلب',
            ], 403);
        }

        // Check max 50 comments
        if ($helpRequest->comments()->count() >= 50) {
            return response()->json([
                'success' => false,
                'message' => 'تم الوصول للحد الأقصى من التعليقات',
            ], 400);
        }

        $comment = HelpComment::create([
            'help_request_id' => $helpRequest->id,
            'user_id'         => $user->id,
            'comment'         => $request->comment,
        ]);

        // Notify the other party about the new comment
        $recipientId = $isOwner ? $helpRequest->helper_id : $helpRequest->requester_id;
        if ($recipientId) {
            Notification::send(
                $recipientId,
                Notification::TYPE_HELP_COMMENT,
                '💬 تعليق جديد',
                "{$user->name}: " . mb_substr($request->comment, 0, 50),
                ['help_request_id' => $helpRequest->id]
            );
        }

        // $comment->load('user:id,name,photo'); // No longer needed for performance

        return response()->json([
            'success' => true,
            'comment' => $comment,
        ], 201);
    }

    /**
     * Admin: Get all help requests
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $status = $request->get('status', 'all');

        $query = HelpRequest::with(['requester:id,name,phone', 'helper:id,name,phone']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(10);

        return response()->json([
            'success'  => true,
            'requests' => $requests,
        ]);
    }

    /**
     * Send FCM topic notification to compound residents
     */
    /**
     * Send FCM topic notification to compound residents
     */
    protected function sendTopicNotification(?int $compoundId, int $senderId, string $requesterName, string $description, int $requestId = null): void
    {
        if (! $compoundId) {
            return;
        }

        $projectId = env('FIREBASE_PROJECT_ID', 'garden-city-compound');
        $authFile  = env('FIREBASE_CREDENTIALS', storage_path('app/firebase-auth.json'));

        if (! file_exists($authFile)) {
            Log::warning('FCM: Missing service account file');
            return;
        }

        try {
            $client = new \Google_Client();
            $client->setAuthConfig($authFile);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            // Topic specific to compound
            $topic = "compound_{$compoundId}_help";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'topic'        => $topic,
                    // 'notification' block removed to prevent auto-display
                    'data'         => [
                        'title' => '🆘 طلب مساعدة جديد',
                        'body'  => "{$requesterName}: " . mb_substr($description, 0, 100),
                        'sender_id'    => (string) $senderId,
                        'request_id'   => (string) $requestId,
                        'type'         => 'new_help_request',
                        'click_action' => $requestId ? "/help?request_id={$requestId}" : '/help',
                    ],
                    'android' => [
                        'priority' => 'high',
                    ],
                    'apns'    => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
                    ],
                    'webpush' => [
                        'notification' => [
                            'icon'  => '/icons/icon-192x192.png',
                            'badge' => '/icons/badge-72x72.png',
                            'dir'   => 'rtl',
                        ],
                        'fcm_options'  => [
                            'link' => '/help',
                        ],
                    ],
                ],
            ]);

            if (! $response->successful()) {
                Log::error('FCM Topic Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('FCM Topic Exception: ' . $e->getMessage());
        }
    }
}
