<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class BankReconciliation extends Page
{
    protected static ?string $slug = 'bank-reconciliation';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Accounting';

    protected string $view = 'filament.pages.bank-reconciliation';

    public static function getNavigationLabel(): string
    {
        return __('Bank Reconciliation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Bank Reconciliation');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBankAccounts(): array
    {
        return [
            [
                'id' => 'ch-postfinance',
                'name' => 'PostFinance AG (CHF Operating)',
                'iban' => 'CH93 0900 0000 1234 5678 9',
                'currency' => 'CHF',
                'bank_balance' => '142,580.40',
                'ledger_balance' => '142,580.40',
                'difference' => '0.00',
                'status' => 'balanced',
            ],
            [
                'id' => 'ch-ubs',
                'name' => 'UBS Switzerland AG (CHF Treasury)',
                'iban' => 'CH24 0024 0240 9876 5432 1',
                'currency' => 'CHF',
                'bank_balance' => '85,420.00',
                'ledger_balance' => '84,120.00',
                'difference' => '1,300.00',
                'status' => 'pending',
            ],
            [
                'id' => 'eu-raiffeisen',
                'name' => 'Raiffeisen Schweiz (EUR SEPA)',
                'iban' => 'CH56 8080 8008 1122 3344 5',
                'currency' => 'EUR',
                'bank_balance' => '24,150.80',
                'ledger_balance' => '24,150.80',
                'difference' => '0.00',
                'status' => 'balanced',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentTransactions(): array
    {
        return [
            [
                'date' => '2026-03-15',
                'description' => 'Direct Debit SIX Paynet - Customer Settlement Alpine Solutions Sàrl',
                'reference' => 'CAMT.053-20260315-001',
                'amount' => '+4,861.25',
                'matched_entry' => 'JE-2026-0042',
                'status' => 'matched',
            ],
            [
                'date' => '2026-03-14',
                'description' => 'Supplier Outgoing Wire - Swiss Components AG',
                'reference' => 'PAIN.001-20260314-089',
                'amount' => '-15,404.25',
                'matched_entry' => 'JE-2026-0039',
                'status' => 'matched',
            ],
            [
                'date' => '2026-03-12',
                'description' => 'Incoming Customer Payment - Zurich Tech Consulting GmbH',
                'reference' => 'CAMT.054-QR-88910',
                'amount' => '+1,300.00',
                'matched_entry' => null,
                'status' => 'unmatched',
            ],
            [
                'date' => '2026-03-10',
                'description' => 'Bank Service Fees - Monthly Account Fee & ISO 20022 Transmission',
                'reference' => 'FEE-202603-01',
                'amount' => '-45.00',
                'matched_entry' => 'JE-2026-0035',
                'status' => 'matched',
            ],
        ];
    }
}
