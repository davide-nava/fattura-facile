<?php

namespace App\Modules\CrmContacts\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ContactPerson extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'contact_persons';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'contactable_type',
        'contactable_id',
        'first_name',
        'last_name',
        'job_title',
        'department',
        'email',
        'phone',
        'mobile',
        'is_primary',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function portalUser(): HasOne
    {
        return $this->hasOne(CustomerPortalUser::class, 'contact_person_id');
    }

    public function groups(): MorphToMany
    {
        return $this->morphToMany(ContactGroup::class, 'member', 'contact_group_memberships');
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}")
        );
    }
}
