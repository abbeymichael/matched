<?php

namespace Tests\Unit\Actions\Social;

use App\Actions\Social\RegisterInterest;
use App\Models\Interest;
use App\Models\MutualMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterInterestTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_sided_interest_does_not_create_match(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $result = (new RegisterInterest())->handle($a, $b);

        $this->assertNull($result);
        $this->assertDatabaseHas('interests', ['from_id' => $a->id, 'to_id' => $b->id]);
        $this->assertDatabaseCount('matches', 0);
    }

    public function test_reciprocal_interest_creates_mutual_match(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        (new RegisterInterest())->handle($a, $b);
        $match = (new RegisterInterest())->handle($b, $a);

        $this->assertInstanceOf(MutualMatch::class, $match);
        $this->assertTrue($match->includesUser($a->id));
        $this->assertTrue($match->includesUser($b->id));
        $this->assertDatabaseCount('matches', 1);
    }

    public function test_lexicographically_smaller_uuid_is_stored_as_user_a(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        (new RegisterInterest())->handle($a, $b);
        $match = (new RegisterInterest())->handle($b, $a);

        $expectedA = $a->id < $b->id ? $a->id : $b->id;
        $this->assertSame($expectedA, $match->user_a_id);
    }

    public function test_cannot_register_interest_in_self(): void
    {
        $a = User::factory()->create();

        $result = (new RegisterInterest())->handle($a, $a);

        $this->assertNull($result);
        $this->assertDatabaseCount('interests', 0);
    }
}
