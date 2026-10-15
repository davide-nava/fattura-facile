<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Companies\Models\Company;
use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'is_active',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class, 'tenant_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_memberships')
            ->withPivot(['role', 'is_active', 'permissions'])
            ->withTimestamps();
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'tenant_id');
    }
}
