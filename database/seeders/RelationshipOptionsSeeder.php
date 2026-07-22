<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class RelationshipOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'relationship_goal' => [
                ['value' => 'long_term', 'label' => 'Long-term relationship'],
                ['value' => 'marriage', 'label' => 'Marriage'],
                ['value' => 'friendship', 'label' => 'Friendship first'],
                ['value' => 'not_sure', 'label' => 'Not sure yet'],
            ],
            'relationship_status' => [
                ['value' => 'single', 'label' => 'Single'],
                ['value' => 'divorced', 'label' => 'Divorced'],
                ['value' => 'widowed', 'label' => 'Widowed'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
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
