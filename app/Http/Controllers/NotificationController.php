<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * قائمة الإشعارات
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'       => true,
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * عدد الإشعارات غير المقروءة
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    /**
     * تحديد كمقروء
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = request()->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم التحديد كمقروء',
        ]);
    }

    /**
     * تحديد الكل كمقروء
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد جميع الإشعارات كمقروءة',
        ]);
    }

    /**
     * حذف إشعار
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $user = request()->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار',
        ]);
    }
    /**
     * حفظ توكن الإشعارات
     * Save FCM token
     */
    public function saveToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token      = $request->token;
        $activeUser = $request->user();

        // 1. Remove this token from any other user (to prevent duplicates on same device/browser)
        User::where('fcm_token', $token)
            ->where('id', '!=', $activeUser->id)
            ->update(['fcm_token' => null]);

        // 2. Save token for current user
        $activeUser->update([
            'fcm_token' => $token,
        ]);

        // 3. Subscribe residents to their compound's help topic
        if ($activeUser->role === 'resident' && $activeUser->compound_id) {
            $this->subscribeToTopic($token, "compound_{$activeUser->compound_id}_help");
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ توكن الإشعارات بنجاح',
        ]);
    }

    /**
     * Subscribe token to FCM topic using FCM v1 API
     */
    protected function subscribeToTopic(string $token, string $topic): void
    {
        $projectId = env('FIREBASE_PROJECT_ID', 'garden-city-compound');
        $authFile  = env('FIREBASE_CREDENTIALS', storage_path('app/firebase-auth.json'));

        if (! file_exists($authFile)) {
            \Illuminate\Support\Facades\Log::warning("FCM Topic Subscribe: Missing auth file");
            return;
        }

        try {
            $client = new \Google_Client();
            $client->setAuthConfig($authFile);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            // Use the FCM v1 API for topic subscription
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization'     => 'Bearer ' . $accessToken,
                'Content-Type'      => 'application/json',
                'access_token_auth' => 'true',
            ])->post("https://iid.googleapis.com/iid/v1/{$token}/rel/topics/{$topic}");

            if ($response->successful()) {
                \Illuminate\Support\Facades\Log::info("FCM Topic Subscribe: Successfully subscribed to {$topic}");
            } else {
                // Try alternative endpoint
                $altResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/subscriptions:batchCreate", [
                    'topic' => "/topics/{$topic}",
                    'tokens' => [$token],
                ]);

                if ($altResponse->successful()) {
                    \Illuminate\Support\Facades\Log::info("FCM Topic Subscribe (v1): Successfully subscribed to {$topic}");
                } else {
                    \Illuminate\Support\Facades\Log::error("FCM Topic Subscribe Failed: " . $altResponse->body());
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Topic Subscribe Error: ' . $e->getMessage());
        }
    }
}
