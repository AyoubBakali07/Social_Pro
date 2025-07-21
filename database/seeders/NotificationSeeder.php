<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                Notification::create([
                    'user_id' => $user->id,
                    'message' => fake()->sentence(8),
                    'type' => fake()->randomElement(['info', 'warning', 'success', 'error']),
                    'is_read' => fake()->boolean(30),
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
} 