<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Suppliers\Models\Supplier;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'supplier_id',
        'order_number',
        'order_date',
        'expected_date',
        'status', // draft, ordered, partially_received, received, cancelled
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'string',
        'tax_total' => 'string',
        'total' => 'string',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class, 'purchase_order_id');
    }

    public function supplierInvoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class, 'purchase_order_id');
    }
}
