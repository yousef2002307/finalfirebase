<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationController extends Controller
{
    public function sendNotification($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $messages = [
            'Welcome to our app!' . $userId,
            'You have a new message' . $userId,
            'Your profile was updated' . $userId,
            'New feature available' . $userId,
            'Thank you for joining us' . $userId,
        ];

        $user->notify(new \App\Notifications\SimpleNotification($messages[array_rand($messages)]));

        return response()->json(['message' => 'Notification sent']);
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
