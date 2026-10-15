<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'title',
        'contact_name',
        'company_name',
        'email',
        'phone',
        'status', // new, contacted, qualified, unqualified
        'source',
        'assigned_to',
        'notes',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'lead_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activatable');
    }
}
