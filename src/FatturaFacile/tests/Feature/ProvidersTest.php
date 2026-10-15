<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use ReflectionMethod;
use Tests\TestCase;

class ProvidersTest extends TestCase
{
    use RefreshDatabase;

    public function test_fortify_rate_limiters(): void
    {
        $provider = new FortifyServiceProvider($this->app);
        $provider->boot();

        // 1. Two-factor rate limiter
        $req2fa = Request::create('/two-factor-challenge', 'POST');
        $session = $this->app['session']->driver();
        $session->put('login.id', 42);
        $req2fa->setLaravelSession($session);

        $limit2fa = RateLimiter::limiter('two-factor')($req2fa);
        $this->assertEquals(5, $limit2fa->maxAttempts);

        // 2. Login rate limiter
        $reqLogin = Request::create('/login', 'POST', ['email' => 'User@Test.ch']);
        $reqLogin->server->set('REMOTE_ADDR', '192.168.1.1');
        $limitLogin = RateLimiter::limiter('login')($reqLogin);
        $this->assertEquals(5, $limitLogin->maxAttempts);

        // 3. Passkeys rate limiter with credential
        $reqPasskey = Request::create('/passkey/auth', 'POST', [
            'credential' => ['id' => 'cred_abc'],
        ]);
        $limitPasskey = RateLimiter::limiter('passkeys')($reqPasskey);
        $this->assertEquals(10, $limitPasskey->maxAttempts);

        // 4. Passkeys rate limiter without credential
        $reqPasskeyNoCred = Request::create('/passkey/auth', 'POST');
        $reqPasskeyNoCred->setLaravelSession($session);
        $limitPasskeyNoCred = RateLimiter::limiter('passkeys')($reqPasskeyNoCred);
        $this->assertEquals(10, $limitPasskeyNoCred->maxAttempts);
    }

    public function test_horizon_service_provider_gate(): void
    {
        $provider = new HorizonServiceProvider($this->app);

        $refGate = new ReflectionMethod(HorizonServiceProvider::class, 'gate');
        $refGate->setAccessible(true);
        $user = new User(['email' => 'random@test.ch']);
        $this->assertFalse(Gate::allows('viewHorizon', [$user]));
    }

    public function test_app_service_provider_password_defaults_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $provider = new AppServiceProvider($this->app);

        $refDefaults = new ReflectionMethod(AppServiceProvider::class, 'configureDefaults');
        $refDefaults->setAccessible(true);
        $refDefaults->invoke($provider);

        $defaultPassword = Password::default();
        $this->assertInstanceOf(Password::class, $defaultPassword);
    }
}
