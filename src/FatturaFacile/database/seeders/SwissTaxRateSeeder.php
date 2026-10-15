<?php

namespace Database\Seeders;

use App\Modules\Products\Models\TaxRate;
use Illuminate\Database\Seeder;

class SwissTaxRateSeeder extends Seeder
{
    public function run(int $tenantId, int $companyId): void
    {
        $rates = [
            [
                'name' => 'Standard Rate (Normalsatz)',
                'code' => 'CHE_STD_81',
                'rate' => '8.1000',
                'is_default' => true,
                'is_active' => true,
                'effective_from' => '2024-01-01',
            ],
            [
                'name' => 'Reduced Rate (Reduzierter Satz)',
                'code' => 'CHE_RED_26',
                'rate' => '2.6000',
                'is_default' => false,
                'is_active' => true,
                'effective_from' => '2024-01-01',
            ],
            [
                'name' => 'Accommodation Rate (Sondersatz Beherbergung)',
                'code' => 'CHE_ACC_38',
                'rate' => '3.8000',
                'is_default' => false,
                'is_active' => true,
                'effective_from' => '2024-01-01',
            ],
            [
                'name' => 'Zero / Exempt Rate (Steuerbefreit)',
                'code' => 'CHE_EXEMPT_00',
                'rate' => '0.0000',
                'is_default' => false,
                'is_active' => true,
                'effective_from' => '2024-01-01',
            ],
        ];

        foreach ($rates as $data) {
            TaxRate::create(array_merge($data, [
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
            ]));
        }
    }
}
