<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_field_to_an_operator(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Operator Sekretariat',
            'email' => 'operator@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);

        $response->assertRedirect(route('admin.users.create', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'operator@example.com',
            'role' => User::ROLE_OPERATOR,
            'bidang' => 'Sekretariat',
        ]);
    }

    public function test_operator_must_have_an_assigned_field(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Operator Tanpa Bidang',
            'email' => 'operator-tanpa-bidang@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_OPERATOR,
        ]);

        $response->assertSessionHasErrors('bidang');
        $this->assertDatabaseMissing('users', ['email' => 'operator-tanpa-bidang@example.com']);
    }
}
