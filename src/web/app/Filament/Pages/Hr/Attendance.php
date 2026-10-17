<?php

declare(strict_types=1);

namespace App\Filament\Pages\Hr;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class Attendance extends Page
{
    protected static ?string $slug = 'hr/attendance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Human Resources';

    protected string $view = 'filament.pages.hr.attendance';

    public static function getNavigationLabel(): string
    {
        return __('Attendance & Time Tracking');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Attendance & Time Tracking');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAttendanceRecords(): array
    {
        return [
            [
                'employee' => 'Marcella Meier',
                'date' => '2026-03-17',
                'check_in' => '08:15',
                'check_out' => '17:30',
                'hours' => '8.50',
                'type' => 'present',
                'project' => 'ERP Core Architecture',
            ],
            [
                'employee' => 'Jean-Luc Favrod',
                'date' => '2026-03-17',
                'check_in' => '08:45',
                'check_out' => '17:45',
                'hours' => '8.25',
                'type' => 'present',
                'project' => 'Swiss VAT & Tax Filing',
            ],
            [
                'employee' => 'Chiara Bernasconi',
                'date' => '2026-03-17',
                'check_in' => '07:30',
                'check_out' => '16:00',
                'hours' => '8.00',
                'type' => 'present',
                'project' => 'Warehouse Inbound Goods Receipt',
            ],
            [
                'employee' => 'Stefan Künzler',
                'date' => '2026-03-17',
                'check_in' => '-',
                'check_out' => '-',
                'hours' => '0.00',
                'type' => 'vacation',
                'project' => 'Annual Leave (Ferien)',
            ],
            [
                'employee' => 'Sophie Dubois',
                'date' => '2026-03-17',
                'check_in' => '13:00',
                'check_out' => '17:00',
                'hours' => '4.00',
                'type' => 'present',
                'project' => 'Customer B2B Portal Onboarding',
            ],
        ];
    }
}
