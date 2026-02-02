<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $imagePath = public_path('download.png');

        foreach ($users as $user) {
            for ($i = 1; $i <= 10; $i++) {
                $notification = Notification::create([
                    'type' => 'test_notification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "Notification #{$i} for {$user->name}",
                        'message' => "This is test notification number {$i}",
                    ],
                    'read_at' => null,
                ]);

                // Attach image if it exists
                if (file_exists($imagePath)) {
                    $notification->addMedia($imagePath)
                        ->toMediaCollection('image');
                }
            }
        }
    }
}
