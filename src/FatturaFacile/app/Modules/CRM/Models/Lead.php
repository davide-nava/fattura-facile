<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\Customers\Models\Customer;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
        'status', // new, contacted, qualified, unqualified, converted
        'source',
        'assigned_to',
        'converted_customer_id',
        'converted_at',
        'notes',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
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

    public function contactPersons(): MorphMany
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    public function groups(): MorphToMany
    {
        return $this->morphToMany(ContactGroup::class, 'member', 'contact_group_memberships');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }
}
