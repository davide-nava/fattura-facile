<?php

namespace Database\Seeders;

use App\Modules\Accounting\Models\Account;
use Illuminate\Database\Seeder;

class SwissChartOfAccountsSeeder extends Seeder
{
    public function run(int $tenantId, int $companyId): void
    {
        $accounts = [
            // Assets (Aktiven)
            ['code' => '1000', 'name' => 'Cash on Hand (Kasse / Caisse)', 'type' => 'asset'],
            ['code' => '1020', 'name' => 'Bank Account CHF (Bank / Banque)', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Trade Receivables (FLL / Debiteurs)', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Merchandise Inventory (Warenvorrat / Stock)', 'type' => 'asset'],

            // Liabilities (Passiven)
            ['code' => '2000', 'name' => 'Trade Payables (VLL / Creanciers)', 'type' => 'liability'],
            ['code' => '2200', 'name' => 'VAT Payable (Geschuldete MWST / TVA due)', 'type' => 'liability'],

            // Equity (Eigenkapital)
            ['code' => '2800', 'name' => 'Share Capital / Owner Equity (Eigenkapital)', 'type' => 'equity'],

            // Revenue (Ertrag)
            ['code' => '3200', 'name' => 'Sales Revenue (Warenertrag / Ventes)', 'type' => 'revenue'],
            ['code' => '3400', 'name' => 'Service Revenue (Dienstleistungsertrag / Services)', 'type' => 'revenue'],

            // Expenses (Aufwand)
            ['code' => '4200', 'name' => 'Cost of Goods Sold (Warenaufwand / Achats)', 'type' => 'expense'],
            ['code' => '5000', 'name' => 'Salaries & Wages (Personalaufwand / Salaires)', 'type' => 'expense'],
            ['code' => '6000', 'name' => 'Rent & Premises (Raumaufwand / Loyer)', 'type' => 'expense'],
            ['code' => '6500', 'name' => 'Administrative Expenses (Verwaltungsaufwand / Administration)', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            Account::create([
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'code' => $acc['code'],
                'name' => $acc['name'],
                'type' => $acc['type'],
                'currency' => 'CHF',
                'is_active' => true,
            ]);
        }
    }
}
