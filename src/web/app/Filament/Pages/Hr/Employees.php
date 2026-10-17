<?php

declare(strict_types=1);

namespace App\Filament\Pages\Hr;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class Employees extends Page
{
    protected static ?string $slug = 'hr/employees';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Human Resources';

    protected string $view = 'filament.pages.hr.employees';

    public static function getNavigationLabel(): string
    {
        return __('Employees');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Employees');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEmployees(): array
    {
        return [
            [
                'id' => 'EMP-001',
                'name' => 'Marcella Meier',
                'role' => 'Lead ERP Architect',
                'department' => 'Engineering',
                'canton' => 'ZH',
                'contract' => 'Permanent (100%)',
                'start_date' => '2023-01-15',
                'status' => 'active',
                'email' => 'm.meier@helvetia-trade.ch',
            ],
            [
                'id' => 'EMP-002',
                'name' => 'Jean-Luc Favrod',
                'role' => 'Senior Financial Controller',
                'department' => 'Finance & Accounting',
                'canton' => 'GE',
                'contract' => 'Permanent (100%)',
                'start_date' => '2023-06-01',
                'status' => 'active',
                'email' => 'jl.favrod@helvetia-trade.ch',
            ],
            [
                'id' => 'EMP-003',
                'name' => 'Chiara Bernasconi',
                'role' => 'Logistics & Warehouse Manager',
                'department' => 'Operations',
                'canton' => 'TI',
                'contract' => 'Permanent (80%)',
                'start_date' => '2024-02-01',
                'status' => 'active',
                'email' => 'c.bernasconi@helvetia-trade.ch',
            ],
            [
                'id' => 'EMP-004',
                'name' => 'Stefan Künzler',
                'role' => 'Sales Director DACH',
                'department' => 'Sales & Business Dev',
                'canton' => 'BE',
                'contract' => 'Permanent (100%)',
                'start_date' => '2024-08-15',
                'status' => 'active',
                'email' => 's.kuenzler@helvetia-trade.ch',
            ],
            [
                'id' => 'EMP-005',
                'name' => 'Sophie Dubois',
                'role' => 'Customer Success Specialist',
                'department' => 'Support',
                'canton' => 'VD',
                'contract' => 'Part-time (50%)',
                'start_date' => '2025-01-10',
                'status' => 'active',
                'email' => 's.dubois@helvetia-trade.ch',
            ],
        ];
    }
}
