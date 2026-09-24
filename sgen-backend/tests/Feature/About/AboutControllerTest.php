<?php

declare(strict_types=1);

namespace Tests\Feature\About;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class AboutControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'about_visitor'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);
    }

    public function test_can_render_about_page_with_inertia(): void
    {
        $response = $this->get('/acerca');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('About/Index')
            ->has('about')
            ->where('about.app_name', 'SGEN-Support')
            ->has('about.technical_cards')
            ->has('about.modules')
            ->has('about.environment')
            ->has('about.versions')
        );
    }
}
