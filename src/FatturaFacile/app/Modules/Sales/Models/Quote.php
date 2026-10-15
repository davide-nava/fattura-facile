<?php

namespace App\Modules\Sales\Models;

use App\Modules\Customers\Models\Customer;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'quotes';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'customer_id',
        'quote_number',
        'quote_date',
        'valid_until',
        'status', // draft, sent, accepted, rejected, converted
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'notes',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'string',
        'tax_total' => 'string',
        'total' => 'string',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }
}
