<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\CrmContacts\Commands\EnableCustomerPortalAccessCommand;
use App\Modules\CrmContacts\Handlers\EnableCustomerPortalAccessHandler;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Sales\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected Customer $customer;

    protected CustomerPortalUser $portalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Portal Test Tenant',
            'slug' => 'portal-test-tenant',
            'domain' => 'portal-test.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Portal Test AG',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'C-PORTAL-01',
            'name' => 'Swisscom AG',
            'currency' => 'CHF',
            'phone' => '+41 58 221 9911',
            'street' => 'Alte Tiefenaustrasse 6',
            'city' => 'Bern',
            'postal_code' => '3048',
            'is_active' => true,
        ]);

        app(TenantContext::class)->setTenant($this->tenant);

        $handler = app(EnableCustomerPortalAccessHandler::class);
        $this->portalUser = $handler->handle(new EnableCustomerPortalAccessCommand(
            customerId: $this->customer->id,
            email: 'portal.admin@swisscom.com',
            password: 'CorrectPassword123!',
            name: 'Christoph Aeschlimann'
        ));
    }

    public function test_guest_portal_views_and_authentication(): void
    {
        // 1. Show login form
        $response = $this->get('/portal/login');
        $response->assertStatus(200);
        $response->assertSee('Sign in to Client Portal');

        // 2. Login with wrong password
        $failResponse = $this->post('/portal/login', [
            'email' => 'portal.admin@swisscom.com',
            'password' => 'WrongPassword',
        ]);
        $failResponse->assertSessionHasErrors('email');

        // 3. Login with suspended user
        $this->portalUser->update(['status' => 'suspended']);
        $suspendedResponse = $this->post('/portal/login', [
            'email' => 'portal.admin@swisscom.com',
            'password' => 'CorrectPassword123!',
        ]);
        $suspendedResponse->assertSessionHasErrors('email');
        $this->portalUser->update(['status' => 'active']);

        // 4. Successful login
        $loginResponse = $this->post('/portal/login', [
            'email' => 'portal.admin@swisscom.com',
            'password' => 'CorrectPassword123!',
        ]);
        $loginResponse->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($this->portalUser, 'customer_portal');
    }

    public function test_authenticated_portal_dashboard_invoices_and_quotes(): void
    {
        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-PORTAL-01',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'issued',
            'currency' => 'CHF',
            'total' => '4200.00',
            'outstanding_amount' => '4200.00',
        ]);

        Quote::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'quote_number' => 'QUO-PORTAL-01',
            'quote_date' => now(),
            'status' => 'sent',
            'currency' => 'CHF',
            'total' => '4200.00',
        ]);

        // Dashboard
        $dashResponse = $this->actingAs($this->portalUser, 'customer_portal')
            ->get('/portal');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Swisscom AG');
        $dashResponse->assertSee('4,200.00');

        // Invoices list
        $invoicesResponse = $this->actingAs($this->portalUser, 'customer_portal')
            ->get('/portal/invoices');
        $invoicesResponse->assertStatus(200);
        $invoicesResponse->assertSee('INV-PORTAL-01');

        // Quotes list
        $quotesResponse = $this->actingAs($this->portalUser, 'customer_portal')
            ->get('/portal/quotes');
        $quotesResponse->assertStatus(200);
        $quotesResponse->assertSee('QUO-PORTAL-01');

        // Profile view
        $profileResponse = $this->actingAs($this->portalUser, 'customer_portal')
            ->get('/portal/profile');
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('Alte Tiefenaustrasse 6');

        // Profile update
        $updateResponse = $this->actingAs($this->portalUser, 'customer_portal')
            ->put('/portal/profile', [
                'name' => 'Christoph Updated',
                'phone' => '+41 58 999 0000',
                'street' => 'New Headquarters 10',
                'city' => 'Bern',
                'postal_code' => '3000',
            ]);
        $updateResponse->assertRedirect(route('portal.profile'));
        $this->assertEquals('New Headquarters 10', $this->customer->fresh()->street);

        // Logout
        $logoutResponse = $this->actingAs($this->portalUser, 'customer_portal')
            ->post('/portal/logout');
        $logoutResponse->assertRedirect(route('portal.login'));
        $this->assertGuest('customer_portal');
    }

    public function test_customer_portal_api_flow(): void
    {
        // 1. API Login
        $loginResponse = $this->postJson('/api/v1/portal/auth/login', [
            'email' => 'portal.admin@swisscom.com',
            'password' => 'CorrectPassword123!',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');
        $this->assertNotEmpty($token);

        // 2. API Dashboard with Bearer token
        $dashResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/portal/dashboard');

        $dashResponse->assertStatus(200);
        $this->assertEquals('Swisscom AG', $dashResponse->json('customer.name'));

        // 3. API Invoices with Bearer token
        $invoicesResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/portal/invoices');

        $invoicesResponse->assertStatus(200);

        // 4. API Quotes with Bearer token
        $quotesResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/portal/quotes');

        $quotesResponse->assertStatus(200);

        // 5. API Profile with Bearer token
        $profileResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/portal/profile');

        $profileResponse->assertStatus(200);

        // 6. API Profile Update
        $updateResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/portal/profile', [
                'city' => 'Zurich Oerlikon',
            ]);

        $updateResponse->assertStatus(200);
        $this->assertEquals('Zurich Oerlikon', $this->customer->fresh()->city);

        // 7. API Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/portal/auth/logout');

        $logoutResponse->assertStatus(200);
    }
}
