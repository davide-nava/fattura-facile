<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Products\Models\Product;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'stock_movements';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'warehouse_id',
        'product_id',
        'movement_type', // goods_receipt, goods_issue, adjustment_in, adjustment_out, transfer_in, transfer_out
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'string',
        'unit_cost' => 'string',
        'created_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
