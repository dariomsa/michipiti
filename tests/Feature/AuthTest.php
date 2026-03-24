<?php

namespace Tests\Feature;

use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_screen_is_available(): void
    {
        $this->seed(RolesSeeder::class);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Crear usuario con roles');
    }

    public function test_user_can_register_with_multiple_roles(): void
    {
        $this->seed(RolesSeeder::class);

        $response = $this->post(route('register.store'), [
            'name' => 'Maria Torres',
            'email' => 'maria@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles' => ['editor', 'periodista'],
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = \App\Models\User::where('email', 'maria@example.com')->firstOrFail();

        $this->assertTrue($user->hasAllRoles(['editor', 'periodista']));
    }

    public function test_register_requires_at_least_one_role(): void
    {
        $this->seed(RolesSeeder::class);

        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Maria Torres',
                'email' => 'maria@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['roles']);
    }

    public function test_user_can_log_in_and_view_dashboard(): void
    {
        $this->seed(RolesSeeder::class);

        $user = \App\Models\User::factory()->create([
            'email' => 'editor@example.com',
            'password' => 'Password123!',
        ]);
        $user->assignRole('editor');

        $response = $this->post(route('login.store'), [
            'email' => 'editor@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Editor');
    }
}
