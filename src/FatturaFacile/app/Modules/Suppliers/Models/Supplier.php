<?php

namespace App\Modules\Suppliers\Models;

use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\Purchasing\Models\PurchaseOrder;
use App\Modules\Purchasing\Models\SupplierInvoice;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Supplier extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'code',
        'name',
        'tax_number',
        'vat_number',
        'email',
        'phone',
        'street',
        'city',
        'postal_code',
        'country',
        'payment_term_days',
        'currency',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'payment_term_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function supplierInvoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class, 'supplier_id');
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
}
