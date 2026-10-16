<?php

namespace App\Modules\Customers\Models;

use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\CrmContacts\Models\CustomerPortalUser;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Sales\Models\Quote;
use App\Modules\Sales\Models\SalesOrder;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Customer extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'code',
        'name',
        'legal_name',
        'tax_number',
        'vat_number',
        'email',
        'phone',
        'street',
        'city',
        'postal_code',
        'state_canton',
        'country',
        'payment_term_days',
        'currency',
        'credit_limit',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'string',
        'payment_term_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'customer_id');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function contactPersons(): MorphMany
    {
        return $this->morphMany(ContactPerson::class, 'contactable');
    }

    public function primaryContact(): MorphOne
    {
        return $this->morphOne(ContactPerson::class, 'contactable')->where('is_primary', true);
    }

    public function groups(): MorphToMany
    {
        return $this->morphToMany(ContactGroup::class, 'member', 'contact_group_memberships');
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(CustomerPortalUser::class, 'customer_id');
    }
}
