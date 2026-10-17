<?php

declare(strict_types=1);

namespace App\Services\Navigation;

use App\Services\Navigation\Contracts\NavigationManagerInterface;

class NavigationManager implements NavigationManagerInterface
{
    /**
     * Get the full list of top-level navigation items.
     *
     * @return array<int, MenuItem>
     */
    public function getItems(): array
    {
        return [
            // 1. Dashboard
            new MenuItem(
                key: 'menu.dashboard',
                icon: 'fa-solid fa-chart-pie',
                route: 'dashboard',
                url: '/dashboard',
                activePatterns: ['dashboard*', 'home'],
            ),

            // 2. Sales / Vendite
            new MenuItem(
                key: 'menu.sales',
                icon: 'fa-solid fa-file-invoice-dollar',
                url: '/admin/invoices',
                activePatterns: ['admin/invoices*', 'admin/customers*', 'admin/quotes*', 'portal/quotes*', 'portal/invoices*'],
                children: [
                    new MenuItem(
                        key: 'menu.sales_customers',
                        icon: 'fa-solid fa-user-group',
                        route: 'filament.admin.resources.customers.index',
                        url: '/admin/customers',
                        activePatterns: ['admin/customers*'],
                    ),
                    new MenuItem(
                        key: 'menu.sales_quotations',
                        icon: 'fa-solid fa-file-lines',
                        route: 'filament.admin.resources.quotes.index',
                        url: '/admin/quotes',
                        activePatterns: ['admin/quotes*', 'portal/quotes*'],
                    ),
                    new MenuItem(
                        key: 'menu.sales_invoices',
                        icon: 'fa-solid fa-file-invoice',
                        route: 'filament.admin.resources.invoices.index',
                        url: '/admin/invoices',
                        activePatterns: ['admin/invoices*', 'portal/invoices*'],
                    ),
                ],
            ),

            // 3. Purchases / Acquisti
            new MenuItem(
                key: 'menu.purchases',
                icon: 'fa-solid fa-cart-shopping',
                url: '/admin/suppliers',
                activePatterns: ['admin/suppliers*', 'admin/purchase-orders*', 'admin/vendor-bills*'],
                children: [
                    new MenuItem(
                        key: 'menu.purchases_suppliers',
                        icon: 'fa-solid fa-truck',
                        route: 'filament.admin.resources.suppliers.index',
                        url: '/admin/suppliers',
                        activePatterns: ['admin/suppliers*'],
                    ),
                    new MenuItem(
                        key: 'menu.purchases_orders',
                        icon: 'fa-solid fa-clipboard-list',
                        route: 'filament.admin.resources.purchase-orders.index',
                        url: '/admin/purchase-orders',
                        activePatterns: ['admin/purchase-orders*'],
                    ),
                    new MenuItem(
                        key: 'menu.purchases_bills',
                        icon: 'fa-solid fa-receipt',
                        route: 'filament.admin.resources.vendor-bills.index',
                        url: '/admin/vendor-bills',
                        activePatterns: ['admin/vendor-bills*'],
                    ),
                ],
            ),

            // 4. Warehouse / Magazzino
            new MenuItem(
                key: 'menu.warehouse',
                icon: 'fa-solid fa-warehouse',
                url: '/admin/products',
                activePatterns: ['admin/products*', 'admin/warehouses*', 'admin/stock-movements*'],
                children: [
                    new MenuItem(
                        key: 'menu.warehouse_products',
                        icon: 'fa-solid fa-box',
                        route: 'filament.admin.resources.products.index',
                        url: '/admin/products',
                        activePatterns: ['admin/products*'],
                    ),
                    new MenuItem(
                        key: 'menu.warehouse_inventory',
                        icon: 'fa-solid fa-boxes-stacked',
                        route: 'filament.admin.resources.warehouses.index',
                        url: '/admin/warehouses',
                        activePatterns: ['admin/warehouses*'],
                    ),
                    new MenuItem(
                        key: 'menu.warehouse_movements',
                        icon: 'fa-solid fa-dolly',
                        route: 'filament.admin.resources.stock-movements.index',
                        url: '/admin/stock-movements',
                        activePatterns: ['admin/stock-movements*'],
                    ),
                ],
            ),

            // 5. Accounting / Contabilità
            new MenuItem(
                key: 'menu.accounting',
                icon: 'fa-solid fa-calculator',
                url: '/admin/journal-entries',
                activePatterns: ['admin/journal-entries*', 'admin/chart-of-accounts*', 'admin/bank-reconciliation*'],
                children: [
                    new MenuItem(
                        key: 'menu.accounting_chart',
                        icon: 'fa-solid fa-book-bookmark',
                        route: 'filament.admin.resources.chart-of-accounts.index',
                        url: '/admin/chart-of-accounts',
                        activePatterns: ['admin/chart-of-accounts*'],
                    ),
                    new MenuItem(
                        key: 'menu.accounting_journal',
                        icon: 'fa-solid fa-book-open',
                        route: 'filament.admin.resources.journal-entries.index',
                        url: '/admin/journal-entries',
                        activePatterns: ['admin/journal-entries*'],
                    ),
                    new MenuItem(
                        key: 'menu.accounting_reconciliation',
                        icon: 'fa-solid fa-building-columns',
                        route: 'filament.admin.pages.bank-reconciliation',
                        url: '/admin/bank-reconciliation',
                        activePatterns: ['admin/bank-reconciliation*'],
                    ),
                ],
            ),

            // 6. Human Resources / Risorse Umane
            new MenuItem(
                key: 'menu.hr',
                icon: 'fa-solid fa-users',
                url: '/admin/hr/employees',
                activePatterns: ['admin/hr*'],
                children: [
                    new MenuItem(
                        key: 'menu.hr_employees',
                        icon: 'fa-solid fa-id-card',
                        route: 'filament.admin.pages.hr.employees',
                        url: '/admin/hr/employees',
                        activePatterns: ['admin/hr/employees*'],
                    ),
                    new MenuItem(
                        key: 'menu.hr_attendance',
                        icon: 'fa-solid fa-calendar-check',
                        route: 'filament.admin.pages.hr.attendance',
                        url: '/admin/hr/attendance',
                        activePatterns: ['admin/hr/attendance*'],
                    ),
                    new MenuItem(
                        key: 'menu.hr_payroll',
                        icon: 'fa-solid fa-money-check-dollar',
                        route: 'filament.admin.pages.hr.payroll',
                        url: '/admin/hr/payroll',
                        activePatterns: ['admin/hr/payroll*'],
                    ),
                ],
            ),

            // 7. Reports / Reportistica
            new MenuItem(
                key: 'menu.reports',
                icon: 'fa-solid fa-chart-line',
                url: '/admin/reports/financial',
                activePatterns: ['admin/reports*'],
                children: [
                    new MenuItem(
                        key: 'menu.reports_financial',
                        icon: 'fa-solid fa-file-invoice-dollar',
                        route: 'filament.admin.pages.reports.financial',
                        url: '/admin/reports/financial',
                        activePatterns: ['admin/reports/financial*'],
                    ),
                    new MenuItem(
                        key: 'menu.reports_sales',
                        icon: 'fa-solid fa-chart-column',
                        route: 'filament.admin.pages.reports.sales',
                        url: '/admin/reports/sales',
                        activePatterns: ['admin/reports/sales*'],
                    ),
                ],
            ),

            // 8. Settings / Impostazioni
            new MenuItem(
                key: 'menu.settings',
                icon: 'fa-solid fa-gear',
                route: 'profile.edit',
                url: '/settings/profile',
                activePatterns: ['settings*'],
                children: [
                    new MenuItem(
                        key: 'menu.settings_company',
                        icon: 'fa-solid fa-building',
                        route: 'company.edit',
                        url: '/settings/company',
                        activePatterns: ['settings/company*'],
                    ),
                    new MenuItem(
                        key: 'menu.settings_users_roles',
                        icon: 'fa-solid fa-user-shield',
                        route: 'security.edit',
                        url: '/settings/security',
                        activePatterns: ['settings/security*'],
                    ),
                    new MenuItem(
                        key: 'menu.settings_general',
                        icon: 'fa-solid fa-sliders',
                        route: 'appearance.edit',
                        url: '/settings/appearance',
                        activePatterns: ['settings/appearance*'],
                    ),
                ],
            ),
        ];
    }
}
