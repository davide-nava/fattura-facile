<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalRouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_settings(): void
    {
        $response = $this->get('/settings');

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_settings_profile(): void
    {
        $response = $this->get('/settings/profile');

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_settings_appearance(): void
    {
        $response = $this->get('/settings/appearance');

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_settings_security(): void
    {
        $response = $this->get('/settings/security');

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_portal_requests_are_redirected_to_portal_login(): void
    {
        $response = $this->get('/portal');

        $response->assertRedirect(route('portal.login'));
    }

    public function test_unauthenticated_api_requests_receive_unauthorized_status(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_public_home_page_is_accessible_without_authentication_and_has_branding(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Fattura');
        $response->assertSee('Facile');
        $response->assertSee('Fatture semplici, offline e cloud');
        $response->assertSee('Accedi');
    }

    public function test_login_page_renders_with_fatturafacile_branding_and_layout(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Fattura');
        $response->assertSee('Facile');
        $response->assertSee('Fatture semplici, offline e cloud');
        $response->assertSee('info@fatturafacile.eu');
    }

    public function test_authenticated_users_can_access_dashboard_with_unified_layout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Fattura');
        $response->assertSee('Facile');
        $response->assertSee('Benvenuto in');
        $response->assertSee('Pannello Gestionale');
    }

    public function test_authenticated_users_can_access_settings_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/profile');

        $response->assertOk();
        $response->assertSee('Fattura');
        $response->assertSee('Facile');
    }
}
