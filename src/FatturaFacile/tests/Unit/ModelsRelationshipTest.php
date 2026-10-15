<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\AccountingPeriod;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalEntryLine;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Companies\Models\Branch;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\Companies\Models\FiscalYear;
use App\Modules\CRM\Models\CrmActivity;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\Customers\Models\Customer;
use App\Modules\Documents\Models\Attachment;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Invoicing\Models\CreditNote;
use App\Modules\Invoicing\Models\CreditNoteItem;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Models\PaymentAllocation;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductCategory;
use App\Modules\Products\Models\TaxRate;
use App\Modules\Purchasing\Models\GoodsReceipt;
use App\Modules\Purchasing\Models\GoodsReceiptItem;
use App\Modules\Purchasing\Models\PurchaseOrder;
use App\Modules\Purchasing\Models\PurchaseOrderItem;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Modules\Sales\Models\DeliveryNote;
use App\Modules\Sales\Models\DeliveryNoteItem;
use App\Modules\Sales\Models\Quote;
use App\Modules\Sales\Models\QuoteItem;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderItem;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ModelsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Company $company;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'domain' => 'test.test',
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Company',
            'currency' => 'CHF',
            'country' => 'CH',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@test.ch',
            'password' => bcrypt('password'),
            'current_tenant_id' => $this->tenant->id,
            'is_superadmin' => false,
        ]);

        app(TenantContext::class)->setTenant($this->tenant);
    }

    public function test_company_and_branches_and_fiscal_years(): void
    {
        $branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Zurich Branch',
            'code' => 'ZH',
            'is_active' => true,
        ]);

        $fiscalYear = FiscalYear::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'FY 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
        ]);

        $this->assertCount(1, $this->company->branches);
        $this->assertCount(1, $this->company->fiscalYears);
        $this->assertEquals($branch->id, $this->company->branches->first()->id);
        $this->assertEquals($fiscalYear->id, $this->company->fiscalYears->first()->id);
    }

    public function test_document_sequence_generation(): void
    {
        $num1 = DocumentSequence::getNextNumber($this->tenant->id, $this->company->id, 'invoice');
        $this->assertEquals('INV-'.date('Y').'-00001', $num1);

        $num2 = DocumentSequence::getNextNumber($this->tenant->id, $this->company->id, 'invoice');
        $this->assertEquals('INV-'.date('Y').'-00002', $num2);

        $this->assertCount(1, $this->company->documentSequences);
    }

    public function test_accounting_models_and_immutability(): void
    {
        $fiscalYear = FiscalYear::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'FY 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
        ]);

        $period = AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'fiscal_year_id' => $fiscalYear->id,
            'period_number' => 1,
            'name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => false,
        ]);

        $accountDebit = Account::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => '1020',
            'name' => 'Bank',
            'type' => 'asset',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        $accountCredit = Account::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => '3000',
            'name' => 'Revenue',
            'type' => 'revenue',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_id' => $period->id,
            'entry_number' => 'JE-0001',
            'entry_date' => '2026-01-15',
            'description' => 'Test Entry',
            'source_type' => 'manual',
            'is_posted' => false,
            'posted_by' => $this->user->id,
        ]);

        $line1 = JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $accountDebit->id,
            'description' => 'Debit Bank',
            'debit' => '100.00',
            'credit' => '0.00',
        ]);

        $line2 = JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $accountCredit->id,
            'description' => 'Credit Revenue',
            'debit' => '0.00',
            'credit' => '100.00',
        ]);

        // Relations check
        $this->assertEquals($fiscalYear->id, $period->fiscalYear->id);
        $this->assertCount(1, $period->journalEntries);
        $this->assertCount(2, $entry->lines);
        $this->assertEquals($this->user->id, $entry->postedBy->id);
        $this->assertCount(1, $accountDebit->lines);
        $this->assertEquals($accountDebit->id, $line1->account->id);
        $this->assertEquals($entry->id, $line1->journalEntry->id);

        // Mark posted
        $entry->update(['is_posted' => true, 'posted_at' => now()]);

        // Attempting to update posted entry throws exception
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('immutable');
        $entry->update(['description' => 'Changed description']);
    }

    public function test_posted_journal_entry_cannot_be_deleted(): void
    {
        $period = AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_number' => 1,
            'name' => 'P1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => false,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_id' => $period->id,
            'entry_number' => 'JE-0002',
            'entry_date' => '2026-01-15',
            'is_posted' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot be deleted');
        $entry->delete();
    }

    public function test_posted_journal_entry_lines_immutable(): void
    {
        $period = AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_number' => 1,
            'name' => 'P1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => false,
        ]);

        $account = Account::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => '1000',
            'name' => 'Cash',
            'type' => 'asset',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_id' => $period->id,
            'entry_number' => 'JE-0003',
            'entry_date' => '2026-01-15',
            'is_posted' => true,
        ]);

        $line = JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => '50.00',
            'credit' => '0.00',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot be modified');
        $line->update(['debit' => '60.00']);
    }

    public function test_posted_journal_entry_lines_cannot_be_deleted(): void
    {
        $period = AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_number' => 1,
            'name' => 'P1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => false,
        ]);

        $account = Account::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => '1000',
            'name' => 'Cash',
            'type' => 'asset',
            'currency' => 'CHF',
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'period_id' => $period->id,
            'entry_number' => 'JE-0004',
            'entry_date' => '2026-01-15',
            'is_posted' => true,
        ]);

        $line = JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => '50.00',
            'credit' => '0.00',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot be deleted');
        $line->delete();
    }

    public function test_crm_models_and_morph_activities(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'title' => 'Big Prospect',
            'contact_name' => 'Bob Smith',
            'assigned_to' => $this->user->id,
        ]);

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'CUST-001',
            'name' => 'Acme Client',
            'is_active' => true,
        ]);

        $opp = Opportunity::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'name' => 'Cloud Migration Deal',
            'stage' => 'proposal',
            'expected_revenue' => '50000.00',
            'probability' => 70,
            'assigned_to' => $this->user->id,
        ]);

        $activity = CrmActivity::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'subject' => 'Call lead',
            'type' => 'call',
            'assigned_to' => $this->user->id,
            'activatable_type' => Lead::class,
            'activatable_id' => $lead->id,
        ]);

        $oppActivity = CrmActivity::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'subject' => 'Opp meeting',
            'type' => 'meeting',
            'assigned_to' => $this->user->id,
            'activatable_type' => Opportunity::class,
            'activatable_id' => $opp->id,
        ]);

        $this->assertEquals($this->user->id, $lead->assignedUser->id);
        $this->assertCount(1, $lead->opportunities);
        $this->assertCount(1, $lead->activities);
        $this->assertCount(1, $opp->activities);
        $this->assertEquals($customer->id, $opp->customer->id);
        $this->assertEquals($lead->id, $opp->lead->id);
        $this->assertEquals($this->user->id, $opp->assignedUser->id);
        $this->assertEquals($this->user->id, $activity->assignedUser->id);
        $this->assertEquals($lead->id, $activity->activatable->id);
    }

    public function test_products_categories_and_stock_balances(): void
    {
        $category = ProductCategory::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Electronics',
            'code' => 'ELEC',
        ]);

        $taxRate = TaxRate::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Standard VAT',
            'code' => 'STD',
            'rate' => '8.10',
            'is_active' => true,
            'is_default' => true,
        ]);

        $warehouse = Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'is_active' => true,
        ]);

        $productManaged = Product::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'category_id' => $category->id,
            'tax_rate_id' => $taxRate->id,
            'name' => 'Laptop Pro',
            'sku' => 'LAP-001',
            'type' => 'product',
            'sales_price' => '1500.00',
            'purchase_price' => '1000.00',
            'is_stock_managed' => true,
            'is_active' => true,
        ]);

        $productService = Product::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'category_id' => $category->id,
            'tax_rate_id' => $taxRate->id,
            'name' => 'Consulting Hour',
            'sku' => 'SRV-001',
            'type' => 'service',
            'sales_price' => '180.00',
            'is_stock_managed' => false,
            'is_active' => true,
        ]);

        $balance = StockBalance::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $productManaged->id,
            'quantity' => '15.0000',
            'avg_unit_cost' => '1000.0000',
        ]);

        $movement = StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $productManaged->id,
            'movement_type' => 'goods_receipt',
            'quantity' => '15.0000',
            'unit_cost' => '1000.00',
            'created_at' => now(),
        ]);

        $this->assertEquals($category->id, $productManaged->category->id);
        $this->assertEquals($taxRate->id, $productManaged->taxRate->id);
        $this->assertCount(2, $category->products);
        $this->assertCount(1, $warehouse->stockBalances);
        $this->assertCount(1, $warehouse->stockMovements);
        $this->assertEquals($warehouse->id, $balance->warehouse->id);
        $this->assertEquals($productManaged->id, $balance->product->id);
        $this->assertEquals($warehouse->id, $movement->warehouse->id);
        $this->assertEquals($productManaged->id, $movement->product->id);
        $this->assertCount(1, $productManaged->stockMovements);

        // totalStock attribute
        $this->assertEquals('15', $productManaged->total_stock);
        $this->assertEquals('0.0000', $productService->total_stock);
    }

    public function test_sales_and_purchasing_and_invoicing_relations(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'CUST-002',
            'name' => 'Beta Client',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'code' => 'SUP-001',
            'name' => 'Alpha Parts',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'Secondary WH',
            'code' => 'WH-2',
            'is_active' => true,
        ]);

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'name' => 'General Item',
            'sku' => 'GEN-01',
            'type' => 'product',
            'sales_price' => '100.00',
            'is_stock_managed' => true,
            'is_active' => true,
        ]);

        // Quote & SalesOrder
        $quote = Quote::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quote_number' => 'QUO-001',
            'quote_date' => '2026-02-01',
            'status' => 'accepted',
            'currency' => 'CHF',
            'total' => '200.00',
        ]);

        $quoteItem = QuoteItem::create([
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'description' => 'Item 1',
            'quantity' => '2',
            'unit_price' => '100.00',
            'total' => '200.00',
        ]);

        $salesOrder = SalesOrder::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'quote_id' => $quote->id,
            'order_number' => 'SO-001',
            'order_date' => '2026-02-05',
            'status' => 'confirmed',
            'currency' => 'CHF',
            'total' => '200.00',
        ]);

        $soItem = SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $product->id,
            'description' => 'Item 1',
            'quantity' => '2',
            'unit_price' => '100.00',
            'total' => '200.00',
        ]);

        $deliveryNote = DeliveryNote::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'sales_order_id' => $salesOrder->id,
            'warehouse_id' => $warehouse->id,
            'delivery_number' => 'DN-001',
            'delivery_date' => '2026-02-06',
            'status' => 'delivered',
        ]);

        $dnItem = DeliveryNoteItem::create([
            'delivery_note_id' => $deliveryNote->id,
            'product_id' => $product->id,
            'quantity' => '2',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $salesOrder->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => '2026-02-07',
            'due_date' => '2026-03-07',
            'status' => 'issued',
            'currency' => 'CHF',
            'total' => '200.00',
            'outstanding_amount' => '200.00',
        ]);

        $invItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => 'Item 1',
            'quantity' => '2',
            'unit_price' => '100.00',
            'total' => '200.00',
        ]);

        $creditNote = CreditNote::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'credit_note_number' => 'CN-001',
            'credit_note_date' => '2026-02-08',
            'status' => 'issued',
            'currency' => 'CHF',
            'total' => '50.00',
        ]);

        $cnItem = CreditNoteItem::create([
            'credit_note_id' => $creditNote->id,
            'description' => 'Discount',
            'quantity' => '1',
            'unit_price' => '50.00',
            'total' => '50.00',
        ]);

        // Purchase Order & Receipts
        $po = PurchaseOrder::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'supplier_id' => $supplier->id,
            'order_number' => 'PO-001',
            'order_date' => '2026-02-01',
            'status' => 'ordered',
            'currency' => 'CHF',
            'total' => '500.00',
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'description' => 'Material',
            'quantity' => '10',
            'unit_price' => '50.00',
            'total' => '500.00',
        ]);

        $gr = GoodsReceipt::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GR-001',
            'receipt_date' => '2026-02-04',
            'status' => 'received',
        ]);

        $grItem = GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'product_id' => $product->id,
            'quantity' => '10',
            'unit_cost' => '50.00',
        ]);

        $suppInv = SupplierInvoice::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'supplier_invoice_number' => 'SUP-INV-99',
            'invoice_date' => '2026-02-05',
            'due_date' => '2026-03-05',
            'status' => 'approved',
            'currency' => 'CHF',
            'total' => '500.00',
        ]);

        // Payment & Allocation
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'payment_number' => 'PAY-001',
            'payment_date' => '2026-02-10',
            'payment_method' => 'bank_transfer',
            'amount' => '150.00',
            'currency' => 'CHF',
        ]);

        $allocation = PaymentAllocation::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'payment_id' => $payment->id,
            'allocatable_type' => Invoice::class,
            'allocatable_id' => $invoice->id,
            'allocated_amount' => '150.00',
        ]);

        // Attachment
        $attachment = Attachment::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'attachable_type' => Invoice::class,
            'attachable_id' => $invoice->id,
            'file_name' => 'doc.pdf',
            'file_path' => '/storage/doc.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        // AuditLog
        $audit = AuditLog::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'action' => 'issue',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        // Assert customer and sales relations
        $this->assertCount(1, $customer->quotes);
        $this->assertCount(1, $customer->salesOrders);
        $this->assertCount(1, $customer->invoices);
        $this->assertEquals($customer->id, $quote->customer->id);
        $this->assertCount(1, $quote->items);
        $this->assertEquals($quote->id, $salesOrder->quote->id);
        $this->assertCount(1, $salesOrder->items);
        $this->assertCount(1, $salesOrder->deliveryNotes);
        $this->assertCount(1, $salesOrder->invoices);
        $this->assertEquals($salesOrder->id, $deliveryNote->salesOrder->id);
        $this->assertEquals($warehouse->id, $deliveryNote->warehouse->id);
        $this->assertCount(1, $deliveryNote->items);
        $this->assertEquals($customer->id, $invoice->customer->id);
        $this->assertEquals($salesOrder->id, $invoice->salesOrder->id);
        $this->assertCount(1, $invoice->items);
        $this->assertCount(1, $invoice->creditNotes);
        $this->assertCount(1, $invoice->paymentAllocations);
        $this->assertEquals($customer->id, $creditNote->customer->id);
        $this->assertEquals($invoice->id, $creditNote->invoice->id);
        $this->assertCount(1, $creditNote->items);

        // Assert supplier and purchasing relations
        $this->assertCount(1, $supplier->purchaseOrders);
        $this->assertCount(1, $supplier->supplierInvoices);
        $this->assertEquals($supplier->id, $po->supplier->id);
        $this->assertCount(1, $po->items);
        $this->assertCount(1, $po->goodsReceipts);
        $this->assertCount(1, $po->supplierInvoices);
        $this->assertEquals($supplier->id, $gr->supplier->id);
        $this->assertEquals($po->id, $gr->purchaseOrder->id);
        $this->assertEquals($warehouse->id, $gr->warehouse->id);
        $this->assertCount(1, $gr->items);
        $this->assertEquals($supplier->id, $suppInv->supplier->id);
        $this->assertEquals($po->id, $suppInv->purchaseOrder->id);

        // Assert item reverse relations
        $this->assertEquals($quote->id, $quoteItem->quote->id);
        $this->assertEquals($product->id, $quoteItem->product->id);
        $this->assertEquals($salesOrder->id, $soItem->salesOrder->id);
        $this->assertEquals($product->id, $soItem->product->id);
        $this->assertEquals($deliveryNote->id, $dnItem->deliveryNote->id);
        $this->assertEquals($product->id, $dnItem->product->id);
        $this->assertEquals($invoice->id, $invItem->invoice->id);
        $this->assertEquals($product->id, $invItem->product->id);
        $this->assertEquals($creditNote->id, $cnItem->creditNote->id);
        $this->assertNull($cnItem->product);
        $this->assertEquals($po->id, $poItem->purchaseOrder->id);
        $this->assertEquals($product->id, $poItem->product->id);
        $this->assertEquals($gr->id, $grItem->goodsReceipt->id);
        $this->assertEquals($product->id, $grItem->product->id);
        $this->assertEquals($customer->id, $salesOrder->customer->id);
        $this->assertCount(0, $suppInv->paymentAllocations);

        // Assert payment and allocations
        $this->assertCount(1, $payment->allocations);
        $this->assertEquals($payment->id, $allocation->payment->id);
        $this->assertEquals($invoice->id, $allocation->allocatable->id);

        // Assert attachment and audit log
        $this->assertEquals($this->user->id, $attachment->user->id);
        $this->assertEquals($invoice->id, $attachment->attachable->id);
        $this->assertEquals($this->user->id, $audit->user->id);
    }

    public function test_tenant_and_membership_relations(): void
    {
        $membership = TenantMembership::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertCount(1, $this->tenant->memberships);
        $this->assertCount(1, $this->tenant->users);
        $this->assertCount(1, $this->tenant->companies);

        $this->assertEquals($this->tenant->id, $membership->tenant->id);
        $this->assertEquals($this->user->id, $membership->user->id);
    }

    public function test_journal_entry_reversal_relation(): void
    {
        $original = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'entry_number' => 'JE-ORIG',
            'entry_date' => '2026-03-01',
            'is_posted' => false,
        ]);

        $reversal = JournalEntry::create([
            'tenant_id' => $this->tenant->id,
            'company_id' => $this->company->id,
            'entry_number' => 'JE-REV',
            'entry_date' => '2026-03-02',
            'reversal_of_id' => $original->id,
            'is_posted' => false,
        ]);

        $this->assertEquals($original->id, $reversal->reversedEntry->id);
    }
}
