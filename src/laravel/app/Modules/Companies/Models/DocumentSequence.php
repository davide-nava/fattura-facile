<?php

namespace App\Modules\Companies\Models;

use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentSequence extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'document_sequences';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'type',        // invoice, quote, sales_order, delivery_note, credit_note, purchase_order
        'prefix',      // e.g. INV-2026-
        'next_number', // integer sequence
        'padding',     // e.g. 5 digits -> 00001
    ];

    /**
     * Atomically generate next document number without duplicates or race conditions.
     */
    public static function getNextNumber(int $tenantId, int $companyId, string $type, string $defaultPrefix = ''): string
    {
        return DB::transaction(function () use ($tenantId, $companyId, $type, $defaultPrefix) {
            $sequence = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('company_id', $companyId)
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $year = date('Y');
                $prefix = $defaultPrefix ?: strtoupper(substr($type, 0, 3))."-{$year}-";
                $sequence = static::create([
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'type' => $type,
                    'prefix' => $prefix,
                    'next_number' => 1,
                    'padding' => 5,
                ]);
            }

            $currentNum = $sequence->next_number;
            $sequence->next_number = $currentNum + 1;
            $sequence->save();

            $padded = str_pad((string) $currentNum, $sequence->padding, '0', STR_PAD_LEFT);

            return $sequence->prefix.$padded;
        });
    }
}
