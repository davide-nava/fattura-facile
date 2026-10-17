<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationLocalizationAndThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_views_and_css_files_exist(): void
    {
        $this->assertFileExists(resource_path('views/vendor/notifications/email.blade.php'));
        $this->assertFileExists(resource_path('views/vendor/notifications/css/email.css'));
        $this->assertFileExists(resource_path('views/emails/verify-email.blade.php'));
        $this->assertFileExists(public_path('images/logo.png'));
    }

    public function test_email_css_contains_dark_and_light_mode_color_schemes(): void
    {
        $css = file_get_contents(resource_path('views/vendor/notifications/css/email.css'));

        $this->assertStringContainsString('@media (prefers-color-scheme: dark)', $css);
        $this->assertStringContainsString('#121212', $css);
        $this->assertStringContainsString('#1e1e1e', $css);
        $this->assertStringContainsString('#e0e0e0', $css);
        $this->assertStringContainsString('#0d9488', $css);
    }

    public function test_mail_layout_includes_color_scheme_meta_and_dark_mode_styles(): void
    {
        $layout = file_get_contents(resource_path('views/vendor/mail/html/layout.blade.php'));

        $this->assertStringContainsString('name="color-scheme" content="light dark"', $layout);
        $this->assertStringContainsString('@media (prefers-color-scheme: dark)', $layout);
        $this->assertStringContainsString('#121212', $layout);
        $this->assertStringContainsString('#1e1e1e', $layout);
    }

    public function test_email_notification_header_renders_brand_logo_and_layout(): void
    {
        $message = (new MailMessage)
            ->line('Testing brand notification');

        $rendered = $message->render();

        $this->assertStringContainsString('https://fatturafacile.eu/img/logo-email.png', $rendered);
        $this->assertStringContainsString('Fattura', $rendered);
        $this->assertStringContainsString('Facile', $rendered);
        $this->assertStringContainsString(__('Fatture semplici, offline e cloud'), $rendered);
    }

    public function test_user_model_implements_has_locale_preference(): void
    {
        $user = new User(['name' => 'Test User', 'email' => 'test@example.com', 'locale' => 'de']);

        $this->assertInstanceOf(HasLocalePreference::class, $user);
        $this->assertSame('de', $user->preferredLocale());
    }

    public function test_customer_portal_user_model_implements_has_locale_preference(): void
    {
        $portalUser = new CustomerPortalUser(['name' => 'Portal User', 'email' => 'portal@example.com', 'locale' => 'fr']);

        $this->assertInstanceOf(HasLocalePreference::class, $portalUser);
        $this->assertSame('fr', $portalUser->preferredLocale());
    }

    public function test_user_preferred_locale_supports_all_languages_with_fallback(): void
    {
        $locales = ['it', 'en', 'de', 'fr'];

        foreach ($locales as $locale) {
            $user = new User(['locale' => $locale]);
            $this->assertSame($locale, $user->preferredLocale());
        }

        $fallbackUser = new User(['locale' => 'invalid_locale']);
        $this->assertSame(config('app.locale', 'en'), $fallbackUser->preferredLocale());
    }

    public function test_verify_email_notification_renders_in_users_preferred_language_it(): void
    {
        $user = User::factory()->create(['locale' => 'it']);

        $notification = new VerifyEmail;
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertSame('Verifica il tuo indirizzo email', $mail->subject);
        $this->assertStringContainsString('Fai clic sul pulsante qui sotto per verificare il tuo indirizzo email.', $rendered);
        $this->assertStringContainsString('Verifica indirizzo email', $rendered);
        $this->assertStringContainsString('Se non hai creato un account, non è richiesta alcuna ulteriore azione.', $rendered);
        $this->assertStringContainsString('Cordiali saluti,', $rendered);
    }

    public function test_verify_email_notification_renders_in_users_preferred_language_en(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $notification = new VerifyEmail;
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertSame('Verify your email address', $mail->subject);
        $this->assertStringContainsString('Please click the button below to verify your email address.', $rendered);
        $this->assertStringContainsString('Verify Email Address', $rendered);
        $this->assertStringContainsString('If you did not create an account, no further action is required.', $rendered);
        $this->assertStringContainsString('Regards,', $rendered);
    }

    public function test_verify_email_notification_renders_in_users_preferred_language_de(): void
    {
        $user = User::factory()->create(['locale' => 'de']);

        $notification = new VerifyEmail;
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertSame('Bestätigen Sie Ihre E-Mail-Adresse', $mail->subject);
        $this->assertStringContainsString('Bitte klicken Sie auf die Schaltfläche unten, um Ihre E-Mail-Adresse zu bestätigen.', $rendered);
        $this->assertStringContainsString('E-Mail-Adresse bestätigen', $rendered);
        $this->assertStringContainsString('Wenn Sie kein Konto erstellt haben, sind keine weiteren Maßnahmen erforderlich.', $rendered);
        $this->assertStringContainsString('Mit freundlichen Grüßen,', $rendered);
    }

    public function test_verify_email_notification_renders_in_users_preferred_language_fr(): void
    {
        $user = User::factory()->create(['locale' => 'fr']);

        $notification = new VerifyEmail;
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertSame('Vérifiez votre adresse e-mail', $mail->subject);
        $this->assertStringContainsString('Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail.', $rendered);
        $this->assertStringContainsString('Vérifier l\'adresse e-mail', $rendered);
        $this->assertStringContainsString('Si vous n\'avez pas créé de compte, aucune action supplémentaire n\'est requise.', $rendered);
        $this->assertStringContainsString('Cordialement,', $rendered);
    }

    public function test_notification_delivery_honors_has_locale_preference(): void
    {
        Notification::fake();

        $user = User::factory()->create(['locale' => 'de']);
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            $this->assertSame('Bestätigen Sie Ihre E-Mail-Adresse', $mail->subject);

            return true;
        });
    }

    public function test_authenticated_user_language_switcher_updates_saved_locale(): void
    {
        $user = User::factory()->create(['locale' => 'it']);

        $response = $this->actingAs($user)->get(route('locale.switch', 'de'));

        $response->assertRedirect();
        $this->assertSame('de', $user->fresh()->locale);
        $this->assertSame('de', session('locale'));
    }

    public function test_reset_password_notification_uses_custom_email_notification_view_with_logo(): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        $notification = new ResetPassword('test-token');
        $mail = $notification->toMail($user);
        $rendered = $mail->render();

        $this->assertStringContainsString('https://fatturafacile.eu/img/logo-email.png', $rendered);
        $this->assertStringContainsString('Fattura', $rendered);
        $this->assertStringContainsString('Facile', $rendered);
    }
}
