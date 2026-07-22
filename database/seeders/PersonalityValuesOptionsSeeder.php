<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class PersonalityValuesOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'personality_type' => [
                ['value' => 'introvert', 'label' => 'Introvert'],
                ['value' => 'extrovert', 'label' => 'Extrovert'],
                ['value' => 'ambivert', 'label' => 'Ambivert'],
            ],
            'love_language' => [
                ['value' => 'words_of_affirmation', 'label' => 'Words of Affirmation'],
                ['value' => 'acts_of_service', 'label' => 'Acts of Service'],
                ['value' => 'receiving_gifts', 'label' => 'Receiving Gifts'],
                ['value' => 'quality_time', 'label' => 'Quality Time'],
                ['value' => 'physical_touch', 'label' => 'Physical Touch'],
            ],
            'politics' => [
                ['value' => 'apolitical', 'label' => 'Apolitical'],
                ['value' => 'liberal', 'label' => 'Liberal'],
                ['value' => 'conservative', 'label' => 'Conservative'],
                ['value' => 'moderate', 'label' => 'Moderate'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
            ],
            'religion' => [
                ['value' => 'christianity', 'label' => 'Christianity'],
                ['value' => 'islam', 'label' => 'Islam'],
                ['value' => 'traditional', 'label' => 'Traditional African Religion'],
                ['value' => 'other', 'label' => 'Other'],
                ['value' => 'none', 'label' => 'None'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
            ],
            'conflict_style' => [
                ['value' => 'discuss_calmly', 'label' => 'Discuss calmly'],
                ['value' => 'need_space', 'label' => 'Need space first'],
                ['value' => 'address_immediately', 'label' => 'Address immediately'],
                ['value' => 'avoid', 'label' => 'Avoid conflict'],
            ],
            'core_values' => [
                ['value' => 'faith', 'label' => 'Faith'],
                ['value' => 'family', 'label' => 'Family'],
                ['value' => 'community', 'label' => 'Community'],
                ['value' => 'respect_for_elders', 'label' => 'Respect for elders'],
                ['value' => 'tradition', 'label' => 'Tradition'],
                ['value' => 'honesty', 'label' => 'Honesty'],
                ['value' => 'loyalty', 'label' => 'Loyalty'],
                ['value' => 'ambition', 'label' => 'Ambition'],
                ['value' => 'kindness', 'label' => 'Kindness'],
                ['value' => 'growth', 'label' => 'Personal growth'],
            ],
        ];

        foreach ($options as $fieldKey => $items) {
            foreach ($items as $index => $item) {
                ProfileFieldOption::firstOrCreate(
                    ['field_key' => $fieldKey, 'value' => (string) $item['value']],
                    ['label' => $item['label'], 'sort_order' => $index, 'is_active' => true]
                );
            }
        }
    }
}
