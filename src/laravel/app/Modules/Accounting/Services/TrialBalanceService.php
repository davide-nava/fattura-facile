<?php

namespace App\Modules\Accounting\Services;

use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\JournalEntryLine;
use App\Shared\Money\MoneyValue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TrialBalanceService
{
    /**
     * Generate trial balance for a given company up to a specific date.
     *
     * @return array{
     *     accounts: Collection,
     *     total_debit: string,
     *     total_credit: string,
     *     is_balanced: bool
     * }
     */
    public function generate(int $companyId, ?string $asOfDate = null): array
    {
        $date = $asOfDate ? Carbon::parse($asOfDate) : Carbon::today();

        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $reportAccounts = collect();
        $grandTotalDebit = MoneyValue::zero();
        $grandTotalCredit = MoneyValue::zero();

        foreach ($accounts as $account) {
            $lines = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($companyId, $date) {
                    $query->where('company_id', $companyId)
                        ->where('is_posted', true)
                        ->where('entry_date', '<=', $date->toDateString());
                })
                ->get();

            $debitSum = MoneyValue::zero();
            $creditSum = MoneyValue::zero();

            foreach ($lines as $line) {
                $debitSum = $debitSum->add($line->debit);
                $creditSum = $creditSum->add($line->credit);
            }

            // Only include accounts with activity or nonzero balance
            if (! $debitSum->isZero() || ! $creditSum->isZero()) {
                $netDebit = MoneyValue::zero();
                $netCredit = MoneyValue::zero();

                if ($debitSum->greaterThan($creditSum)) {
                    $netDebit = $debitSum->subtract($creditSum);
                } else {
                    $netCredit = $creditSum->subtract($debitSum);
                }

                $grandTotalDebit = $grandTotalDebit->add($netDebit);
                $grandTotalCredit = $grandTotalCredit->add($netCredit);

                $reportAccounts->push([
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $netDebit->getAmount(),
                    'credit' => $netCredit->getAmount(),
                ]);
            }
        }

        return [
            'as_of_date' => $date->toDateString(),
            'accounts' => $reportAccounts,
            'total_debit' => $grandTotalDebit->getAmount(),
            'total_credit' => $grandTotalCredit->getAmount(),
            'is_balanced' => $grandTotalDebit->equals($grandTotalCredit),
        ];
    }
}
