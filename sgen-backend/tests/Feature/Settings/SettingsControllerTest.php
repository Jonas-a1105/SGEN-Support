<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class SettingsControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['username' => 'settings_tester'],
            [
                'password' => bcrypt('old_password_123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->user);
    }

    public function test_can_render_settings_index_page_with_inertia(): void
    {
        $response = $this->get('/configuracion');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Settings/Index')
            ->has('settings')
        );
    }

    public function test_can_update_settings(): void
    {
        $payload = [
            'theme' => 'dark',
            'language' => 'es',
            'stroke_width' => '2px',
            'accent_color' => '#2563eb',
        ];

        $response = $this->put('/configuracion', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('usuarios', [
            'id' => $this->user->id,
            'tema' => 'dark',
        ]);
    }

    public function test_can_update_password(): void
    {
        $payload = [
            'current_password' => 'old_password_123',
            'new_password' => 'new_secret_password_456',
            'new_password_confirmation' => 'new_secret_password_456',
        ];

        $response = $this->post('/configuracion/password', $payload);

        $response->assertRedirect();
    }
}
