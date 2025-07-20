<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Client;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        foreach ($clients as $client) {
            for ($i = 0; $i < 5; $i++) {
                Post::create([
                    'agency_id' => $client->agency_id,
                    'client_id' => $client->id,
                    'content' => fake()->sentence(10),
                    'media' => null,
                    'scheduleDate' => now()->addDays(rand(1, 30)),
                    'platform' => fake()->randomElement(['facebook', 'twitter', 'instagram']),
                    'postType' => fake()->randomElement(['image', 'video', 'text']),
                    'status' => fake()->randomElement(['draft', 'scheduled', 'published', 'approved', 'rejected']),
                    'feedback' => null,
                ]);
            }
        }
    }
} 