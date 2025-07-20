<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public static $admin;
    public static $agencies = [];
    public static $clients = [];

    public function run(): void
    {
        // 1 Admin
        self::$admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@socialchat.com',
        ]);

        // 5 Agencies
        self::$agencies = User::factory()->count(5)->agency()->create();

        // 10 Clients
        self::$clients = User::factory()->count(10)->client()->create();
    }
} 