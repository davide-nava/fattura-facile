<?php

namespace App\Modules\Companies\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];
}
