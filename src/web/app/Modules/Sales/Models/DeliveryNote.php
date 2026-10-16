<?php

namespace App\Modules\Sales\Models;

use App\Modules\Inventory\Models\Warehouse;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'delivery_notes';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'sales_order_id',
        'warehouse_id',
        'delivery_number',
        'delivery_date',
        'status', // draft, shipped, delivered, cancelled
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class, 'delivery_note_id');
    }
}
