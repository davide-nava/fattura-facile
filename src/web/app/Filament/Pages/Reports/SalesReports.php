<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SalesReports extends Page
{
    protected static ?string $slug = 'reports/sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Reports & Analytics';

    protected string $view = 'filament.pages.reports.sales-reports';

    public static function getNavigationLabel(): string
    {
        return __('Sales Analytics & Revenue Reports');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Sales Analytics & Revenue Reports');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMonthlyRevenue(): array
    {
        return [
            ['month' => 'October 2025', 'revenue' => '28,450.00', 'invoices' => 12, 'growth' => '+8.4%'],
            ['month' => 'November 2025', 'revenue' => '32,100.00', 'invoices' => 14, 'growth' => '+12.8%'],
            ['month' => 'December 2025', 'revenue' => '45,800.00', 'invoices' => 19, 'growth' => '+42.7%'],
            ['month' => 'January 2026', 'revenue' => '24,200.00', 'invoices' => 10, 'growth' => '-47.2%'],
            ['month' => 'February 2026', 'revenue' => '38,900.00', 'invoices' => 16, 'growth' => '+60.7%'],
            ['month' => 'March 2026 (MTD)', 'revenue' => '48,250.00', 'invoices' => 21, 'growth' => '+24.0%'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopClients(): array
    {
        return [
            [
                'customer' => 'Alpine Solutions Sàrl',
                'canton' => 'GE',
                'invoices_count' => 8,
                'total_spend' => '38,450.00',
                'currency' => 'CHF',
                'payment_performance' => 'On Time (100%)',
            ],
            [
                'customer' => 'Zurich Financial Tech AG',
                'canton' => 'ZH',
                'invoices_count' => 6,
                'total_spend' => '32,100.00',
                'currency' => 'CHF',
                'payment_performance' => 'On Time (100%)',
            ],
            [
                'customer' => 'Ticino Manufacturing Sagl',
                'canton' => 'TI',
                'invoices_count' => 5,
                'total_spend' => '22,400.00',
                'currency' => 'CHF',
                'payment_performance' => 'Average 12 days',
            ],
            [
                'customer' => 'Basel Pharma Logistics AG',
                'canton' => 'BS',
                'invoices_count' => 4,
                'total_spend' => '18,650.00',
                'currency' => 'CHF',
                'payment_performance' => 'On Time (100%)',
            ],
        ];
    }
}
