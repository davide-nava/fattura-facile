<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Payments\Models\PaymentAllocation;
use App\Modules\Suppliers\Models\Supplier;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SupplierInvoice extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'supplier_invoices';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'supplier_id',
        'purchase_order_id',
        'supplier_invoice_number',
        'invoice_date',
        'due_date',
        'status', // draft, received, approved, paid, cancelled
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'paid_amount',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function paymentAllocations(): MorphMany
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
    }
}
