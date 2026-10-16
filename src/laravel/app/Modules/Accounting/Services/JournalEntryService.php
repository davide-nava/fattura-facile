<?php

namespace App\Modules\Accounting\Services;

use App\Modules\Accounting\Models\AccountingPeriod;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalEntryLine;
use App\Modules\Audit\Services\AuditService;
use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Models\DocumentSequence;
use App\Modules\Tenancy\Services\TenantContext;
use App\Shared\Money\MoneyValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class JournalEntryService
{
    public function __construct(
        private AuditService $auditService,
        private TenantContext $tenantContext
    ) {}

    /**
     * Create a draft journal entry.
     *
     * @param  array<array{account_id: int, debit: string|float, credit: string|float, description?: string}>  $lines
     */
    public function createEntry(
        int $companyId,
        string $entryDate,
        array $lines,
        ?string $reference = null,
        ?string $description = null,
        string $sourceType = 'manual',
        ?int $sourceId = null
    ): JournalEntry {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('A journal entry requires at least two lines for double-entry bookkeeping.');
        }

        return DB::transaction(function () use ($companyId, $entryDate, $lines, $reference, $description, $sourceType, $sourceId) {
            $company = Company::findOrFail($companyId);
            $tenantId = $this->tenantContext->getTenantId() ?? $company->tenant_id;
            $date = Carbon::parse($entryDate);

            // Find accounting period
            $period = AccountingPeriod::where('company_id', $companyId)
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->first();

            if ($period && $period->is_closed) {
                throw new RuntimeException("Accounting period {$period->name} is closed. New entries cannot be posted.");
            }

            $entryNumber = DocumentSequence::getNextNumber($tenantId, $companyId, 'journal_entry', 'JE-'.$date->format('Y').'-');

            $entry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'period_id' => $period?->id,
                'entry_number' => $entryNumber,
                'entry_date' => $date->toDateString(),
                'reference' => $reference,
                'description' => $description,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'is_posted' => false,
            ]);

            foreach ($lines as $line) {
                $debit = number_format((float) ($line['debit'] ?? 0), 2, '.', '');
                $credit = number_format((float) ($line['credit'] ?? 0), 2, '.', '');

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                ]);
            }

            $this->auditService->log('create', $entry, null, $entry->toArray(), $companyId);

            return $entry;
        });
    }

    /**
     * Post journal entry, verifying double-entry equilibrium: sum(debit) == sum(credit).
     */
    public function postEntry(int $journalEntryId): JournalEntry
    {
        return DB::transaction(function () use ($journalEntryId) {
            $entry = JournalEntry::with('lines')->findOrFail($journalEntryId);

            if ($entry->is_posted) {
                throw new RuntimeException("Journal entry {$entry->entry_number} is already posted.");
            }

            if ($entry->lines->count() < 2) {
                throw new RuntimeException('Cannot post entry with fewer than 2 lines.');
            }

            // Verify period is open
            if ($entry->period && $entry->period->is_closed) {
                throw new RuntimeException('Cannot post to closed accounting period.');
            }

            // Calculate total debits and credits with exact decimal math
            $totalDebit = MoneyValue::zero();
            $totalCredit = MoneyValue::zero();

            foreach ($entry->lines as $line) {
                $totalDebit = $totalDebit->add($line->debit);
                $totalCredit = $totalCredit->add($line->credit);
            }

            if (! $totalDebit->equals($totalCredit)) {
                throw new RuntimeException(
                    "Double-entry accounting violation: Debits ({$totalDebit->getAmount()}) do not balance Credits ({$totalCredit->getAmount()}) for entry {$entry->entry_number}."
                );
            }

            if ($totalDebit->isZero()) {
                throw new RuntimeException('Cannot post a journal entry with zero total amounts.');
            }

            $old = $entry->toArray();
            $entry->is_posted = true;
            $entry->posted_at = now();
            $entry->posted_by = Auth::id();
            $entry->save();

            $this->auditService->log('post', $entry, $old, $entry->toArray(), $entry->company_id);

            return $entry;
        });
    }

    /**
     * Create and post an immutable reversal entry for a posted journal entry.
     */
    public function reverseEntry(int $journalEntryId, string $reason): JournalEntry
    {
        return DB::transaction(function () use ($journalEntryId, $reason) {
            $original = JournalEntry::with('lines')->findOrFail($journalEntryId);

            if (! $original->is_posted) {
                throw new RuntimeException('Only posted entries can be reversed.');
            }

            // Build reversed lines: swap debit and credit
            $reversedLines = [];
            foreach ($original->lines as $line) {
                $reversedLines[] = [
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'description' => "Reversal: {$line->description}",
                ];
            }

            $reversal = $this->createEntry(
                $original->company_id,
                Carbon::today()->toDateString(),
                $reversedLines,
                "REV-{$original->entry_number}",
                "Reversal of {$original->entry_number}: {$reason}",
                $original->source_type,
                $original->source_id
            );

            $reversal->reversal_of_id = $original->id;
            $reversal->save();

            return $this->postEntry($reversal->id);
        });
    }
}
