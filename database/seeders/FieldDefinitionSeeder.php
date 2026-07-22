<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\FieldDefinition;
use Illuminate\Database\Seeder;

class FieldDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['key' => 'body_type', 'label' => 'Body Type', 'category' => 'Physical Attribute', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'ethnicity', 'label' => 'Ethnicity', 'category' => 'Physical Attribute', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'hair_color', 'label' => 'Hair Color', 'category' => 'Physical Attribute', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'eye_color', 'label' => 'Eye Color', 'category' => 'Physical Attribute', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],

            ['key' => 'relationship_goal', 'label' => 'Relationship Goal', 'category' => 'Relationship', 'type' => FieldType::SingleSelect, 'active' => true, 'weight' => 3.0, 'required' => true],
            ['key' => 'relationship_status', 'label' => 'Relationship Status', 'category' => 'Relationship', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],

            ['key' => 'education_level', 'label' => 'Education Level', 'category' => 'Career/Education', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'industry', 'label' => 'Industry', 'category' => 'Career/Education', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'income_range', 'label' => 'Income Range', 'category' => 'Career/Education', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'work_schedule', 'label' => 'Work Schedule', 'category' => 'Career/Education', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],

            ['key' => 'living_situation', 'label' => 'Living Situation', 'category' => 'Lifestyle', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'exercise_frequency', 'label' => 'Exercise Frequency', 'category' => 'Lifestyle', 'type' => FieldType::Scale, 'active' => true, 'weight' => 1.0, 'required' => false, 'config' => ['scale_length' => 4]],
            ['key' => 'diet', 'label' => 'Diet', 'category' => 'Lifestyle', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'smoking', 'label' => 'Smoking', 'category' => 'Lifestyle', 'type' => FieldType::SingleSelect, 'active' => true, 'weight' => 2.0, 'required' => true],
            ['key' => 'drinking', 'label' => 'Drinking', 'category' => 'Lifestyle', 'type' => FieldType::Scale, 'active' => true, 'weight' => 1.5, 'required' => false, 'config' => ['scale_length' => 4]],
            ['key' => 'cannabis_use', 'label' => 'Cannabis Use', 'category' => 'Lifestyle', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'pets', 'label' => 'Pets', 'category' => 'Lifestyle', 'type' => FieldType::MultiSelect, 'active' => false, 'weight' => 1.0, 'required' => false, 'config' => ['max_selections' => 3]],
            ['key' => 'travel_frequency', 'label' => 'Travel Frequency', 'category' => 'Lifestyle', 'type' => FieldType::Scale, 'active' => false, 'weight' => 1.0, 'required' => false, 'config' => ['scale_length' => 4]],

            ['key' => 'personality_type', 'label' => 'Personality Type', 'category' => 'Personality/Values', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'love_language', 'label' => 'Love Language', 'category' => 'Personality/Values', 'type' => FieldType::SingleSelect, 'active' => true, 'weight' => 1.5, 'required' => true],
            ['key' => 'politics', 'label' => 'Politics', 'category' => 'Personality/Values', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'religion', 'label' => 'Religion', 'category' => 'Personality/Values', 'type' => FieldType::SingleSelect, 'active' => true, 'weight' => 2.5, 'required' => true],
            ['key' => 'conflict_style', 'label' => 'Conflict Style', 'category' => 'Personality/Values', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'core_values', 'label' => 'Core Values', 'category' => 'Personality/Values', 'type' => FieldType::MultiSelect, 'active' => true, 'weight' => 2.0, 'required' => true, 'config' => ['max_selections' => 5]],

            ['key' => 'interests', 'label' => 'Interests & Hobbies', 'category' => 'Interests', 'type' => FieldType::MultiSelect, 'active' => true, 'weight' => 1.5, 'required' => false, 'config' => ['max_selections' => 5]],
            ['key' => 'music_genres', 'label' => 'Music Genres', 'category' => 'Interests', 'type' => FieldType::MultiSelect, 'active' => false, 'weight' => 1.0, 'required' => false, 'config' => ['max_selections' => 5]],
            ['key' => 'media_genres', 'label' => 'Media/TV Genres', 'category' => 'Interests', 'type' => FieldType::MultiSelect, 'active' => false, 'weight' => 1.0, 'required' => false, 'config' => ['max_selections' => 5]],

            ['key' => 'communication_style', 'label' => 'Communication Style', 'category' => 'Communication/Matching', 'type' => FieldType::SingleSelect, 'active' => false, 'weight' => 1.0, 'required' => false],
            ['key' => 'dealbreakers', 'label' => 'Dealbreakers', 'category' => 'Communication/Matching', 'type' => FieldType::MultiSelect, 'active' => true, 'weight' => 2.0, 'required' => false, 'config' => ['max_selections' => 3]],
            ['key' => 'must_haves', 'label' => 'Must-Haves', 'category' => 'Communication/Matching', 'type' => FieldType::MultiSelect, 'active' => false, 'weight' => 1.0, 'required' => false, 'config' => ['max_selections' => 3]],
        ];

        foreach ($definitions as $index => $def) {
            FieldDefinition::firstOrCreate(
                ['key' => $def['key']],
                [
                    'label' => $def['label'],
                    'description' => null,
                    'category' => $def['category'],
                    'field_type' => $def['type'],
                    'is_active' => $def['active'],
                    'is_hard_filter' => false,
                    'is_required' => $def['required'],
                    'is_core' => false,
                    'weight' => $def['weight'],
                    'sort_order' => $index + 1,
                    'config' => $def['config'] ?? null,
                ]
            );
        }
    }
}
