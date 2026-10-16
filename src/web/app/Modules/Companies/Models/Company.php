<?php

namespace App\Modules\Companies\Models;

use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'tenant_id',
        'name',
        'legal_name',
        'tax_number', // UID e.g. CHE-123.456.789
        'vat_number', // e.g. CHE-123.456.789 MWST
        'currency',   // e.g. CHF
        'country',    // CH
        'street',
        'city',
        'postal_code',
        'state_canton',
        'email',
        'phone',
        'website',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'company_id');
    }

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class, 'company_id');
    }

    public function documentSequences(): HasMany
    {
        return $this->hasMany(DocumentSequence::class, 'company_id');
    }
}
