<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    /**
     * Notification types
     */
    const TYPE_NEW_REQUEST       = 'new_request';
    const TYPE_NEW_PROPOSAL      = 'new_proposal';
    const TYPE_PROPOSAL_ACCEPTED = 'proposal_accepted';
    const TYPE_WORK_STARTED      = 'work_started';
    const TYPE_WORK_COMPLETED    = 'work_completed';
    const TYPE_NEW_REVIEW        = 'new_review';

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public static function send(int $userId, string $type, string $title, string $body, array $data = []): self
    {
        $notification = self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        // Send FCM Push
        self::sendPush($notification);

        return $notification;
    }

    protected static function sendPush(self $notification): void
    {
        $user = $notification->user;
        if (! $user || ! $user->fcm_token) {
            return;
        }

        $projectId = env('FIREBASE_PROJECT_ID', 'garden-city-compound');
        $authFile  = env('FIREBASE_CREDENTIALS', storage_path('app/firebase-auth.json'));

        if (! file_exists($authFile)) {
            Log::warning('FCM v1: Missing service account file at ' . $authFile . '. Please ensure the file exists or set FIREBASE_CREDENTIALS in .env');
            return;
        }

        try {
            // Get Google Access Token
            $client = new \Google_Client();
            $client->setAuthConfig($authFile);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            Log::info("FCM v1: Sending to user {$user->id} (token: " . substr($user->fcm_token, 0, 10) . "...)");
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token'        => $user->fcm_token,
                    'notification' => [
                        'title' => $notification->title,
                        'body'  => $notification->body,
                    ],
                    'data'         => [
                        'type'         => $notification->type,
                        'request_id'   => (string) ($notification->data['request_id'] ?? ''),
                        'click_action' => isset($notification->data['request_id']) ? "/dashboard?request_id=" . $notification->data['request_id'] : '/dashboard',
                    ],
                    'webpush'      => [
                        'notification' => [
                            'icon'  => '/icons/icon-192x192.png',
                            'badge' => '/icons/badge-72x72.png',
                            'dir'   => 'rtl',
                        ],
                        'fcm_options'  => [
                            'link' => isset($notification->data['request_id']) ? "/dashboard?request_id=" . $notification->data['request_id'] : '/dashboard',
                        ],
                    ],
                ],
            ]);

            if (! $response->successful()) {
                Log::error('FCM v1 Error: ' . $response->body());
            } else {
                Log::info('FCM v1: Sent successfully');
            }
        } catch (\Exception $e) {
            Log::error('FCM v1 Exception: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to multiple users
     */
    public static function sendToMany(array $userIds, string $type, string $title, string $body, array $data = []): void
    {
        foreach ($userIds as $userId) {
            self::send($userId, $type, $title, $body, $data);
        }
    }
}
