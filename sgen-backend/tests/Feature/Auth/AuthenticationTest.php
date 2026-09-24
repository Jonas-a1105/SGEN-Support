<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        User::firstOrCreate(
            ['username' => 'auth_tester'],
            [
                'password' => Hash::make('password123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
        );
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'username' => 'auth_tester',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/inventario');
    }

    public function test_users_can_authenticate_with_remember_me(): void
    {
        $response = $this->post('/login', [
            'username' => 'auth_tester',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/inventario');
        $this->assertNotNull(User::where('username', 'auth_tester')->first()->remember_token);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'auth_tester',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('username', 'auth_tester')->firstOrFail();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/inventario');

        $response->assertRedirect('/login');
    }
}
