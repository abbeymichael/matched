<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class PhysicalAttributeOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'body_type' => [
                ['value' => 'slim', 'label' => 'Slim'],
                ['value' => 'average', 'label' => 'Average'],
                ['value' => 'athletic', 'label' => 'Athletic'],
                ['value' => 'curvy', 'label' => 'Curvy'],
                ['value' => 'plus_size', 'label' => 'Plus size'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
            ],
            'ethnicity' => [
                ['value' => 'akan', 'label' => 'Akan'],
                ['value' => 'ga_adangbe', 'label' => 'Ga-Adangbe'],
                ['value' => 'ewe', 'label' => 'Ewe'],
                ['value' => 'dagomba', 'label' => 'Dagomba'],
                ['value' => 'fante', 'label' => 'Fante'],
                ['value' => 'ashanti', 'label' => 'Ashanti'],
                ['value' => 'other', 'label' => 'Other'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer not to say'],
            ],
            'hair_color' => [
                ['value' => 'black', 'label' => 'Black'],
                ['value' => 'brown', 'label' => 'Brown'],
                ['value' => 'blonde', 'label' => 'Blonde'],
                ['value' => 'gray', 'label' => 'Gray'],
                ['value' => 'red', 'label' => 'Red'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'eye_color' => [
                ['value' => 'brown', 'label' => 'Brown'],
                ['value' => 'black', 'label' => 'Black'],
                ['value' => 'hazel', 'label' => 'Hazel'],
                ['value' => 'green', 'label' => 'Green'],
                ['value' => 'blue', 'label' => 'Blue'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ];

        $this->seed($options);
    }

    private function seed(array $options): void
    {
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
