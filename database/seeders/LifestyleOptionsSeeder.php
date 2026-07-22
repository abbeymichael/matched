<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class LifestyleOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'living_situation' => [
                ['value' => 'alone', 'label' => 'Live alone'],
                ['value' => 'with_family', 'label' => 'With family'],
                ['value' => 'with_roommates', 'label' => 'With roommates'],
                ['value' => 'with_partner', 'label' => 'With partner / children'],
            ],
            'exercise_frequency' => [
                ['value' => 0, 'label' => 'Never'],
                ['value' => 1, 'label' => 'Rarely'],
                ['value' => 2, 'label' => 'Sometimes'],
                ['value' => 3, 'label' => 'Often'],
                ['value' => 4, 'label' => 'Daily'],
            ],
            'diet' => [
                ['value' => 'no_restrictions', 'label' => 'No restrictions'],
                ['value' => 'vegetarian', 'label' => 'Vegetarian'],
                ['value' => 'vegan', 'label' => 'Vegan'],
                ['value' => 'halal', 'label' => 'Halal'],
                ['value' => 'kosher', 'label' => 'Kosher'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'smoking' => [
                ['value' => 'never', 'label' => 'Never'],
                ['value' => 'occasionally', 'label' => 'Occasionally'],
                ['value' => 'regularly', 'label' => 'Regularly'],
                ['value' => 'trying_to_quit', 'label' => 'Trying to quit'],
            ],
            'drinking' => [
                ['value' => 0, 'label' => 'Never'],
                ['value' => 1, 'label' => 'Rarely'],
                ['value' => 2, 'label' => 'Socially'],
                ['value' => 3, 'label' => 'Often'],
                ['value' => 4, 'label' => 'Daily'],
            ],
            'cannabis_use' => [
                ['value' => 'never', 'label' => 'Never'],
                ['value' => 'occasionally', 'label' => 'Occasionally'],
                ['value' => 'regularly', 'label' => 'Regularly'],
            ],
            'pets' => [
                ['value' => 'dogs', 'label' => 'Dogs'],
                ['value' => 'cats', 'label' => 'Cats'],
                ['value' => 'birds', 'label' => 'Birds'],
                ['value' => 'fish', 'label' => 'Fish'],
                ['value' => 'no_pets', 'label' => 'No pets'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'travel_frequency' => [
                ['value' => 0, 'label' => 'Rarely'],
                ['value' => 1, 'label' => 'Once a year'],
                ['value' => 2, 'label' => 'A few times a year'],
                ['value' => 3, 'label' => 'Monthly'],
                ['value' => 4, 'label' => 'Often'],
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
