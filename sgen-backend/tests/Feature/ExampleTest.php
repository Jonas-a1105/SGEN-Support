<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_root_redirects_to_login_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_root_redirects_to_dashboard_for_authenticated_users(): void
    {
        $user = User::firstOrCreate(
            ['username' => 'test_root_user'],
            ['password' => bcrypt('password'), 'rol' => 'admin', 'tema' => 'light']
        );

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }
}
