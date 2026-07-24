<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_locked_user_lands_on_dashboard_after_login(): void
    {
        $user = User::factory()->locked()->create(['phone' => '+233244123456']);

        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_unlocked_user_redirected_away_from_dashboard(): void
    {
        $user = User::factory()->create(['phone' => '+233244123457', 'profile_locked' => false]);

        $this->actingAs($user);
        $this->get('/dashboard')->assertRedirect();
    }

    public function test_banned_user_cannot_reach_dashboard(): void
    {
        $user = User::factory()->locked()->banned()->create(['phone' => '+233244123458']);

        $this->actingAs($user);
        $this->get('/dashboard')->assertRedirect();
    }
}
