<?php

namespace Database\Factories;

use App\Models\Preferences;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'phone' => '+233' . $this->faker->unique()->numerify('#########'),
            'phone_verified_at' => now(),
            'profile_locked' => false,
            'match_threshold' => 60,
            'status' => 'active',
            'verification_status' => 'approved',
            'is_admin' => false,
            'consented_at' => now(),
            'last_active_at' => now(),
        ];
    }

    public function withProfile(array $overrides = []): static
    {
        return $this->afterCreating(function (User $user) use ($overrides) {
            Profile::create(array_merge([
                'user_id' => $user->id,
                'display_name' => $this->faker->firstName(),
                'date_of_birth' => now()->subYears(28)->toDateString(),
                'gender' => 'male',
                'city' => 'Accra',
                'lat' => 5.6037,
                'lng' => -0.1870,
            ], $overrides));
        });
    }

    public function withPreferences(array $overrides = []): static
    {
        return $this->afterCreating(function (User $user) use ($overrides) {
            Preferences::create(array_merge([
                'user_id' => $user->id,
                'age_min' => 21,
                'age_max' => 40,
                'accepted_genders' => ['female'],
                'max_distance_km' => 50,
            ], $overrides));
        });
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'profile_locked' => true,
            'locked_at' => now(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verification_status' => 'approved',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => 'suspended',
            'suspension_ends_at' => now()->addDays(7),
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn () => [
            'status' => 'banned',
            'banned_at' => now(),
            'ban_reason' => 'Test ban',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'is_admin' => true,
        ]);
    }
}
