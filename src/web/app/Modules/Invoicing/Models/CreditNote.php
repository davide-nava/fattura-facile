<?php

namespace App\Modules\Invoicing\Models;

use App\Modules\Customers\Models\Customer;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'credit_notes';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'customer_id',
        'invoice_id',
        'credit_note_number',
        'credit_note_date',
        'status', // draft, issued, applied
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'reason',
        'issued_at',
    ];

    protected $casts = [
        'credit_note_date' => 'date',
        'issued_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id');
    }
}
