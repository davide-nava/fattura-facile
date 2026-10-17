<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SidebarComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_component_renders_all_erp_modules_and_icons(): void
    {
        $rendered = Blade::render('<x-sidebar />');

        // Main modules and icons
        $this->assertStringContainsString('fa-solid fa-chart-pie', $rendered);
        $this->assertStringContainsString('fa-solid fa-file-invoice-dollar', $rendered);
        $this->assertStringContainsString('fa-solid fa-cart-shopping', $rendered);
        $this->assertStringContainsString('fa-solid fa-warehouse', $rendered);
        $this->assertStringContainsString('fa-solid fa-calculator', $rendered);
        $this->assertStringContainsString('fa-solid fa-users', $rendered);
        $this->assertStringContainsString('fa-solid fa-chart-line', $rendered);
        $this->assertStringContainsString('fa-solid fa-gear', $rendered);

        // Sub-items icons
        $this->assertStringContainsString('fa-solid fa-user-group', $rendered);
        $this->assertStringContainsString('fa-solid fa-file-lines', $rendered);
        $this->assertStringContainsString('fa-solid fa-file-invoice', $rendered);
        $this->assertStringContainsString('fa-solid fa-truck', $rendered);
        $this->assertStringContainsString('fa-solid fa-clipboard-list', $rendered);
        $this->assertStringContainsString('fa-solid fa-receipt', $rendered);
        $this->assertStringContainsString('fa-solid fa-box', $rendered);
        $this->assertStringContainsString('fa-solid fa-boxes-stacked', $rendered);
        $this->assertStringContainsString('fa-solid fa-dolly', $rendered);
        $this->assertStringContainsString('fa-solid fa-book-bookmark', $rendered);
        $this->assertStringContainsString('fa-solid fa-book-open', $rendered);
        $this->assertStringContainsString('fa-solid fa-building-columns', $rendered);
        $this->assertStringContainsString('fa-solid fa-id-card', $rendered);
        $this->assertStringContainsString('fa-solid fa-calendar-check', $rendered);
        $this->assertStringContainsString('fa-solid fa-money-check-dollar', $rendered);
        $this->assertStringContainsString('fa-solid fa-chart-column', $rendered);
        $this->assertStringContainsString('fa-solid fa-building', $rendered);
        $this->assertStringContainsString('fa-solid fa-user-shield', $rendered);
        $this->assertStringContainsString('fa-solid fa-sliders', $rendered);
    }

    public function test_sidebar_renders_multilingual_labels_in_italian(): void
    {
        app()->setLocale('it');
        $rendered = html_entity_decode(Blade::render('<x-sidebar />'));

        $this->assertStringContainsString('Dashboard', $rendered);
        $this->assertStringContainsString('Vendite', $rendered);
        $this->assertStringContainsString('Clienti', $rendered);
        $this->assertStringContainsString('Preventivi', $rendered);
        $this->assertStringContainsString('Fatture', $rendered);
        $this->assertStringContainsString('Acquisti', $rendered);
        $this->assertStringContainsString('Fornitori', $rendered);
        $this->assertStringContainsString('Ordini di Acquisto', $rendered);
        $this->assertStringContainsString('Fatture Fornitori', $rendered);
        $this->assertStringContainsString('Magazzino', $rendered);
        $this->assertStringContainsString('Articoli', $rendered);
        $this->assertStringContainsString('Giacenze', $rendered);
        $this->assertStringContainsString('Movimenti di Magazzino', $rendered);
        $this->assertStringContainsString('Contabilità', $rendered);
        $this->assertStringContainsString('Piano dei Conti', $rendered);
        $this->assertStringContainsString('Prima Nota', $rendered);
        $this->assertStringContainsString('Riconciliazione Bancaria', $rendered);
        $this->assertStringContainsString('Risorse Umane', $rendered);
        $this->assertStringContainsString('Dipendenti', $rendered);
        $this->assertStringContainsString('Presenze', $rendered);
        $this->assertStringContainsString('Stipendi', $rendered);
        $this->assertStringContainsString('Reportistica', $rendered);
        $this->assertStringContainsString('Bilanci e Analisi', $rendered);
        $this->assertStringContainsString('Analisi Vendite', $rendered);
        $this->assertStringContainsString('Impostazioni', $rendered);
        $this->assertStringContainsString('Azienda', $rendered);
        $this->assertStringContainsString('Utenti e Ruoli', $rendered);
        $this->assertStringContainsString('Configurazioni Generali', $rendered);
    }

    public function test_sidebar_renders_multilingual_labels_in_english(): void
    {
        app()->setLocale('en');
        $rendered = html_entity_decode(Blade::render('<x-sidebar />'));

        $this->assertStringContainsString('Dashboard', $rendered);
        $this->assertStringContainsString('Sales', $rendered);
        $this->assertStringContainsString('Customers', $rendered);
        $this->assertStringContainsString('Quotations', $rendered);
        $this->assertStringContainsString('Invoices', $rendered);
        $this->assertStringContainsString('Purchases', $rendered);
        $this->assertStringContainsString('Suppliers', $rendered);
        $this->assertStringContainsString('Purchase Orders', $rendered);
        $this->assertStringContainsString('Vendor Bills', $rendered);
        $this->assertStringContainsString('Warehouse', $rendered);
        $this->assertStringContainsString('Products', $rendered);
        $this->assertStringContainsString('Inventory Status', $rendered);
        $this->assertStringContainsString('Stock Movements', $rendered);
        $this->assertStringContainsString('Accounting', $rendered);
        $this->assertStringContainsString('Chart of Accounts', $rendered);
        $this->assertStringContainsString('Journal Entries', $rendered);
        $this->assertStringContainsString('Bank Reconciliation', $rendered);
        $this->assertStringContainsString('Human Resources', $rendered);
        $this->assertStringContainsString('Employees', $rendered);
        $this->assertStringContainsString('Attendance', $rendered);
        $this->assertStringContainsString('Payroll', $rendered);
        $this->assertStringContainsString('Reports', $rendered);
        $this->assertStringContainsString('Financial Reports', $rendered);
        $this->assertStringContainsString('Sales Reports', $rendered);
        $this->assertStringContainsString('Settings', $rendered);
        $this->assertStringContainsString('Company Profile', $rendered);
        $this->assertStringContainsString('Users & Roles', $rendered);
        $this->assertStringContainsString('General Configurations', $rendered);
    }

    public function test_sidebar_renders_multilingual_labels_in_german(): void
    {
        app()->setLocale('de');
        $rendered = html_entity_decode(Blade::render('<x-sidebar />'));

        $this->assertStringContainsString('Dashboard', $rendered);
        $this->assertStringContainsString('Verkauf', $rendered);
        $this->assertStringContainsString('Kunden', $rendered);
        $this->assertStringContainsString('Angebote', $rendered);
        $this->assertStringContainsString('Rechnungen', $rendered);
        $this->assertStringContainsString('Einkauf', $rendered);
        $this->assertStringContainsString('Lieferanten', $rendered);
        $this->assertStringContainsString('Bestellungen', $rendered);
        $this->assertStringContainsString('Lieferantenrechnungen', $rendered);
        $this->assertStringContainsString('Lager', $rendered);
        $this->assertStringContainsString('Artikel', $rendered);
        $this->assertStringContainsString('Lagerbestände', $rendered);
        $this->assertStringContainsString('Lagerbewegungen', $rendered);
        $this->assertStringContainsString('Buchhaltung', $rendered);
        $this->assertStringContainsString('Kontenplan', $rendered);
        $this->assertStringContainsString('Buchungsjournal', $rendered);
        $this->assertStringContainsString('Bankabstimmung', $rendered);
        $this->assertStringContainsString('Personalwesen', $rendered);
        $this->assertStringContainsString('Mitarbeiter', $rendered);
        $this->assertStringContainsString('Anwesenheit', $rendered);
        $this->assertStringContainsString('Gehaltsabrechnung', $rendered);
        $this->assertStringContainsString('Berichte', $rendered);
        $this->assertStringContainsString('Finanzberichte', $rendered);
        $this->assertStringContainsString('Verkaufsberichte', $rendered);
        $this->assertStringContainsString('Einstellungen', $rendered);
        $this->assertStringContainsString('Unternehmensprofil', $rendered);
        $this->assertStringContainsString('Benutzer & Rollen', $rendered);
        $this->assertStringContainsString('Allgemeine Konfigurationen', $rendered);
    }

    public function test_sidebar_renders_multilingual_labels_in_french(): void
    {
        app()->setLocale('fr');
        $rendered = html_entity_decode(Blade::render('<x-sidebar />'));

        $this->assertStringContainsString('Tableau de bord', $rendered);
        $this->assertStringContainsString('Ventes', $rendered);
        $this->assertStringContainsString('Clients', $rendered);
        $this->assertStringContainsString('Devis', $rendered);
        $this->assertStringContainsString('Factures', $rendered);
        $this->assertStringContainsString('Achats', $rendered);
        $this->assertStringContainsString('Fournisseurs', $rendered);
        $this->assertStringContainsString('Bons de commande', $rendered);
        $this->assertStringContainsString('Factures fournisseurs', $rendered);
        $this->assertStringContainsString('Entrepôt', $rendered);
        $this->assertStringContainsString('Articles', $rendered);
        $this->assertStringContainsString('État des stocks', $rendered);
        $this->assertStringContainsString('Mouvements de stock', $rendered);
        $this->assertStringContainsString('Comptabilité', $rendered);
        $this->assertStringContainsString('Plan comptable', $rendered);
        $this->assertStringContainsString('Journal des écritures', $rendered);
        $this->assertStringContainsString('Rapprochement bancaire', $rendered);
        $this->assertStringContainsString('Ressources Humaines', $rendered);
        $this->assertStringContainsString('Employés', $rendered);
        $this->assertStringContainsString('Présences', $rendered);
        $this->assertStringContainsString('Salaires', $rendered);
        $this->assertStringContainsString('Rapports', $rendered);
        $this->assertStringContainsString('Rapports financiers', $rendered);
        $this->assertStringContainsString('Rapports de ventes', $rendered);
        $this->assertStringContainsString('Paramètres', $rendered);
        $this->assertTrue(
            str_contains($rendered, "Profil de l'entreprise") || str_contains($rendered, 'Profil de l&#039;entreprise'),
            'Expected French Company Profile translation in sidebar'
        );
        $this->assertStringContainsString('Utilisateurs et Rôles', $rendered);
        $this->assertStringContainsString('Configurations générales', $rendered);
    }

    public function test_authenticated_user_accesses_dashboard_with_sidebar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('fa-solid fa-chart-pie', false);
        $response->assertSee('fa-solid fa-file-invoice-dollar', false);
        $response->assertSee('Fattura');
        $response->assertSee('Facile');
    }
}
