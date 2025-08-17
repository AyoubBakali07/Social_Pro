<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agency;
use App\Models\User;

class AgencySeeder extends Seeder
{
    public static $agencies = [];

    public function run(): void
    {
        $agencyUsers = User::where('role', 'agency')->get();
        foreach ($agencyUsers as $user) {
            $agency = Agency::create([
                'user_id' => $user->id,
                'company_name' => fake()->company(),
                'status' => 'Active',
            ]);
            self::$agencies[] = $agency;
        }
    }
} 