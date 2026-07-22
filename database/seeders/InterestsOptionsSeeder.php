<?php

namespace Database\Seeders;

use App\Models\ProfileFieldOption;
use Illuminate\Database\Seeder;

class InterestsOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            'interests' => [
                ['value' => 'football', 'label' => 'Football / Soccer'],
                ['value' => 'fufu_joints', 'label' => 'Fufu joints / local food'],
                ['value' => 'church_events', 'label' => 'Church / mosque events'],
                ['value' => 'highlife_afrobeats', 'label' => 'Highlife / Afrobeats'],
                ['value' => 'ghanaian_cinema', 'label' => 'Ghanaian cinema'],
                ['value' => 'reading', 'label' => 'Reading'],
                ['value' => 'hiking', 'label' => 'Hiking / nature'],
                ['value' => 'cooking', 'label' => 'Cooking'],
                ['value' => 'dancing', 'label' => 'Dancing'],
                ['value' => 'travel', 'label' => 'Travel'],
                ['value' => 'gaming', 'label' => 'Gaming'],
                ['value' => 'photography', 'label' => 'Photography'],
                ['value' => 'music', 'label' => 'Music'],
                ['value' => 'fitness', 'label' => 'Fitness'],
                ['value' => 'entrepreneurship', 'label' => 'Entrepreneurship'],
            ],
            'music_genres' => [
                ['value' => 'highlife', 'label' => 'Highlife'],
                ['value' => 'afrobeats', 'label' => 'Afrobeats'],
                ['value' => 'gospel', 'label' => 'Gospel'],
                ['value' => 'hiphop', 'label' => 'Hip-hop / Rap'],
                ['value' => 'rnb', 'label' => 'R&B'],
                ['value' => 'reggae', 'label' => 'Reggae'],
                ['value' => 'classical', 'label' => 'Classical'],
                ['value' => 'jazz', 'label' => 'Jazz'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'media_genres' => [
                ['value' => 'drama', 'label' => 'Drama'],
                ['value' => 'comedy', 'label' => 'Comedy'],
                ['value' => 'documentary', 'label' => 'Documentary'],
                ['value' => 'thriller', 'label' => 'Thriller'],
                ['value' => 'romance', 'label' => 'Romance'],
                ['value' => 'action', 'label' => 'Action'],
                ['value' => 'reality', 'label' => 'Reality TV'],
                ['value' => 'other', 'label' => 'Other'],
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
