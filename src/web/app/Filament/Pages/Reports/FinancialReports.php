<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class FinancialReports extends Page
{
    protected static ?string $slug = 'reports/financial';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Reports & Analytics';

    protected string $view = 'filament.pages.reports.financial-reports';

    public static function getNavigationLabel(): string
    {
        return __('Financial Reports & Trial Balance');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Financial Reports & Trial Balance');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTrialBalance(): array
    {
        return [
            [
                'code' => '1000',
                'name' => 'Cash on Hand (Kasse CHF)',
                'type' => 'asset',
                'debit' => '2,450.00',
                'credit' => '0.00',
                'balance' => '2,450.00',
            ],
            [
                'code' => '1020',
                'name' => 'Bank Account (PostFinance Operating)',
                'type' => 'asset',
                'debit' => '142,580.40',
                'credit' => '0.00',
                'balance' => '142,580.40',
            ],
            [
                'code' => '1100',
                'name' => 'Trade Receivables (Forderungen L+L)',
                'type' => 'asset',
                'debit' => '48,250.00',
                'credit' => '0.00',
                'balance' => '48,250.00',
            ],
            [
                'code' => '1200',
                'name' => 'Merchandise Inventory (Warenvorrat)',
                'type' => 'asset',
                'debit' => '47,500.00',
                'credit' => '0.00',
                'balance' => '47,500.00',
            ],
            [
                'code' => '2000',
                'name' => 'Trade Payables (Verbindlichkeiten L+L)',
                'type' => 'liability',
                'debit' => '0.00',
                'credit' => '32,150.00',
                'balance' => '32,150.00',
            ],
            [
                'code' => '2200',
                'name' => 'VAT Payable (Geschuldete MWST 8.1%)',
                'type' => 'liability',
                'debit' => '0.00',
                'credit' => '3,908.25',
                'balance' => '3,908.25',
            ],
            [
                'code' => '2800',
                'name' => 'Common Stock Capital (Aktienkapital)',
                'type' => 'equity',
                'debit' => '0.00',
                'credit' => '100,000.00',
                'balance' => '100,000.00',
            ],
            [
                'code' => '3200',
                'name' => 'Product Sales Revenue (Handelserlöse)',
                'type' => 'revenue',
                'debit' => '0.00',
                'credit' => '134,800.00',
                'balance' => '134,800.00',
            ],
            [
                'code' => '4200',
                'name' => 'Cost of Goods Sold (Handelswarenaufwand)',
                'type' => 'expense',
                'debit' => '65,400.00',
                'credit' => '0.00',
                'balance' => '65,400.00',
            ],
            [
                'code' => '5000',
                'name' => 'Personnel Expenses (Personalaufwand)',
                'type' => 'expense',
                'debit' => '38,400.00',
                'credit' => '0.00',
                'balance' => '38,400.00',
            ],
        ];
    }
}
