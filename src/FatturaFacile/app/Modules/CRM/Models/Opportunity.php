<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Opportunity extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'opportunities';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'customer_id',
        'lead_id',
        'name',
        'expected_revenue',
        'stage', // prospecting, qualification, proposal, negotiation, closed_won, closed_lost
        'probability',
        'expected_close_date',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'expected_revenue' => 'string',
        'probability' => 'integer',
        'expected_close_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activatable');
    }
}
