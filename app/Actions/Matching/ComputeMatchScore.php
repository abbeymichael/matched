<?php

namespace App\Actions\Matching;

use App\Enums\FieldType;
use App\Models\FieldDefinition;
use App\Models\Preferences;
use App\Models\Profile;
use App\Services\GeoService;
use App\Services\ScoringService;

/**
 * The generic scoring engine (§3 of AGENTS.md). Core fields (gender, age,
 * location — §2.1) are handled directly against the `profiles`/`preferences`
 * columns since they're not admin-togglable rows in `field_definitions`.
 * Admin-configured library fields (§2.2) are handled generically, keyed by
 * `field_type`, so the engine is reusable across whatever fields an admin
 * activates without a code change.
 */
final class ComputeMatchScore
{
    public function __construct(
        private readonly ScoringService $scoring,
        private readonly GeoService $geo,
    ) {}

    /**
     * Hard filters are checked before scoring runs — a candidate failing any
     * one of these never appears on the viewer's list, regardless of score (§3.1).
     */
    public function passesHardFilters(
        Preferences $viewerPrefs,
        Profile $viewerProfile,
        Profile $targetProfile,
        array $targetFieldValues,
        array $viewerPreferenceValues
    ): bool {
        // Gender is always a hard filter (§2.1). Missing viewer preference =
        // accept all; missing candidate value would fail (but gender is a
        // required core field, so this shouldn't happen in practice).
        $acceptedGenders = $viewerPrefs->accepted_genders ?? [];
        if (! empty($acceptedGenders) && ! in_array($targetProfile->gender, $acceptedGenders, true)) {
            return false;
        }

        // Distance: hard cutoff, no buffer (§3.1 — buffer is only for weighted decay).
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

        // Age: hard cutoff, no buffer.
        $age = $targetProfile->date_of_birth?->age;
        if ($age === null || $age < $viewerPrefs->age_min || $age > $viewerPrefs->age_max) {
            return false;
        }

        // Admin-configured hard filters (§3.1 per-field-type hard cutoffs).
        foreach (FieldDefinition::activeHardFilters() as $field) {
            $preferenceValue = $viewerPreferenceValues[$field->key] ?? null;
            $profileValue = $targetFieldValues[$field->key] ?? null;

            if (! $this->hardFilterMatch($field, $profileValue, $preferenceValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Per-field-type hard-cutoff check (§3.1), deliberately NOT reusing the
     * buffered weighted-scoring functions — a hard filter has no tolerance.
     */
    private function hardFilterMatch(FieldDefinition $field, mixed $profileValue, mixed $preferenceValue): bool
    {
        return match ($field->field_type) {
            FieldType::SingleSelect => $this->hardFilterSingleSelect($profileValue, $preferenceValue),
            FieldType::MultiSelect => $this->hardFilterMultiSelect($profileValue, $preferenceValue),
            FieldType::Scale, FieldType::Range, FieldType::Number => $this->hardFilterRange($profileValue, $preferenceValue),
            FieldType::Geo => true, // geo is always core, never an admin-library hard filter
            FieldType::Text => true, // text fields are never scored/filtered
        };
    }

    private function hardFilterSingleSelect(mixed $profileValue, mixed $preferenceValue): bool
    {
        $accepted = is_array($preferenceValue) ? $preferenceValue : [];
        if (empty($accepted)) {
            return true; // no preference set = accept all
        }

        return $profileValue !== null && in_array($profileValue, $accepted, true);
    }

    private function hardFilterMultiSelect(mixed $profileValue, mixed $preferenceValue): bool
    {
        $desired = is_array($preferenceValue) ? $preferenceValue : [];
        if (empty($desired)) {
            return true;
        }

        $candidateValues = is_array($profileValue) ? $profileValue : [];

        return count(array_intersect($candidateValues, $desired)) > 0;
    }

    private function hardFilterRange(mixed $profileValue, mixed $preferenceValue): bool
    {
        if (! is_array($preferenceValue) || ! isset($preferenceValue['min'], $preferenceValue['max'])) {
            return true; // no preference range set = accept all
        }
        if ($profileValue === null) {
            return false;
        }

        $value = (float) $profileValue;

        return $value >= (float) $preferenceValue['min'] && $value <= (float) $preferenceValue['max'];
    }

    /**
     * Weighted score for a viewer→candidate pair, 0-100 (§3.3). Core age and
     * location contribute at an implicit weight of 1.0 each alongside the
     * admin's normalized weighted fields, since §2.1 marks both as "weighted"
     * scoring roles even though they live outside `field_definitions`.
     */
    public function score(
        Preferences $viewerPrefs,
        Profile $viewerProfile,
        Profile $targetProfile,
        array $targetFieldValues,
        array $viewerPreferenceValues
    ): int {
        $weightedFields = FieldDefinition::activeWeighted();
        $libraryWeight = $weightedFields->sum('weight');

        $librarySum = 0.0;
        foreach ($weightedFields as $field) {
            $preferenceValue = $viewerPreferenceValues[$field->key] ?? null;
            $profileValue = $targetFieldValues[$field->key] ?? null;

            $fieldScore = $this->scoring->score($field, $profileValue, $preferenceValue) ?? 1.0;
            $librarySum += $field->weight * $fieldScore;
        }

        $ageScore = $this->scoring->scoreRange(
            new FieldDefinition(['config' => []]),
            $targetProfile->date_of_birth?->age,
            ['min' => $viewerPrefs->age_min, 'max' => $viewerPrefs->age_max]
        );

        $geoScore = $this->scoring->scoreGeo(
            ['lat' => $targetProfile->lat, 'lng' => $targetProfile->lng],
            ['lat' => $viewerProfile->lat, 'lng' => $viewerProfile->lng, 'max_distance_km' => $viewerPrefs->max_distance_km]
        );

        $coreWeight = 2.0; // age (1.0) + geo (1.0), always present
        $totalWeight = $libraryWeight + $coreWeight;

        if ($totalWeight <= 0) {
            return 100; // edge case (§3): avoid division by zero
        }

        $finalScore = ($librarySum + $ageScore + $geoScore) / $totalWeight;

        return (int) round(max(0.0, min(1.0, $finalScore)) * 100);
    }
}
