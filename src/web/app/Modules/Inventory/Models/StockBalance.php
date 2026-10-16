<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Products\Models\Product;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'stock_balances';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'avg_unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'avg_unit_cost' => 'decimal:4',
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
