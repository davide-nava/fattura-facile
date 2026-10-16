<?php

namespace App\Modules\Purchasing\Models;

use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Suppliers\Models\Supplier;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'goods_receipts';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'supplier_id',
        'purchase_order_id',
        'warehouse_id',
        'receipt_number',
        'receipt_date',
        'status', // received, inspected
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }
}
