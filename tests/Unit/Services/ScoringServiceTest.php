<?php

namespace Tests\Unit\Services;

use App\Models\FieldDefinition;
use App\Services\GeoService;
use App\Services\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    private ScoringService $scoring;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoring = new ScoringService(new GeoService());
    }

    public function test_single_select_matches_when_value_in_accepted_set(): void
    {
        $this->assertSame(1.0, $this->scoring->scoreSingleSelect('dog_lover', ['dog_lover', 'cat_lover']));
    }

    public function test_single_select_no_preference_accepts_all(): void
    {
        $this->assertSame(1.0, $this->scoring->scoreSingleSelect('anything', []));
    }

    public function test_single_select_fails_when_not_in_accepted_set(): void
    {
        $this->assertSame(0.0, $this->scoring->scoreSingleSelect('cat_lover', ['dog_lover']));
    }

    public function test_multi_select_full_overlap_scores_one(): void
    {
        $this->assertSame(1.0, $this->scoring->scoreMultiSelect(['hiking', 'reading'], ['hiking', 'reading']));
    }

    public function test_multi_select_partial_overlap_scores_ratio(): void
    {
        $this->assertSame(0.5, $this->scoring->scoreMultiSelect(['hiking'], ['hiking', 'reading']));
    }

    public function test_multi_select_no_preference_accepts_all(): void
    {
        $this->assertSame(1.0, $this->scoring->scoreMultiSelect(['anything'], []));
    }

    public function test_range_within_bounds_scores_one(): void
    {
        $field = new FieldDefinition(['config' => []]);
        $this->assertSame(1.0, $this->scoring->scoreRange($field, 30, ['min' => 25, 'max' => 35]));
    }

    public function test_range_outside_bounds_decays_linearly(): void
    {
        $field = new FieldDefinition(['config' => ['buffer_percent' => 0.20, 'buffer_minimum' => 2]]);
        // range width 10 (25-35), buffer = max(10*0.2, 2) = 2
        $score = $this->scoring->scoreRange($field, 36, ['min' => 25, 'max' => 35]);
        $this->assertEqualsWithDelta(0.5, $score, 0.01);
    }

    public function test_range_far_outside_bounds_scores_zero(): void
    {
        $field = new FieldDefinition(['config' => []]);
        $score = $this->scoring->scoreRange($field, 100, ['min' => 25, 'max' => 35]);
        $this->assertSame(0.0, $score);
    }

    public function test_geo_within_distance_scores_one(): void
    {
        $score = $this->scoring->scoreGeo(
            ['lat' => 5.6037, 'lng' => -0.1870],
            ['lat' => 5.6037, 'lng' => -0.1870, 'max_distance_km' => 50]
        );
        $this->assertSame(1.0, $score);
    }

    public function test_geo_beyond_decay_bound_scores_zero(): void
    {
        $score = $this->scoring->scoreGeo(
            ['lat' => 5.6037, 'lng' => -0.1870],
            ['lat' => 10.0, 'lng' => -5.0, 'max_distance_km' => 5]
        );
        $this->assertSame(0.0, $score);
    }

    public function test_within_hard_distance(): void
    {
        $this->assertTrue($this->scoring->withinHardDistance(
            ['lat' => 5.6037, 'lng' => -0.1870],
            ['lat' => 5.6037, 'lng' => -0.1870, 'max_distance_km' => 10]
        ));
    }
}
