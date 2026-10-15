<?php

namespace App\Modules\Accounting\Models;

use App\Models\User;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class JournalEntry extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'period_id',
        'entry_number',
        'entry_date',
        'reference',
        'description',
        'source_type', // manual, invoice, supplier_invoice, payment, inventory
        'source_id',
        'is_posted',
        'posted_at',
        'posted_by',
        'reversal_of_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::updating(function (JournalEntry $entry) {
            // Prevent changing posted entry details if it was already posted
            if ($entry->getOriginal('is_posted') && ! $entry->isDirty('reversal_of_id')) {
                throw new RuntimeException('Posted journal entries are strictly immutable. Create a reversal entry instead.');
            }
        });

        static::deleting(function (JournalEntry $entry) {
            if ($entry->is_posted) {
                throw new RuntimeException('Posted journal entries cannot be deleted.');
            }
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'period_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_of_id');
    }
}
