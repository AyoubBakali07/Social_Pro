<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;
use App\Models\Agency;

class ClientSeeder extends Seeder
{
    public static $clients = [];

    public function run(): void
    {
        $clientUsers = User::where('role', 'client')->get();
        $agencies = Agency::all();
        $agencyIndex = 0;
        $clientsPerAgency = 2;
        $agencyCount = $agencies->count();

        foreach ($clientUsers as $i => $user) {
            $agency = $agencies[$agencyIndex];
            $client = Client::create([
                'user_id' => $user->id,
                'agency_id' => $agency->id,
                'company_name' => fake()->company(),
                'status' => 'active',
            ]);
            self::$clients[] = $client;
            if ((($i + 1) % $clientsPerAgency) === 0 && $agencyIndex < $agencyCount - 1) {
                $agencyIndex++;
            }
        }
    }
} 