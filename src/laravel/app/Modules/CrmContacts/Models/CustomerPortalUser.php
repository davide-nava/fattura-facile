<?php

namespace App\Modules\CrmContacts\Models;

use App\Modules\Customers\Models\Customer;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerPortalUser extends Authenticatable
{
    use BelongsToCompany, BelongsToTenant, HasFactory, Notifiable;

    protected $table = 'customer_portal_users';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'customer_id',
        'contact_person_id',
        'name',
        'email',
        'password',
        'access_token',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'access_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contactPerson(): BelongsTo
    {
        return $this->belongsTo(ContactPerson::class, 'contact_person_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasAccessToTenant(int $tenantId): bool
    {
        return (int) $this->tenant_id === (int) $tenantId;
    }

    public function getCurrentTenantIdAttribute(): ?int
    {
        return $this->tenant_id;
    }
}
