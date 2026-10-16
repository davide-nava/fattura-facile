<?php

namespace App\Modules\Products\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'tax_rates';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'code',
        'rate',
        'is_active',
        'is_default',
        'effective_from',
    ];

    protected $casts = [
        'rate' => 'string',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'effective_from' => 'date',
    ];
}
