<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class CareerEducationOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'education_level' => [
                ['value' => 'high_school', 'label' => 'High school'],
                ['value' => 'diploma', 'label' => 'Diploma / HND'],
                ['value' => 'bachelors', 'label' => 'Bachelor\'s degree'],
                ['value' => 'masters', 'label' => 'Master\'s degree'],
                ['value' => 'doctorate', 'label' => 'Doctorate'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'industry' => [
                ['value' => 'tech', 'label' => 'Technology'],
                ['value' => 'finance', 'label' => 'Finance / Banking'],
                ['value' => 'healthcare', 'label' => 'Healthcare'],
                ['value' => 'education', 'label' => 'Education'],
                ['value' => 'creative', 'label' => 'Creative / Media'],
                ['value' => 'trading', 'label' => 'Trading / Business'],
                ['value' => 'government', 'label' => 'Government / Public service'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'income_range' => [
                ['value' => 'tier_1', 'label' => 'Tier 1'],
                ['value' => 'tier_2', 'label' => 'Tier 2'],
                ['value' => 'tier_3', 'label' => 'Tier 3'],
                ['value' => 'tier_4', 'label' => 'Tier 4'],
                ['value' => 'tier_5', 'label' => 'Tier 5'],
                ['value' => 'tier_6', 'label' => 'Tier 6'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
            ],
            'work_schedule' => [
                ['value' => 'fixed', 'label' => 'Fixed hours'],
                ['value' => 'flexible', 'label' => 'Flexible'],
                ['value' => 'shift', 'label' => 'Shift work'],
                ['value' => 'remote', 'label' => 'Remote / hybrid'],
                ['value' => 'entrepreneur', 'label' => 'Entrepreneur / self-employed'],
            ],
        ];

        foreach ($options as $fieldKey => $items) {
            foreach ($items as $index => $item) {
                ProfileFieldOption::firstOrCreate(
                    ['field_key' => $fieldKey, 'value' => $item['value']],
                    ['label' => $item['label'], 'sort_order' => $index, 'is_active' => true]
                );
            }
        }
    }
}
