<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Notifications\Notification;

class NotificationController extends Controller
{
    protected $messaging;

    public function __construct(FirebaseService $firebase)
    {
        $this->messaging = $firebase->getMessaging();
    }

    public function sendNotification($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$user->fcm_token) {
            return response()->json(['message' => 'User has no FCM token registered'], 400);
        }

        $messages = [
            'Welcome to our app!' . $userId,
            'You have a new message' . $userId,
            'Your profile was updated' . $userId,
            'New feature available' . $userId,
            'Thank you for joining us' . $userId,
        ];

        $notificationText = request()->input('message') ?? $messages[array_rand($messages)];

        try {
            // Send Firebase notification
            $message = CloudMessage::fromArray([
                'token' => $user->fcm_token,
                'notification' => [
                    'title' => 'New Notification',
                    'body' => $notificationText
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'user_id' => (string)$userId
                ]
            ]);

            $this->messaging->send($message);

            // Save to database
            $user->notify(new \App\Notifications\SimpleNotification($notificationText));

            return response()->json(['message' => 'Notification sent successfully via Firebase and saved to database']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send notification: ' . $e->getMessage()], 500);
        }
    }

    public function showNotifications($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user->notifications);
    }
}
