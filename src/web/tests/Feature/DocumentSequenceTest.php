<?php

namespace Tests\Feature;

use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_sequence_generates_sequential_numbers(): void
    {
        $tenant = Tenant::create(['name' => 'Geneva Watches SA', 'slug' => 'geneva-watches']);
        app(TenantContext::class)->setTenant($tenant);

        $company = Company::create(['name' => 'Geneva HQ', 'currency' => 'CHF']);

        $inv1 = DocumentSequence::getNextNumber($tenant->id, $company->id, 'invoice', 'INV-2026-');
        $inv2 = DocumentSequence::getNextNumber($tenant->id, $company->id, 'invoice', 'INV-2026-');
        $inv3 = DocumentSequence::getNextNumber($tenant->id, $company->id, 'invoice', 'INV-2026-');

        $this->assertEquals('INV-2026-00001', $inv1);
        $this->assertEquals('INV-2026-00002', $inv2);
        $this->assertEquals('INV-2026-00003', $inv3);

        // Quotes sequence should be independent
        $quote1 = DocumentSequence::getNextNumber($tenant->id, $company->id, 'quote', 'QUO-2026-');
        $this->assertEquals('QUO-2026-00001', $quote1);
    }
}
