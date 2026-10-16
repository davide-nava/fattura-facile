<?php

namespace App\Filament\Widgets;

use App\Modules\Companies\Models\Company;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\Customers\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Tenancy\Services\TenantContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);
        $tenant = $tenantContext->getTenant();

        if (! $tenant) {
            return [
                Stat::make('Tenant Context', 'No active tenant')
                    ->description('Select or initialize tenant to view live CRM statistics')
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

        $activeCustomers = Customer::where('is_active', true)->count();
        $activeSuppliers = Supplier::where('is_active', true)->count();
        $openLeads = Lead::whereNotIn('status', ['converted', 'unqualified'])->count();

        $openOpportunities = Opportunity::whereNotIn('stage', ['closed_won', 'closed_lost'])->get();
        $pipelineTotal = '0.00';
        foreach ($openOpportunities as $opp) {
            $pipelineTotal = bcadd($pipelineTotal, (string) ($opp->expected_revenue ?? '0.00'), 2);
        }

        $currency = $company->currency ?: 'CHF';

        return [
            Stat::make('Active Customers', (string) $activeCustomers)
                ->description('Registered client accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Active Suppliers', (string) $activeSuppliers)
                ->description('Procurement vendors')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make('Open Leads', (string) $openLeads)
                ->description('Pipeline prospects')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),

            Stat::make('Opportunity Pipeline', "{$currency} {$pipelineTotal}")
                ->description('Expected revenue in sales pipeline')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
