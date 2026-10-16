<?php

namespace App\Modules\Companies\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'code',
        'street',
        'city',
        'postal_code',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
