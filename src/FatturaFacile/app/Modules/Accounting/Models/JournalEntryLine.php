<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::updating(function (JournalEntryLine $line) {
            if ($line->journalEntry?->is_posted) {
                throw new RuntimeException('Lines on a posted journal entry cannot be modified.');
            }
        });

        static::deleting(function (JournalEntryLine $line) {
            if ($line->journalEntry?->is_posted) {
                throw new RuntimeException('Lines on a posted journal entry cannot be deleted.');
            }
        });
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
