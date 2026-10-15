<?php

namespace App\Modules\Payments\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentAllocation extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'payment_allocations';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'payment_id',
        'allocatable_type',
        'allocatable_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'string',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }
}
