<?php

namespace App\Actions\Matching;

use App\Models\FieldDefinition;
use App\Models\Preferences;
use App\Models\Profile;
use App\Services\GeoService;
use App\Services\ScoringService;

final class ComputeMatchScore
{
    public function __construct(
        private readonly ScoringService $scoring,
        private readonly GeoService $geo,
    ) {}

    public function passesHardFilters(Preferences $viewerPrefs, Profile $viewerProfile, Profile $targetProfile, array $targetFieldValues, array $viewerPreferenceValues): bool
    {
        // Gender
        if (! in_array($targetProfile->gender, $viewerPrefs->accepted_genders ?? [], true)) {
            return false;
        }

        // Distance
        if ($viewerPrefs->max_distance_km > 0) {
            $distance = $this->geo->distanceKm(
                (float) $viewerProfile->lat,
                (float) $viewerProfile->lng,
                (float) $targetProfile->lat,
                (float) $targetProfile->lng,
            );
            if ($distance > $viewerPrefs->max_distance_km) {
                return false;
            }
        }

        // Age
        $age = $targetProfile->date_of_birth?->age;
        if ($age !== null && ($age < $viewerPrefs->age_min || $age > $viewerPrefs->age_max)) {
            return false;
        }

        // Admin-configured hard filters
        $hardFilters = FieldDefinition::activeHardFilters();
        foreach ($hardFilters as $field) {
            $preferenceValue = $viewerPreferenceValues[$field->key] ?? null;
            $profileValue = $targetFieldValues[$field->key] ?? null;

            if (! $this->hardFilterMatch($field, $profileValue, $preferenceValue)) {
                return false;
            }
        }

        return true;
    }

    private function hardFilterMatch(FieldDefinition $field, mixed $profileValue, mixed $preferenceValue): bool
    {
        return match ($field->field_type) {
            default => $this->scoring->score($field, $profileValue, $preferenceValue) > 0,
        };
    }

    public function score(Preferences $viewerPrefs, Profile $viewerProfile, Profile $targetProfile, array $targetFieldValues, array $viewerPreferenceValues): int
    {
        $weightedFields = FieldDefinition::activeWeighted();
        $totalWeight = $weightedFields->sum('weight');

        if ($totalWeight <= 0) {
            return 100;
        }

        $score = 0.0;
        foreach ($weightedFields as $field) {
            $preferenceValue = $viewerPreferenceValues[$field->key] ?? null;
            $profileValue = match ($field->key) {
                'age' => $targetProfile->date_of_birth?->age,
                'geo' => ['lat' => $targetProfile->lat, 'lng' => $targetProfile->lng],
                default => $targetFieldValues[$field->key] ?? null,
            };

            $fieldScore = $this->scoring->score($field, $profileValue, $preferenceValue) ?? 1.0;
            $score += ($field->weight / $totalWeight) * $fieldScore;
        }

        // Core geo + age weight (1.0 each, included as implicit core fields)
        // Simplified: add age/geo scores directly to weighted average with fixed weights.
        $ageScore = $this->scoring->scoreRange(new FieldDefinition(['config' => []]), $targetProfile->date_of_birth?->age, ['min' => $viewerPrefs->age_min, 'max' => $viewerPrefs->age_max]);
        $geoScore = $this->scoring->scoreGeo(
            ['lat' => $targetProfile->lat, 'lng' => $targetProfile->lng],
            ['lat' => $viewerProfile->lat, 'lng' => $viewerProfile->lng, 'max_distance_km' => $viewerPrefs->max_distance_km]
        );

        $score = ($score * $totalWeight + $ageScore + $geoScore) / ($totalWeight + 2);

        return (int) round($score * 100);
    }
}
