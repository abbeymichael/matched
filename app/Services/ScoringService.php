<?php

namespace App\Services;

use App\Enums\FieldType;
use App\Models\FieldDefinition;

/**
 * Generic per-field-type scoring functions (§3.2 of AGENTS.md).
 *
 * Every function is a pure function: (FieldDefinition, profileValue, preferenceValue) -> float [0.0, 1.0].
 * These are keyed by fieldType, not by field name, so the engine is reusable
 * across whatever fields an admin activates.
 */
final class ScoringService
{
    public function __construct(private readonly GeoService $geo) {}

    /**
     * Score a single field. Returns null if the field type is not scored (text)
     * so the caller can exclude it from weighting.
     */
    public function score(FieldDefinition $field, mixed $profileValue, mixed $preferenceValue): ?float
    {
        return match ($field->field_type) {
            FieldType::SingleSelect => $this->scoreSingleSelect($profileValue, $preferenceValue),
            FieldType::MultiSelect => $this->scoreMultiSelect($profileValue, $preferenceValue),
            FieldType::Scale => $this->scoreScale($field, $profileValue, $preferenceValue),
            FieldType::Range, FieldType::Number => $this->scoreRange($field, $profileValue, $preferenceValue),
            FieldType::Geo => $this->scoreGeo($profileValue, $preferenceValue),
            FieldType::Text => null,
        };
    }

    /**
     * single_select: 1.0 if candidate's value is in viewer's accepted set, else 0.0.
     */
    public function scoreSingleSelect(mixed $profileValue, mixed $preferenceValue): float
    {
        $accepted = is_array($preferenceValue) ? $preferenceValue : [];
        if (empty($accepted)) {
            return 1.0; // no preference set = accept all (benefit of the doubt)
        }
        if ($profileValue === null) {
            return 0.0; // missing required profile value
        }

        return in_array($profileValue, $accepted, true) ? 1.0 : 0.0;
    }

    /**
     * multi_select: overlap ratio |intersection| / |viewer.desired|, capped at 1.0.
     * Empty viewer desired set = no preference = 1.0 automatically.
     */
    public function scoreMultiSelect(mixed $profileValue, mixed $preferenceValue): float
    {
        $desired = is_array($preferenceValue) ? $preferenceValue : [];
        if (empty($desired)) {
            return 1.0;
        }

        $candidateValues = is_array($profileValue) ? $profileValue : [];
        if (empty($candidateValues)) {
            return 0.0; // candidate skipped optional field but viewer has a preference
        }

        $intersection = array_intersect($candidateValues, $desired);

        return min(1.0, count($intersection) / count($desired));
    }

    /**
     * scale: 1.0 within [min,max], else linear decay based on ordinal distance
     * to nearest bound over the total scale length.
     */
    public function scoreScale(FieldDefinition $field, mixed $profileValue, mixed $preferenceValue): float
    {
        if (! is_array($preferenceValue) || ! isset($preferenceValue['min'], $preferenceValue['max'])) {
            return 1.0; // no preference range set
        }
        if ($profileValue === null) {
            return 0.0;
        }

        $min = (int) $preferenceValue['min'];
        $max = (int) $preferenceValue['max'];
        $value = (int) $profileValue;

        if ($value >= $min && $value <= $max) {
            return 1.0;
        }

        $config = $field->config ?? [];
        $scaleLength = max(1, (int) ($config['scale_length'] ?? ($max - $min + 1) ?: 1));

        $distance = $value < $min ? ($min - $value) : ($value - $max);

        return max(0.0, 1 - ($distance / $scaleLength));
    }

    /**
     * range/number (e.g. age): 1.0 within [min,max], linear decay to 0.0 over a
     * configurable tolerance buffer outside the range (default 20% of range
     * width, minimum 2 units).
     */
    public function scoreRange(FieldDefinition $field, mixed $profileValue, mixed $preferenceValue): float
    {
        if (! is_array($preferenceValue) || ! isset($preferenceValue['min'], $preferenceValue['max'])) {
            return 1.0;
        }
        if ($profileValue === null) {
            return 0.0;
        }

        $min = (float) $preferenceValue['min'];
        $max = (float) $preferenceValue['max'];
        $value = (float) $profileValue;

        if ($value >= $min && $value <= $max) {
            return 1.0;
        }

        $config = $field->config ?? [];
        $bufferPercent = (float) ($config['buffer_percent'] ?? config('matchlock.range_buffer_percent', 0.20));
        $bufferMinimum = (float) ($config['buffer_minimum'] ?? config('matchlock.range_buffer_minimum', 2));
        $width = max(0.0001, $max - $min);
        $buffer = max($width * $bufferPercent, $bufferMinimum);

        $distance = $value < $min ? ($min - $value) : ($value - $max);

        return max(0.0, 1 - ($distance / $buffer));
    }

    /**
     * geo: 1.0 within max_distance_km, linear decay from 1.0 to 0.0 between
     * max_distance_km and (geo_decay_multiplier x max_distance_km), 0.0 beyond.
     *
     * $profileValue: ['lat' => float, 'lng' => float]
     * $preferenceValue: ['lat' => float, 'lng' => float, 'max_distance_km' => int]
     */
    public function scoreGeo(mixed $profileValue, mixed $preferenceValue): float
    {
        if (! is_array($profileValue) || ! isset($profileValue['lat'], $profileValue['lng'])) {
            return 0.0;
        }
        if (! is_array($preferenceValue) || ! isset($preferenceValue['lat'], $preferenceValue['lng'], $preferenceValue['max_distance_km'])) {
            return 1.0;
        }

        $maxDistance = (float) $preferenceValue['max_distance_km'];
        if ($maxDistance <= 0) {
            return 1.0;
        }

        $distance = $this->geo->distanceKm(
            (float) $preferenceValue['lat'],
            (float) $preferenceValue['lng'],
            (float) $profileValue['lat'],
            (float) $profileValue['lng'],
        );

        if ($distance <= $maxDistance) {
            return 1.0;
        }

        $multiplier = (float) config('matchlock.geo_decay_multiplier', 1.5);
        $outerBound = $maxDistance * $multiplier;

        if ($distance >= $outerBound) {
            return 0.0;
        }

        return max(0.0, 1 - (($distance - $maxDistance) / ($outerBound - $maxDistance)));
    }

    /**
     * geo hard-filter check (no buffer): true if within max distance.
     */
    public function withinHardDistance(array $profileValue, array $preferenceValue): bool
    {
        $distance = $this->geo->distanceKm(
            (float) $preferenceValue['lat'],
            (float) $preferenceValue['lng'],
            (float) $profileValue['lat'],
            (float) $profileValue['lng'],
        );

        return $distance <= (float) $preferenceValue['max_distance_km'];
    }
}
