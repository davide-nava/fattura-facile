<?php

namespace App\Modules\Products\Models;

use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'category_id',
        'tax_rate_id',
        'name',
        'sku',
        'type', // product, service
        'sales_price',
        'purchase_price',
        'unit',
        'is_stock_managed',
        'is_active',
        'description',
    ];

    protected $casts = [
        'sales_price' => 'string',
        'purchase_price' => 'string',
        'is_stock_managed' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class, 'product_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function getTotalStockAttribute(): string
    {
        if (! $this->is_stock_managed) {
            return '0.0000';
        }

        return (string) $this->stockBalances()->sum('quantity');
    }
}
