<?php

namespace App\Modules\CrmContacts\Models;

use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactGroupMembership extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'contact_group_memberships';

    protected $fillable = [
        'tenant_id',
        'contact_group_id',
        'member_type',
        'member_id',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ContactGroup::class, 'contact_group_id');
    }

    public function member(): MorphTo
    {
        return $this->morphTo();
    }
}
