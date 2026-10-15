<?php

namespace App\Modules\Products\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'product_categories';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'name',
        'code',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
