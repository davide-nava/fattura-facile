<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_language_switcher_with_all_supported_languages(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('data-test="language-switcher-it"', false);
        $response->assertSee('data-test="language-switcher-en"', false);
        $response->assertSee('data-test="language-switcher-de"', false);
        $response->assertSee('data-test="language-switcher-fr"', false);
        $response->assertSee('IT');
        $response->assertSee('EN');
        $response->assertSee('DE');
        $response->assertSee('FR');
    }

    public function test_switching_locale_persists_in_session_and_redirects(): void
    {
        foreach (SetLocale::SUPPORTED_LOCALES as $locale) {
            $response = $this->from(route('login'))->get(route('locale.switch', $locale));

            $response->assertRedirect(route('login'));
            $response->assertSessionHas('locale', $locale);
        }
    }

    public function test_switching_to_unsupported_locale_is_ignored(): void
    {
        $response = $this->from(route('login'))->get(route('locale.switch', 'es'));

        $response->assertRedirect(route('login'));
        $this->assertFalse(session()->has('locale') && session('locale') === 'es');
    }

    public function test_locale_query_param_sets_session_and_app_locale(): void
    {
        $response = $this->get('/login?lang=de');

        $response->assertOk();
        $response->assertSessionHas('locale', 'de');
        $this->assertSame('de', App::getLocale());
    }

    public function test_translations_render_correctly_for_each_locale(): void
    {
        // 1. Italian
        $itResponse = $this->withSession(['locale' => 'it'])->get(route('login'));
        $itResponse->assertOk();
        $itResponse->assertSee('Accedi');

        // 2. English
        $enResponse = $this->withSession(['locale' => 'en'])->get(route('login'));
        $enResponse->assertOk();
        $enResponse->assertSee('Log in');

        // 3. German
        $deResponse = $this->withSession(['locale' => 'de'])->get(route('login'));
        $deResponse->assertOk();
        $deResponse->assertSee('Anmelden');

        // 4. French
        $frResponse = $this->withSession(['locale' => 'fr'])->get(route('login'));
        $frResponse->assertOk();
        $frResponse->assertSee('Connexion');
    }

    public function test_all_supported_locales_have_translation_files(): void
    {
        foreach (SetLocale::SUPPORTED_LOCALES as $locale) {
            $jsonFile = lang_path("{$locale}.json");
            $this->assertFileExists($jsonFile, "Missing JSON language file for {$locale}");

            $jsonContent = json_decode((string) file_get_contents($jsonFile), true);
            $this->assertIsArray($jsonContent);
            $this->assertNotEmpty($jsonContent);

            $phpUiFile = lang_path("{$locale}/ui.php");
            $this->assertFileExists($phpUiFile, "Missing ui.php file for {$locale}");
        }
    }

    public function test_english_text_string_keys_rule_is_enforced_and_maps_correctly(): void
    {
        // Verify English key fallback resolution when locale is en
        App::setLocale('en');
        $this->assertSame('Need help? Contact', __('Need help? Contact'));
        $this->assertSame('Management Panel', __('Management Panel'));
        $this->assertSame('Simple invoices, offline and cloud', __('Simple invoices, offline and cloud'));

        // Verify English keys properly map to Italian translations
        App::setLocale('it');
        $this->assertSame('Hai bisogno di supporto? Contatta', __('Need help? Contact'));
        $this->assertSame('Pannello Gestionale', __('Management Panel'));
        $this->assertSame('Fatture semplici, offline e cloud', __('Simple invoices, offline and cloud'));

        // Verify English keys properly map to German translations
        App::setLocale('de');
        $this->assertSame('Brauchen Sie Hilfe? Kontaktieren Sie', __('Need help? Contact'));
        $this->assertSame('Verwaltungs-Panel', __('Management Panel'));
        $this->assertSame('Einfache Rechnungen, offline und in der Cloud', __('Simple invoices, offline and cloud'));

        // Verify English keys properly map to French translations
        App::setLocale('fr');
        $this->assertSame('Besoin d\'aide ? Contactez', __('Need help? Contact'));
        $this->assertSame('Panneau de gestion', __('Management Panel'));
        $this->assertSame('Factures simples, hors ligne et cloud', __('Simple invoices, offline and cloud'));
    }

    public function test_switching_locale_sets_persistent_cookie(): void
    {
        $response = $this->from(route('login'))->get(route('locale.switch', 'it'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('locale', 'it');
        $response->assertPlainCookie('locale', 'it');
    }

    public function test_incoming_request_with_cookie_restores_locale_without_prior_session(): void
    {
        // Simulate a fresh visit or page reload where session is empty but cookie exists
        $response = $this->withUnencryptedCookie('locale', 'de')->get(route('login'));

        $response->assertOk();
        $this->assertSame('de', App::getLocale());
        $response->assertSessionHas('locale', 'de');
        $response->assertSee('Anmelden');
    }

    public function test_switching_locale_for_authenticated_user_updates_user_record_and_cookie(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->get(route('locale.switch', 'fr'));

        $response->assertRedirect(route('dashboard'));
        $response->assertPlainCookie('locale', 'fr');
        $response->assertSessionHas('locale', 'fr');
        $this->assertSame('fr', $user->fresh()->locale);
        $this->assertSame('fr', App::getLocale());
    }

    public function test_authenticated_user_saved_locale_is_restored_when_no_cookie_present(): void
    {
        $user = User::factory()->create(['locale' => 'de']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertSame('de', App::getLocale());
        $response->assertSessionHas('locale', 'de');
        $response->assertPlainCookie('locale', 'de');
    }

    public function test_post_language_updates_locale_and_returns_json_with_cookie(): void
    {
        $response = $this->postJson(route('locale.update'), ['locale' => 'it']);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'locale' => 'it',
        ]);
        $response->assertPlainCookie('locale', 'it');
        $this->assertSame('it', App::getLocale());
    }

    public function test_post_api_v1_locale_updates_locale_and_returns_json_with_cookie(): void
    {
        $response = $this->postJson(route('api.locale.update'), ['locale' => 'fr']);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'locale' => 'fr',
        ]);
        $response->assertPlainCookie('locale', 'fr');
        $this->assertSame('fr', App::getLocale());
    }

    public function test_post_language_with_invalid_locale_fails_validation(): void
    {
        $response = $this->postJson(route('locale.update'), ['locale' => 'invalid_lang']);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['locale']);
    }

    public function test_invalid_or_malicious_cookie_falls_back_safely(): void
    {
        $response = $this->withUnencryptedCookie('locale', '<script>alert(1)</script>')->get(route('login'));

        $response->assertOk();
        $this->assertContains(App::getLocale(), SetLocale::SUPPORTED_LOCALES);
    }

    public function test_accept_language_header_is_honored_as_fallback(): void
    {
        $response = $this->withHeaders(['Accept-Language' => 'fr,fr-FR;q=0.8,en;q=0.5'])->get(route('login'));

        $response->assertOk();
        $this->assertSame('fr', App::getLocale());
    }

    public function test_language_switcher_markup_contains_client_side_click_handlers(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('onclick="window.setAppLocale && window.setAppLocale(\'it\')"', false);
        $response->assertSee('onclick="window.setAppLocale && window.setAppLocale(\'en\')"', false);
        $response->assertSee('onclick="window.setAppLocale && window.setAppLocale(\'de\')"', false);
        $response->assertSee('onclick="window.setAppLocale && window.setAppLocale(\'fr\')"', false);
    }
}
