<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Navigation\MenuItem;
use Tests\TestCase;

class MenuItemTest extends TestCase
{
    public function test_menu_item_instantiation_and_getters(): void
    {
        $item = new MenuItem(
            key: 'menu.dashboard',
            icon: 'fa-solid fa-chart-pie',
            route: 'dashboard',
            url: '/dashboard',
            activePatterns: ['dashboard*'],
            badge: '5',
            badgeColor: 'teal',
        );

        $this->assertSame('menu.dashboard', $item->key);
        $this->assertSame('fa-solid fa-chart-pie', $item->icon);
        $this->assertSame('dashboard', $item->route);
        $this->assertSame('/dashboard', $item->url);
        $this->assertSame(['dashboard*'], $item->activePatterns);
        $this->assertSame('5', $item->badge);
        $this->assertSame('teal', $item->badgeColor);
        $this->assertFalse($item->hasChildren());
    }

    public function test_menu_item_get_label_translates_correctly(): void
    {
        app()->setLocale('it');
        $item = new MenuItem(key: 'menu.sales', icon: 'fa-solid fa-file-invoice-dollar');
        $this->assertSame('Vendite', $item->getLabel());

        app()->setLocale('en');
        $this->assertSame('Sales', $item->getLabel());

        app()->setLocale('de');
        $this->assertSame('Verkauf', $item->getLabel());

        app()->setLocale('fr');
        $this->assertSame('Ventes', $item->getLabel());
    }

    public function test_menu_item_get_url_resolves_named_route_when_exists(): void
    {
        $item = new MenuItem(
            key: 'menu.dashboard',
            icon: 'fa-solid fa-chart-pie',
            route: 'dashboard',
            url: '/fallback-url',
        );

        $this->assertSame(route('dashboard'), $item->getUrl());
    }

    public function test_menu_item_get_url_falls_back_to_url_when_named_route_missing(): void
    {
        $item = new MenuItem(
            key: 'menu.test',
            icon: 'fa-solid fa-check',
            route: 'non.existent.route.name',
            url: '/fallback-url',
        );

        $this->assertSame(url('/fallback-url'), $item->getUrl());
    }

    public function test_menu_item_get_url_returns_hash_when_no_route_and_no_url(): void
    {
        $item = new MenuItem(
            key: 'menu.test',
            icon: 'fa-solid fa-check',
        );

        $this->assertSame('#', $item->getUrl());
    }

    public function test_menu_item_children_and_active_child_detection(): void
    {
        $child1 = new MenuItem(
            key: 'menu.sales_customers',
            icon: 'fa-solid fa-user-group',
            url: '/admin/customers',
            activePatterns: ['admin/customers*'],
        );

        $child2 = new MenuItem(
            key: 'menu.sales_invoices',
            icon: 'fa-solid fa-file-invoice',
            url: '/admin/invoices',
            activePatterns: ['admin/invoices*'],
        );

        $parent = new MenuItem(
            key: 'menu.sales',
            icon: 'fa-solid fa-file-invoice-dollar',
            children: [$child1, $child2],
        );

        $this->assertTrue($parent->hasChildren());
        $this->assertCount(2, $parent->children);
    }
}
