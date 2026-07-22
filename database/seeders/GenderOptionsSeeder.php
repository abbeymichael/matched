<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class GenderOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['value' => 'male', 'label' => 'Male'],
            ['value' => 'female', 'label' => 'Female'],
            ['value' => 'non_binary', 'label' => 'Non-binary'],
            ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
        ];

        foreach ($options as $index => $option) {
            ProfileFieldOption::firstOrCreate(
                ['field_key' => 'gender', 'value' => $option['value']],
                ['label' => $option['label'], 'sort_order' => $index, 'is_active' => true]
            );
        }
    }
}
