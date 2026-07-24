<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_fields(): void
    {
        $user = User::factory()->locked()->create();

        $this->actingAs($user)->get('/admin/fields')->assertForbidden();
    }

    public function test_admin_can_access_admin_fields(): void
    {
        $admin = User::factory()->locked()->admin()->create();

        $this->actingAs($admin)->get('/admin/fields')->assertOk();
    }

    public function test_guest_redirected_from_admin_routes(): void
    {
        $this->get('/admin/users')->assertRedirect();
    }
}
