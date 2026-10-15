<?php

namespace Tests\Feature;

use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\AccountingPeriod;
use App\Modules\Accounting\Services\JournalEntryService;
use App\Modules\Accounting\Services\TrialBalanceService;
use App\Modules\Companies\Models\Company;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DoubleEntryAccountingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Company $company;

    private Account $cashAccount;

    private Account $bankAccount;

    private Account $revenueAccount;

    private Account $arAccount;

    private JournalEntryService $journalService;

    private TrialBalanceService $trialBalanceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Alps Finance AG', 'slug' => 'alps-finance']);
        app(TenantContext::class)->setTenant($this->tenant);

        $this->company = Company::create([
            'name' => 'Alps Finance Holding',
            'currency' => 'CHF',
        ]);

        $this->cashAccount = Account::create([
            'company_id' => $this->company->id,
            'code' => '1000',
            'name' => 'Petty Cash',
            'type' => 'asset',
        ]);

        $this->bankAccount = Account::create([
            'company_id' => $this->company->id,
            'code' => '1020',
            'name' => 'UBS Bank Account CHF',
            'type' => 'asset',
        ]);

        $this->arAccount = Account::create([
            'company_id' => $this->company->id,
            'code' => '1100',
            'name' => 'Accounts Receivable',
            'type' => 'asset',
        ]);

        $this->revenueAccount = Account::create([
            'company_id' => $this->company->id,
            'code' => '3200',
            'name' => 'Consulting Revenue',
            'type' => 'revenue',
        ]);

        $this->journalService = app(JournalEntryService::class);
        $this->trialBalanceService = app(TrialBalanceService::class);
    }

    public function test_balanced_journal_entry_posts_successfully(): void
    {
        // Debit Bank 1500.00, Credit Revenue 1500.00
        $lines = [
            ['account_id' => $this->bankAccount->id, 'debit' => 1500.00, 'credit' => 0.00, 'description' => 'Received payment'],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 1500.00, 'description' => 'Service billed'],
        ];

        $entry = $this->journalService->createEntry(
            $this->company->id,
            '2026-02-15',
            $lines,
            'REC-001',
            'Payment received for invoice'
        );

        $this->assertFalse($entry->is_posted);

        $posted = $this->journalService->postEntry($entry->id);

        $this->assertTrue($posted->is_posted);
        $this->assertNotNull($posted->posted_at);
    }

    public function test_unbalanced_journal_entry_throws_equilibrium_violation(): void
    {
        // Unbalanced: Debit 1000.00, Credit 950.00
        $lines = [
            ['account_id' => $this->bankAccount->id, 'debit' => 1000.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 950.00],
        ];

        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', $lines);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Double-entry accounting violation');

        $this->journalService->postEntry($entry->id);
    }

    public function test_posted_journal_entry_is_immutable(): void
    {
        $lines = [
            ['account_id' => $this->bankAccount->id, 'debit' => 200.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 200.00],
        ];

        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', $lines);
        $this->journalService->postEntry($entry->id);
        $entry->refresh();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('immutable');

        // Attempting to modify posted entry description
        $entry->description = 'Tampered description';
        $entry->save();
    }

    public function test_reversal_entry_swaps_debits_and_credits_accurately(): void
    {
        $lines = [
            ['account_id' => $this->arAccount->id, 'debit' => 750.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 750.00],
        ];

        $original = $this->journalService->createEntry($this->company->id, '2026-02-15', $lines);
        $this->journalService->postEntry($original->id);

        $reversal = $this->journalService->reverseEntry($original->id, 'Duplicate invoice cancellation');

        $this->assertTrue($reversal->is_posted);
        $this->assertEquals($original->id, $reversal->reversal_of_id);

        // Reversal lines must have swapped debits and credits:
        // AR should have Credit 750.00, Revenue should have Debit 750.00
        $arReversalLine = $reversal->lines()->where('account_id', $this->arAccount->id)->first();
        $revReversalLine = $reversal->lines()->where('account_id', $this->revenueAccount->id)->first();

        $this->assertEquals('0.00', $arReversalLine->debit);
        $this->assertEquals('750.00', $arReversalLine->credit);
        $this->assertEquals('750.00', $revReversalLine->debit);
        $this->assertEquals('0.00', $revReversalLine->credit);
    }

    public function test_trial_balance_verifies_general_ledger_equilibrium(): void
    {
        // Entry 1: Capital injection (Bank Dr 10000, Revenue/Equity Cr 10000)
        $this->journalService->postEntry(
            $this->journalService->createEntry($this->company->id, '2026-01-01', [
                ['account_id' => $this->bankAccount->id, 'debit' => 10000.00, 'credit' => 0.00],
                ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 10000.00],
            ])->id
        );

        // Entry 2: Cash withdrawal from bank (Cash Dr 1500, Bank Cr 1500)
        $this->journalService->postEntry(
            $this->journalService->createEntry($this->company->id, '2026-01-05', [
                ['account_id' => $this->cashAccount->id, 'debit' => 1500.00, 'credit' => 0.00],
                ['account_id' => $this->bankAccount->id, 'debit' => 0.00, 'credit' => 1500.00],
            ])->id
        );

        $report = $this->trialBalanceService->generate($this->company->id, '2026-01-31');

        $this->assertTrue($report['is_balanced']);
        $this->assertEquals('10000.00', $report['total_debit']);
        $this->assertEquals('10000.00', $report['total_credit']);

        // Check individual balances
        // Bank: Net Dr 10000 - 1500 = 8500
        // Cash: Net Dr 1500
        // Revenue: Net Cr 10000
        $accounts = collect($report['accounts']);
        $bankRow = $accounts->firstWhere('code', '1020');
        $cashRow = $accounts->firstWhere('code', '1000');
        $revenueRow = $accounts->firstWhere('code', '3200');

        $this->assertEquals('8500.00', $bankRow['debit']);
        $this->assertEquals('1500.00', $cashRow['debit']);
        $this->assertEquals('10000.00', $revenueRow['credit']);
    }

    public function test_posting_to_closed_period_is_denied(): void
    {
        AccountingPeriod::create([
            'company_id' => $this->company->id,
            'period_number' => 1,
            'name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => true, // Closed period
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('closed');

        $this->journalService->createEntry($this->company->id, '2026-01-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 100.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 100.00],
        ]);
    }

    public function test_create_entry_requires_at_least_two_lines(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least two lines');

        $this->journalService->createEntry($this->company->id, '2026-02-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 100.00, 'credit' => 0.00],
        ]);
    }

    public function test_post_already_posted_entry_throws(): void
    {
        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 100.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 100.00],
        ]);

        $this->journalService->postEntry($entry->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already posted');

        $this->journalService->postEntry($entry->id);
    }

    public function test_post_entry_with_fewer_than_two_lines_throws(): void
    {
        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 100.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 100.00],
        ]);

        // Delete one line directly to create invalid entry state
        $entry->lines()->first()->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('fewer than 2 lines');

        $this->journalService->postEntry($entry->id);
    }

    public function test_post_entry_with_zero_amounts_throws(): void
    {
        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 0.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 0.00],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('zero total amounts');

        $this->journalService->postEntry($entry->id);
    }

    public function test_post_entry_when_period_becomes_closed_throws(): void
    {
        $period = AccountingPeriod::create([
            'company_id' => $this->company->id,
            'period_number' => 2,
            'name' => 'February 2026',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
            'is_closed' => false,
        ]);

        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 100.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 100.00],
        ]);

        // Close period after creation but before posting
        $period->update(['is_closed' => true]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('closed accounting period');

        $this->journalService->postEntry($entry->id);
    }

    public function test_reverse_unposted_entry_throws(): void
    {
        $entry = $this->journalService->createEntry($this->company->id, '2026-02-15', [
            ['account_id' => $this->bankAccount->id, 'debit' => 100.00, 'credit' => 0.00],
            ['account_id' => $this->cashAccount->id, 'debit' => 0.00, 'credit' => 100.00],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only posted entries can be reversed');

        $this->journalService->reverseEntry($entry->id, 'Unposted reversal attempt');
    }
}
