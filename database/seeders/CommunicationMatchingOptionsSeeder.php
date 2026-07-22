<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class CommunicationMatchingOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'communication_style' => [
                ['value' => 'text_first', 'label' => 'Text first, then call'],
                ['value' => 'calls_preferred', 'label' => 'Calls preferred'],
                ['value' => 'in_person', 'label' => 'In-person preferred'],
                ['value' => 'daily_checkins', 'label' => 'Daily check-ins'],
                ['value' => 'low_key', 'label' => 'Low-key / as needed'],
            ],
            'dealbreakers' => [
                ['value' => 'smoking', 'label' => 'Smoking'],
                ['value' => 'drinking', 'label' => 'Regular drinking'],
                ['value' => 'no_religion', 'label' => 'No shared religion'],
                ['value' => 'different_goals', 'label' => 'Different relationship goals'],
                ['value' => 'long_distance', 'label' => 'Long distance'],
                ['value' => 'no_kids_desire', 'label' => 'Does not want children'],
            ],
            'must_haves' => [
                ['value' => 'faith', 'label' => 'Shared faith'],
                ['value' => 'family_oriented', 'label' => 'Family-oriented'],
                ['value' => 'ambition', 'label' => 'Ambition'],
                ['value' => 'sense_of_humor', 'label' => 'Sense of humor'],
                ['value' => 'honesty', 'label' => 'Honesty'],
                ['value' => 'kindness', 'label' => 'Kindness'],
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
