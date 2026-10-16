<?php

namespace App\Modules\Payments\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'payment_number',
        'payment_date',
        'payment_method', // bank_transfer, cash, card, qr_bill, other
        'amount',
        'currency',
        'reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'string',
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }
}
