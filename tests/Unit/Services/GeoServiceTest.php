<?php

namespace Tests\Unit\Services;

use App\Services\GeoService;
use Tests\TestCase;

class GeoServiceTest extends TestCase
{
    public function test_distance_between_identical_points_is_zero(): void
    {
        $geo = new GeoService();

        $this->assertEqualsWithDelta(0.0, $geo->distanceKm(5.6037, -0.1870, 5.6037, -0.1870), 0.001);
    }

    public function test_distance_between_accra_and_kumasi_is_roughly_correct(): void
    {
        $geo = new GeoService();

        // Accra (5.6037, -0.1870) to Kumasi (6.6885, -1.6244) ~ 200km
        $distance = $geo->distanceKm(5.6037, -0.1870, 6.6885, -1.6244);

        $this->assertEqualsWithDelta(200, $distance, 20);
    }
}
