<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    private TenantContext $tenantContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantContext = app(TenantContext::class);
    }

    public function test_tenant_a_cannot_see_tenant_b_companies(): void
    {
        $tenantA = Tenant::create(['name' => 'Acme AG', 'slug' => 'acme-ag']);
        $tenantB = Tenant::create(['name' => 'Helvetia SA', 'slug' => 'helvetia-sa']);

        // Create company for Tenant A in Tenant A context
        $this->tenantContext->setTenant($tenantA);
        $companyA = Company::create([
            'name' => 'Acme Holding',
            'tax_number' => 'CHE-111.111.111',
            'currency' => 'CHF',
            'country' => 'CH',
        ]);

        // Create company for Tenant B in Tenant B context
        $this->tenantContext->setTenant($tenantB);
        $companyB = Company::create([
            'name' => 'Helvetia Logistics',
            'tax_number' => 'CHE-222.222.222',
            'currency' => 'CHF',
            'country' => 'CH',
        ]);

        // Set context back to Tenant A
        $this->tenantContext->setTenant($tenantA);

        $companiesVisibleToA = Company::all();

        $this->assertCount(1, $companiesVisibleToA);
        $this->assertEquals($companyA->id, $companiesVisibleToA->first()->id);
        $this->assertEquals('Acme Holding', $companiesVisibleToA->first()->name);

        // Explicit lookup of Tenant B company by ID must return null under Tenant A context
        $this->assertNull(Company::find($companyB->id));
    }

    public function test_models_automatically_assign_active_tenant_id(): void
    {
        $tenant = Tenant::create(['name' => 'Zurich Tech GmbH', 'slug' => 'zurich-tech']);
        $this->tenantContext->setTenant($tenant);

        $company = Company::create([
            'name' => 'Zurich Tech',
            'currency' => 'CHF',
        ]);

        $this->assertEquals($tenant->id, $company->tenant_id);
    }

    public function test_unauthenticated_or_unscoped_queries_fail_safely(): void
    {
        $tenant = Tenant::create(['name' => 'Swiss Corp', 'slug' => 'swiss-corp']);
        $this->tenantContext->setTenant($tenant);
        Company::create(['name' => 'Swiss Corp AG', 'currency' => 'CHF']);

        // Reset context to null (unauthenticated / missing tenant context)
        $this->tenantContext->reset();

        // Querying without tenant context in non-bypassed mode must return empty (1=0 guard)
        $this->assertCount(0, Company::all());
    }

    public function test_privileged_system_context_can_query_across_tenants_safely(): void
    {
        $tenantA = Tenant::create(['name' => 'Tenant 1', 'slug' => 'tenant-1']);
        $tenantB = Tenant::create(['name' => 'Tenant 2', 'slug' => 'tenant-2']);

        $this->tenantContext->setTenant($tenantA);
        Company::create(['name' => 'Company 1', 'currency' => 'CHF']);

        $this->tenantContext->setTenant($tenantB);
        Company::create(['name' => 'Company 2', 'currency' => 'CHF']);

        $this->tenantContext->reset();

        $totalCount = $this->tenantContext->executeAsSystem(function () {
            return Company::count();
        });

        $this->assertEquals(2, $totalCount);
    }

    public function test_user_membership_controls_tenant_access(): void
    {
        $tenantA = Tenant::create(['name' => 'Tenant Alpha', 'slug' => 'tenant-alpha']);
        $tenantB = Tenant::create(['name' => 'Tenant Beta', 'slug' => 'tenant-beta']);

        $user = User::factory()->create();

        // Assign user only to Tenant Alpha
        TenantMembership::create([
            'tenant_id' => $tenantA->id,
            'user_id' => $user->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        $this->assertTrue($user->hasAccessToTenant($tenantA->id));
        $this->assertFalse($user->hasAccessToTenant($tenantB->id));
        $this->assertEquals('tenant_admin', $user->getRoleInTenant($tenantA->id));
    }
}
