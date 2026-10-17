<?php

declare(strict_types=1);

namespace App\Filament\Pages\Hr;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class Payroll extends Page
{
    protected static ?string $slug = 'hr/payroll';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Human Resources';

    protected string $view = 'filament.pages.hr.payroll';

    public static function getNavigationLabel(): string
    {
        return __('Payroll & Compensation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Payroll & Compensation');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPayrollRuns(): array
    {
        return [
            [
                'employee' => 'Marcella Meier',
                'gross' => '9,500.00',
                'ahv_alv' => '608.00',
                'bvg' => '475.00',
                'withholding_tax' => '0.00',
                'net' => '8,417.00',
                'currency' => 'CHF',
                'period' => '2026-03',
                'status' => 'approved',
            ],
            [
                'employee' => 'Jean-Luc Favrod',
                'gross' => '8,200.00',
                'ahv_alv' => '524.80',
                'bvg' => '410.00',
                'withholding_tax' => '0.00',
                'net' => '7,265.20',
                'currency' => 'CHF',
                'period' => '2026-03',
                'status' => 'approved',
            ],
            [
                'employee' => 'Chiara Bernasconi',
                'gross' => '5,400.00',
                'ahv_alv' => '345.60',
                'bvg' => '270.00',
                'withholding_tax' => '0.00',
                'net' => '4,784.40',
                'currency' => 'CHF',
                'period' => '2026-03',
                'status' => 'approved',
            ],
            [
                'employee' => 'Stefan Künzler',
                'gross' => '9,000.00',
                'ahv_alv' => '576.00',
                'bvg' => '450.00',
                'withholding_tax' => '0.00',
                'net' => '7,974.00',
                'currency' => 'CHF',
                'period' => '2026-03',
                'status' => 'approved',
            ],
            [
                'employee' => 'Sophie Dubois',
                'gross' => '3,100.00',
                'ahv_alv' => '198.40',
                'bvg' => '155.00',
                'withholding_tax' => '180.00',
                'net' => '2,566.60',
                'currency' => 'CHF',
                'period' => '2026-03',
                'status' => 'draft',
            ],
        ];
    }
}
