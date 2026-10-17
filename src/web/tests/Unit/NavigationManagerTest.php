<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Navigation\Contracts\NavigationManagerInterface;
use App\Services\Navigation\MenuItem;
use App\Services\Navigation\NavigationManager;
use Tests\TestCase;

class NavigationManagerTest extends TestCase
{
    public function test_navigation_manager_implements_interface_and_resolves_from_container(): void
    {
        $manager = $this->app->make(NavigationManagerInterface::class);

        $this->assertInstanceOf(NavigationManager::class, $manager);
        $this->assertInstanceOf(NavigationManagerInterface::class, $manager);
    }

    public function test_navigation_manager_contains_all_standard_erp_modules(): void
    {
        $manager = new NavigationManager;
        $items = $manager->getItems();

        $this->assertCount(8, $items);

        $keys = array_map(fn (MenuItem $item) => $item->key, $items);
        $this->assertSame([
            'menu.dashboard',
            'menu.sales',
            'menu.purchases',
            'menu.warehouse',
            'menu.accounting',
            'menu.hr',
            'menu.reports',
            'menu.settings',
        ], $keys);

        // Verify Dashboard item
        $this->assertSame('fa-solid fa-chart-pie', $items[0]->icon);
        $this->assertFalse($items[0]->hasChildren());

        // Verify Sales and its sub-items
        $this->assertSame('fa-solid fa-file-invoice-dollar', $items[1]->icon);
        $this->assertCount(3, $items[1]->children);
        $this->assertSame('menu.sales_customers', $items[1]->children[0]->key);
        $this->assertSame('fa-solid fa-user-group', $items[1]->children[0]->icon);
        $this->assertSame('menu.sales_quotations', $items[1]->children[1]->key);
        $this->assertSame('fa-solid fa-file-lines', $items[1]->children[1]->icon);
        $this->assertSame('menu.sales_invoices', $items[1]->children[2]->key);
        $this->assertSame('fa-solid fa-file-invoice', $items[1]->children[2]->icon);

        // Verify Purchases and its sub-items
        $this->assertSame('fa-solid fa-cart-shopping', $items[2]->icon);
        $this->assertCount(3, $items[2]->children);
        $this->assertSame('menu.purchases_suppliers', $items[2]->children[0]->key);
        $this->assertSame('fa-solid fa-truck', $items[2]->children[0]->icon);
        $this->assertSame('menu.purchases_orders', $items[2]->children[1]->key);
        $this->assertSame('fa-solid fa-clipboard-list', $items[2]->children[1]->icon);
        $this->assertSame('menu.purchases_bills', $items[2]->children[2]->key);
        $this->assertSame('fa-solid fa-receipt', $items[2]->children[2]->icon);

        // Verify Warehouse and its sub-items
        $this->assertSame('fa-solid fa-warehouse', $items[3]->icon);
        $this->assertCount(3, $items[3]->children);
        $this->assertSame('menu.warehouse_products', $items[3]->children[0]->key);
        $this->assertSame('fa-solid fa-box', $items[3]->children[0]->icon);
        $this->assertSame('menu.warehouse_inventory', $items[3]->children[1]->key);
        $this->assertSame('fa-solid fa-boxes-stacked', $items[3]->children[1]->icon);
        $this->assertSame('menu.warehouse_movements', $items[3]->children[2]->key);
        $this->assertSame('fa-solid fa-dolly', $items[3]->children[2]->icon);

        // Verify Accounting and its sub-items
        $this->assertSame('fa-solid fa-calculator', $items[4]->icon);
        $this->assertCount(3, $items[4]->children);
        $this->assertSame('menu.accounting_chart', $items[4]->children[0]->key);
        $this->assertSame('fa-solid fa-book-bookmark', $items[4]->children[0]->icon);
        $this->assertSame('menu.accounting_journal', $items[4]->children[1]->key);
        $this->assertSame('fa-solid fa-book-open', $items[4]->children[1]->icon);
        $this->assertSame('menu.accounting_reconciliation', $items[4]->children[2]->key);
        $this->assertSame('fa-solid fa-building-columns', $items[4]->children[2]->icon);

        // Verify HR and its sub-items
        $this->assertSame('fa-solid fa-users', $items[5]->icon);
        $this->assertCount(3, $items[5]->children);
        $this->assertSame('menu.hr_employees', $items[5]->children[0]->key);
        $this->assertSame('fa-solid fa-id-card', $items[5]->children[0]->icon);
        $this->assertSame('menu.hr_attendance', $items[5]->children[1]->key);
        $this->assertSame('fa-solid fa-calendar-check', $items[5]->children[1]->icon);
        $this->assertSame('menu.hr_payroll', $items[5]->children[2]->key);
        $this->assertSame('fa-solid fa-money-check-dollar', $items[5]->children[2]->icon);

        // Verify Reports and its sub-items
        $this->assertSame('fa-solid fa-chart-line', $items[6]->icon);
        $this->assertCount(2, $items[6]->children);
        $this->assertSame('menu.reports_financial', $items[6]->children[0]->key);
        $this->assertSame('fa-solid fa-file-invoice-dollar', $items[6]->children[0]->icon);
        $this->assertSame('menu.reports_sales', $items[6]->children[1]->key);
        $this->assertSame('fa-solid fa-chart-column', $items[6]->children[1]->icon);

        // Verify Settings and its sub-items
        $this->assertSame('fa-solid fa-gear', $items[7]->icon);
        $this->assertCount(3, $items[7]->children);
        $this->assertSame('menu.settings_company', $items[7]->children[0]->key);
        $this->assertSame('fa-solid fa-building', $items[7]->children[0]->icon);
        $this->assertSame('menu.settings_users_roles', $items[7]->children[1]->key);
        $this->assertSame('fa-solid fa-user-shield', $items[7]->children[1]->icon);
        $this->assertSame('menu.settings_general', $items[7]->children[2]->key);
        $this->assertSame('fa-solid fa-sliders', $items[7]->children[2]->icon);
    }

    public function test_all_menu_items_have_valid_translations_in_all_four_languages(): void
    {
        $manager = new NavigationManager;
        $items = $manager->getItems();

        $locales = ['it', 'en', 'de', 'fr'];

        foreach ($locales as $locale) {
            app()->setLocale($locale);

            foreach ($items as $item) {
                $translated = $item->getLabel();
                $this->assertNotEmpty($translated, "Missing translation for {$item->key} in {$locale}");
                $this->assertNotSame($item->key, $translated, "Translation key returned unparsed for {$item->key} in {$locale}");

                foreach ($item->children as $child) {
                    $childTranslated = $child->getLabel();
                    $this->assertNotEmpty($childTranslated, "Missing translation for {$child->key} in {$locale}");
                    $this->assertNotSame($child->key, $childTranslated, "Translation key returned unparsed for {$child->key} in {$locale}");
                }
            }
        }
    }
}
