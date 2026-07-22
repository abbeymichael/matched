<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['phone' => '+233000000000'],
            [
                'phone_verified_at' => now(),
                'status' => 'active',
                'verification_status' => 'approved',
                'is_admin' => true,
                'match_threshold' => 60,
                'consented_at' => now(),
            ]
        );
    }
}
