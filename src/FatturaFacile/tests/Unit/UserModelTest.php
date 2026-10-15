<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Tenancy\Models\Tenant;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_initials_computation(): void
    {
        $u1 = new User(['name' => 'John Doe']);
        $this->assertEquals('JD', $u1->initials());

        $u2 = new User(['name' => 'Alice']);
        $this->assertEquals('A', $u2->initials());

        $u3 = new User(['name' => 'First Middle Last']);
        $this->assertEquals('FL', $u3->initials());
    }

    public function test_can_access_panel(): void
    {
        $user = new User(['name' => 'Test User']);
        $panel = new Panel;
        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_user_tenant_relations_and_access(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'domain' => 'acme.test',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'current_tenant_id' => $tenant->id,
            'is_superadmin' => false,
        ]);

        $membership = TenantMembership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => 'editor',
            'is_active' => true,
            'permissions' => ['invoices.view', 'invoices.create'],
        ]);

        // Relations
        $this->assertEquals($tenant->id, $user->currentTenant->id);
        $this->assertCount(1, $user->memberships);
        $this->assertCount(1, $user->tenants);

        // Access checks
        $this->assertTrue($user->hasAccessToTenant($tenant->id));
        $this->assertFalse($user->hasAccessToTenant(9999));

        // Role checks
        $this->assertEquals('editor', $user->getRoleInTenant());
        $this->assertEquals('editor', $user->getRoleInTenant($tenant->id));
        $this->assertNull($user->getRoleInTenant(9999));

        // Permission checks
        $this->assertTrue($user->hasPermissionInTenant('invoices.view'));
        $this->assertTrue($user->hasPermissionInTenant('invoices.create', $tenant->id));
        $this->assertFalse($user->hasPermissionInTenant('invoices.delete'));
        $this->assertFalse($user->hasPermissionInTenant('invoices.view', 9999));

        // Inactive membership
        $membership->update(['is_active' => false]);
        $this->assertFalse($user->hasAccessToTenant($tenant->id));
        $this->assertNull($user->getRoleInTenant());
        $this->assertFalse($user->hasPermissionInTenant('invoices.view'));
    }

    public function test_user_permission_wildcard_and_tenant_admin(): void
    {
        $tenant = Tenant::create([
            'name' => 'Beta Corp',
            'slug' => 'beta-corp',
            'domain' => 'beta.test',
            'is_active' => true,
        ]);

        $userAdmin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'current_tenant_id' => $tenant->id,
            'is_superadmin' => false,
        ]);

        TenantMembership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $userAdmin->id,
            'role' => 'tenant_admin',
            'is_active' => true,
            'permissions' => [],
        ]);

        $this->assertTrue($userAdmin->hasPermissionInTenant('anything'));

        // Wildcard permissions
        $userWildcard = User::create([
            'name' => 'Wildcard User',
            'email' => 'wild@example.com',
            'password' => bcrypt('password'),
            'current_tenant_id' => $tenant->id,
            'is_superadmin' => false,
        ]);

        TenantMembership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $userWildcard->id,
            'role' => 'custom',
            'is_active' => true,
            'permissions' => ['*'],
        ]);

        $this->assertTrue($userWildcard->hasPermissionInTenant('any.permission'));
    }

    public function test_superadmin_bypasses_all_tenant_checks(): void
    {
        $superadmin = User::create([
            'name' => 'Super User',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'current_tenant_id' => null,
            'is_superadmin' => true,
        ]);

        $this->assertTrue($superadmin->hasAccessToTenant(1234));
        $this->assertEquals('superadmin', $superadmin->getRoleInTenant(1234));
        $this->assertTrue($superadmin->hasPermissionInTenant('anything', 1234));
    }

    public function test_no_current_tenant_returns_null_or_false(): void
    {
        $user = User::create([
            'name' => 'Orphan User',
            'email' => 'orphan@example.com',
            'password' => bcrypt('password'),
            'current_tenant_id' => null,
            'is_superadmin' => false,
        ]);

        $this->assertNull($user->getRoleInTenant());
        $this->assertFalse($user->hasPermissionInTenant('some.perm'));
    }
}
