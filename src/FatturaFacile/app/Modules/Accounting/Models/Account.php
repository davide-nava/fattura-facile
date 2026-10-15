<?php

namespace App\Modules\Accounting\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'code', // e.g. 1000, 1020, 1100, 2000, 3000, 4000
        'name',
        'type', // asset, liability, equity, revenue, expense
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }
}
