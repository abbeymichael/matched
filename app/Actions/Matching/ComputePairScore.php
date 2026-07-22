<?php

namespace App\Actions\Matching;

use App\Models\MatchScore;
use App\Models\User;

final class ComputePairScore
{
    public function __construct(private readonly ComputeMatchScore $engine) {}

    public function handle(User $viewer, User $target): void
    {
        $viewerPrefs = $viewer->preferences;
        $targetPrefs = $target->preferences;
        $viewerProfile = $viewer->profile;
        $targetProfile = $target->profile;

        if (! $viewerPrefs || ! $targetPrefs || ! $viewerProfile || ! $targetProfile) {
            return;
        }

        $viewerFieldValues = $viewer->profileFieldValues->keyBy('field_key')->map->value->toArray();
        $targetFieldValues = $target->profileFieldValues->keyBy('field_key')->map->value->toArray();
        $viewerPreferenceValues = $viewer->preferenceFieldValues->keyBy('field_key')->map->value->toArray();
        $targetPreferenceValues = $target->preferenceFieldValues->keyBy('field_key')->map->value->toArray();

        $passed = $this->engine->passesHardFilters($viewerPrefs, $viewerProfile, $targetProfile, $targetFieldValues, $viewerPreferenceValues);
        $score = $passed ? $this->engine->score($viewerPrefs, $viewerProfile, $targetProfile, $targetFieldValues, $viewerPreferenceValues) : 0;

        MatchScore::updateOrCreate(
            ['viewer_id' => $viewer->id, 'target_id' => $target->id],
            ['score' => $score, 'passed_hard_filters' => $passed, 'updated_at' => now()]
        );
    }
}
