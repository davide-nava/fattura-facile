<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Tenancy\Http\Middleware\InitializeTenantContext;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_context_workflow(): void
    {
        $context = new TenantContext;

        $this->assertFalse($context->check());
        $this->assertNull($context->getTenant());
        $this->assertNull($context->getTenantId());
        $this->assertFalse($context->isBypassingIsolation());

        $tenant = Tenant::create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'domain' => 'demo.test',
            'is_active' => true,
        ]);

        $context->setTenant($tenant);

        $this->assertTrue($context->check());
        $this->assertEquals($tenant->id, $context->getTenant()->id);
        $this->assertEquals($tenant->id, $context->getTenantId());
        $this->assertEquals($tenant->id, $context->requireTenant()->id);
        $this->assertEquals($tenant->id, $context->requireTenantId());

        // System execution bypass
        $result = $context->executeAsSystem(function () use ($context) {
            $this->assertTrue($context->isBypassingIsolation());

            return 'executed_privileged';
        });

        $this->assertEquals('executed_privileged', $result);
        $this->assertFalse($context->isBypassingIsolation());

        // Reset
        $context->reset();
        $this->assertFalse($context->check());
        $this->assertNull($context->getTenant());
    }

    public function test_require_tenant_throws_when_null(): void
    {
        $context = new TenantContext;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('valid tenant context is strictly required');
        $context->requireTenant();
    }

    public function test_require_tenant_id_throws_when_null(): void
    {
        $context = new TenantContext;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('valid tenant context is strictly required');
        $context->requireTenantId();
    }

    public function test_middleware_unauthenticated_request(): void
    {
        $middleware = new InitializeTenantContext;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertFalse(app(TenantContext::class)->check());
    }

    public function test_middleware_sets_tenant_from_session_and_persists_session(): void
    {
        $tenant = Tenant::create([
            'name' => 'Session Tenant',
            'slug' => 'session-tenant',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->tenants()->attach($tenant->id, ['role' => 'admin', 'is_active' => true]);

        $this->actingAs($user);

        $middleware = new InitializeTenantContext;
        $request = Request::create('/test', 'GET');
        $session = $this->app['session']->driver('array');
        $session->put('active_tenant_id', $tenant->id);
        $request->setLaravelSession($session);

        $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertTrue(app(TenantContext::class)->check());
        $this->assertEquals($tenant->id, app(TenantContext::class)->getTenantId());
        $this->assertEquals($tenant->id, $session->get('active_tenant_id'));
    }

    public function test_middleware_resets_when_user_has_no_tenants(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $middleware = new InitializeTenantContext;
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertFalse(app(TenantContext::class)->check());
    }
}
