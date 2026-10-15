<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Security;
use App\Livewire\Settings\TwoFactor\RecoveryCodes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class TwoFactorAndPasskeysTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]);
        Features::passkeys();
    }

    public function test_recovery_codes_loads_and_regenerates(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-1', 'code-2'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecoveryCodes::class);
        $component->assertSet('recoveryCodes', ['code-1', 'code-2']);

        // Test regenerate
        $genAction = Mockery::mock(GenerateNewRecoveryCodes::class);
        $genAction->shouldReceive('__invoke')->once()->with(Mockery::type(User::class))->andReturnUsing(function ($u) {
            $u->forceFill(['two_factor_recovery_codes' => encrypt(json_encode(['code-3', 'code-4']))])->save();
        });

        $this->app->instance(GenerateNewRecoveryCodes::class, $genAction);

        $component->call('regenerateRecoveryCodes', $genAction);
        $component->assertSet('recoveryCodes', ['code-3', 'code-4']);
    }

    public function test_recovery_codes_handles_corrupt_data(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => 'invalid-not-encrypted',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecoveryCodes::class);
        $component->assertHasErrors(['recoveryCodes']);
        $component->assertSet('recoveryCodes', []);
    }

    public function test_security_passkey_delete_workflow(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $passkey = $user->passkeys()->create([
            'name' => 'MacBook TouchID',
            'credential_id' => 'cred_123',
            'credential' => 'cred_data_blob',
        ]);

        $component = Livewire::test(Security::class);
        $component->call('confirmDelete', $passkey->id);
        $component->assertSet('showDeleteModal', true);
        $component->assertSet('deletingPasskeyId', $passkey->id);
        $component->assertSet('deletingPasskeyName', 'MacBook TouchID');

        // Close modal without deleting
        $component->call('closeDeleteModal');
        $component->assertSet('showDeleteModal', false);
        $this->assertNull($component->get('deletingPasskeyId'));

        // Delete passkey when id is null (returns early)
        $deleteAction = Mockery::mock(DeletePasskey::class);
        $this->app->instance(DeletePasskey::class, $deleteAction);
        $component->call('deletePasskey');

        // Delete passkey properly
        $component->call('confirmDelete', $passkey->id);
        $deleteAction->shouldReceive('__invoke')->once();
        $component->call('deletePasskey');
        $component->assertSet('showDeleteModal', false);
    }

    public function test_security_two_factor_workflow_and_modal_config(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('SECRET123'),
        ]);
        $this->actingAs($user);

        $component = Livewire::test(Security::class);

        // Default modal config when enabling
        $config = $component->get('modalConfig');
        $this->assertEquals('Enable two-factor authentication', $config['title']);

        // Enable 2FA
        $enableAction = Mockery::mock(EnableTwoFactorAuthentication::class);
        $enableAction->shouldReceive('__invoke')->once()->andReturnUsing(function ($u) {
            $u->forceFill(['two_factor_secret' => encrypt('SECRET123')])->save();
        });
        $component->call('enable', $enableAction);
        $component->assertSet('showModal', true);
        $component->assertSet('manualSetupKey', 'SECRET123');

        // Show verification
        $component->call('showVerificationIfNecessary');
        $component->assertSet('showVerificationStep', true);
        $configVerify = $component->get('modalConfig');
        $this->assertEquals('Verify authentication code', $configVerify['title']);

        // Reset verification
        $component->set('code', '123456');
        $component->call('resetVerification');
        $component->assertSet('code', '');
        $component->assertSet('showVerificationStep', false);

        // Confirm 2FA
        $confirmAction = Mockery::mock(ConfirmTwoFactorAuthentication::class);
        $confirmAction->shouldReceive('__invoke')->once()->with(Mockery::type(User::class), '654321');
        $component->set('code', '654321');
        $component->call('confirmTwoFactor', $confirmAction);
        $component->assertSet('twoFactorEnabled', true);

        // Modal config when enabled
        $configEnabled = $component->get('modalConfig');
        $this->assertEquals('Two-factor authentication enabled', $configEnabled['title']);

        // Disable 2FA
        $disableAction = Mockery::mock(DisableTwoFactorAuthentication::class);
        $disableAction->shouldReceive('__invoke')->once();
        $component->call('disable', $disableAction);
        $component->assertSet('twoFactorEnabled', false);

        // Close modal
        $component->call('closeModal');
        $component->assertSet('showModal', false);
    }

    public function test_security_two_factor_without_confirmation_requirement(): void
    {
        Features::twoFactorAuthentication([
            'confirm' => false,
            'confirmPassword' => false,
        ]);

        $user = User::factory()->create([
            'two_factor_secret' => encrypt('SECRET_NO_CONFIRM'),
        ]);
        $this->actingAs($user);

        $component = Livewire::test(Security::class);
        $component->assertSet('requiresConfirmation', false);

        // enable without confirmation runs line 187
        $enableAction = Mockery::mock(EnableTwoFactorAuthentication::class);
        $enableAction->shouldReceive('__invoke')->once();
        $component->call('enable', $enableAction);

        // showVerificationIfNecessary calls closeModal when confirmation not required
        $component->call('showVerificationIfNecessary');
        $component->assertSet('showModal', false);
    }

    public function test_security_load_setup_data_exception_handling(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(Security::class);

        $enableAction = Mockery::mock(EnableTwoFactorAuthentication::class);
        $enableAction->shouldReceive('__invoke')->once()->andReturnUsing(function ($u) {
            $u->forceFill(['two_factor_secret' => 'unencrypted_invalid_payload'])->save();
        });

        $component->call('enable', $enableAction);
        $component->assertHasErrors(['setupData']);
        $this->assertEquals('', $component->get('qrCodeSvg'));
        $this->assertEquals('', $component->get('manualSetupKey'));
    }
}
