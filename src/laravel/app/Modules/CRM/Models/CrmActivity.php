<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmActivity extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'crm_activities';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'subject',
        'type', // call, meeting, email, task
        'due_date',
        'completed_at',
        'assigned_to',
        'activatable_type',
        'activatable_id',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activatable(): MorphTo
    {
        return $this->morphTo();
    }
}
