<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProfileFieldOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PhysicalAttributeOptionsSeeder::class,
            RelationshipOptionsSeeder::class,
            CareerEducationOptionsSeeder::class,
            LifestyleOptionsSeeder::class,
            PersonalityValuesOptionsSeeder::class,
            InterestsOptionsSeeder::class,
            CommunicationMatchingOptionsSeeder::class,
        ]);
    }
}
