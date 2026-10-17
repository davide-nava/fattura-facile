<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DarkModeThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_with_dark_mode_support_and_theme_toggle(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('dark:bg-slate-950', false);
        $response->assertSee('toggleTheme()', false);
        $response->assertSee('resolveTheme()', false);
        $response->assertSee('theme-changed', false);
    }

    public function test_dashboard_renders_with_dark_mode_and_theme_controls(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('dark:bg-slate-950', false);
        $response->assertSee('dark:bg-slate-900', false);
        $response->assertSee('toggleTheme()', false);
        $response->assertSee('FatturaFacile', false);
    }

    public function test_login_and_register_pages_render_with_dark_mode_and_theme_toggle(): void
    {
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk();
        $loginResponse->assertSee('toggleTheme()', false);
        $loginResponse->assertSee('dark:via-slate-950', false);

        $registerResponse = $this->get(route('register'));
        $registerResponse->assertOk();
        $registerResponse->assertSee('toggleTheme()', false);
        $registerResponse->assertSee('dark:via-slate-950', false);
    }

    public function test_appearance_settings_page_renders_theme_options(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('appearance.edit'));

        $response->assertOk();
        $response->assertSee('Appearance', false);
        $response->assertSee('Light', false);
        $response->assertSee('Dark', false);
        $response->assertSee('System', false);
    }

    public function test_client_portal_login_renders_with_dark_mode_support(): void
    {
        $response = $this->get(route('portal.login'));

        $response->assertOk();
        $response->assertSee('dark:bg-slate-950', false);
        $response->assertSee('dark:bg-slate-900', false);
        $response->assertSee('toggleTheme()', false);
    }
}
