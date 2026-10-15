<?php

namespace App\Filament\Widgets;

use App\Modules\Companies\Models\Company;
use App\Modules\Reporting\Services\ReportingService;
use App\Modules\Tenancy\Services\TenantContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);
        $tenant = $tenantContext->getTenant();

        if (! $tenant) {
            return [
                Stat::make('Tenant Context', 'No active tenant')
                    ->description('Select or initialize tenant to view live financials')
                    ->color('gray'),
            ];
        }

        $company = Company::where('tenant_id', $tenant->id)->first();
        if (! $company) {
            return [
                Stat::make('Company', 'No legal entity configured')
                    ->color('warning'),
            ];
        }

        /** @var ReportingService $reporting */
        $reporting = app(ReportingService::class);
        $metrics = $reporting->getDashboardMetrics($company->id);

        return [
            Stat::make('Monthly Revenue', $metrics['month_revenue'])
                ->description('Issued invoices this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Trade Receivables', $metrics['total_receivables'])
                ->description("Overdue: {$metrics['overdue_receivables']}")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Trade Payables', $metrics['total_payables'])
                ->description('Supplier invoices due')
                ->color('warning'),

            Stat::make('Stock Valuation', $metrics['stock_valuation'])
                ->description('Current inventory on hand')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
        ];
    }
}
