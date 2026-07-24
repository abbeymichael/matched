<?php

namespace Tests\Unit\Actions\Matching;

use App\Actions\Matching\ComputeMatchScore;
use App\Models\FieldDefinition;
use App\Models\Preferences;
use App\Models\Profile;
use App\Models\User;
use App\Services\GeoService;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputeMatchScoreTest extends TestCase
{
    use RefreshDatabase;

    private ComputeMatchScore $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ComputeMatchScore(new ScoringService(new GeoService()), new GeoService());
    }

    private function makeProfile(array $overrides = []): Profile
    {
        $user = User::factory()->create();

        return Profile::create(array_merge([
            'user_id' => $user->id,
            'display_name' => 'Test',
            'date_of_birth' => now()->subYears(28)->toDateString(),
            'gender' => 'female',
            'city' => 'Accra',
            'lat' => 5.6037,
            'lng' => -0.1870,
        ], $overrides));
    }

    private function makePreferences(User $user, array $overrides = []): Preferences
    {
        return Preferences::create(array_merge([
            'user_id' => $user->id,
            'age_min' => 21,
            'age_max' => 40,
            'accepted_genders' => ['female'],
            'max_distance_km' => 100,
        ], $overrides));
    }

    public function test_passes_hard_filters_when_all_core_conditions_met(): void
    {
        $viewer = $this->makeProfile(['gender' => 'male']);
        $viewerPrefs = $this->makePreferences($viewer->user);
        $target = $this->makeProfile(['gender' => 'female']);

        $this->assertTrue($this->action->passesHardFilters($viewerPrefs, $viewer, $target, [], []));
    }

    public function test_fails_hard_filters_when_gender_not_accepted(): void
    {
        $viewer = $this->makeProfile(['gender' => 'male']);
        $viewerPrefs = $this->makePreferences($viewer->user, ['accepted_genders' => ['female']]);
        $target = $this->makeProfile(['gender' => 'male']);

        $this->assertFalse($this->action->passesHardFilters($viewerPrefs, $viewer, $target, [], []));
    }

    public function test_fails_hard_filters_when_outside_age_range(): void
    {
        $viewer = $this->makeProfile(['gender' => 'male']);
        $viewerPrefs = $this->makePreferences($viewer->user, ['age_min' => 21, 'age_max' => 25]);
        $target = $this->makeProfile(['gender' => 'female', 'date_of_birth' => now()->subYears(40)->toDateString()]);

        $this->assertFalse($this->action->passesHardFilters($viewerPrefs, $viewer, $target, [], []));
    }

    public function test_fails_hard_filters_when_outside_distance(): void
    {
        $viewer = $this->makeProfile(['gender' => 'male', 'lat' => 5.6037, 'lng' => -0.1870]);
        $viewerPrefs = $this->makePreferences($viewer->user, ['max_distance_km' => 10]);
        $target = $this->makeProfile(['gender' => 'female', 'lat' => 10.0, 'lng' => -5.0]);

        $this->assertFalse($this->action->passesHardFilters($viewerPrefs, $viewer, $target, [], []));
    }

    public function test_admin_hard_filter_field_excludes_non_matching_candidate(): void
    {
        $field = FieldDefinition::factory()->hardFilter()->singleSelect()->create(['key' => 'smoking']);

        $viewer = $this->makeProfile(['gender' => 'male']);
        $viewerPrefs = $this->makePreferences($viewer->user);
        $target = $this->makeProfile(['gender' => 'female']);

        $result = $this->action->passesHardFilters(
            $viewerPrefs,
            $viewer,
            $target,
            ['smoking' => 'smoker'],
            ['smoking' => ['non_smoker']]
        );

        $this->assertFalse($result);
    }

    public function test_score_returns_100_when_perfectly_matched_with_no_library_fields(): void
    {
        $viewer = $this->makeProfile(['gender' => 'male']);
        $viewerPrefs = $this->makePreferences($viewer->user, ['age_min' => 20, 'age_max' => 35]);
        $target = $this->makeProfile(['gender' => 'female', 'date_of_birth' => now()->subYears(28)->toDateString()]);

        $score = $this->action->score($viewerPrefs, $viewer, $target, [], []);

        $this->assertSame(100, $score);
    }

    public function test_score_decreases_with_weighted_field_mismatch(): void
    {
        FieldDefinition::factory()->singleSelect()->create(['key' => 'diet', 'weight' => 2.0]);

        $viewer = $this->makeProfile(['gender' => 'male']);
        $viewerPrefs = $this->makePreferences($viewer->user, ['age_min' => 20, 'age_max' => 35]);
        $target = $this->makeProfile(['gender' => 'female', 'date_of_birth' => now()->subYears(28)->toDateString()]);

        $score = $this->action->score(
            $viewerPrefs,
            $viewer,
            $target,
            ['diet' => 'vegan'],
            ['diet' => ['vegetarian']]
        );

        $this->assertLessThan(100, $score);
    }
}
