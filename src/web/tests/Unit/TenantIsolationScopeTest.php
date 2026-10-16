<?php

namespace Tests\Unit;

use App\Modules\Companies\Models\Company;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_scope_isolation_and_auto_assignment(): void
    {
        $context = app(TenantContext::class);

        $tenant1 = Tenant::create(['name' => 'T1', 'slug' => 't1', 'domain' => 't1.test', 'is_active' => true]);
        $tenant2 = Tenant::create(['name' => 'T2', 'slug' => 't2', 'domain' => 't2.test', 'is_active' => true]);

        // Context 1
        $context->setTenant($tenant1);

        $c1 = Company::create(['name' => 'Comp 1', 'currency' => 'CHF', 'country' => 'CH']);
        $this->assertEquals($tenant1->id, $c1->tenant_id);
        $this->assertEquals($tenant1->id, $c1->tenant->id);

        // Context 2
        $context->setTenant($tenant2);
        $c2 = Company::create(['name' => 'Comp 2', 'currency' => 'EUR', 'country' => 'DE']);
        $this->assertEquals($tenant2->id, $c2->tenant_id);

        // Under tenant 2, should only see c2
        $this->assertCount(1, Company::all());
        $this->assertEquals('Comp 2', Company::first()->name);

        // Under tenant 1, should only see c1
        $context->setTenant($tenant1);
        $this->assertCount(1, Company::all());
        $this->assertEquals('Comp 1', Company::first()->name);

        // Under executeAsSystem, should see both c1 and c2
        $all = $context->executeAsSystem(fn () => Company::all());
        $this->assertCount(2, $all);

        // Without tenant context in test environment, returns 0 results due to 1 = 0
        $context->reset();
        $this->assertCount(0, Company::all());
    }
}
