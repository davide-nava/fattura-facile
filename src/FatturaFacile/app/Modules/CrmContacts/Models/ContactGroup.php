<?php

namespace App\Modules\CrmContacts\Models;

use App\Modules\CRM\Models\Lead;
use App\Modules\Customers\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ContactGroup extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'contact_groups';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'code',
        'type', // customer, supplier, lead, general
        'color',
        'description',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(ContactGroupMembership::class, 'contact_group_id');
    }

    public function customers(): MorphToMany
    {
        return $this->morphedByMany(Customer::class, 'member', 'contact_group_memberships');
    }

    public function suppliers(): MorphToMany
    {
        return $this->morphedByMany(Supplier::class, 'member', 'contact_group_memberships');
    }

    public function leads(): MorphToMany
    {
        return $this->morphedByMany(Lead::class, 'member', 'contact_group_memberships');
    }

    public function contactPersons(): MorphToMany
    {
        return $this->morphedByMany(ContactPerson::class, 'member', 'contact_group_memberships');
    }
}
