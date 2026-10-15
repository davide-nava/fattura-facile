<?php

namespace App\Modules\Documents\Models;

use App\Models\User;
use App\Shared\Traits\BelongsToCompany;
use App\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use BelongsToCompany, BelongsToTenant, HasFactory;

    protected $table = 'attachments';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'user_id',
        'attachable_type',
        'attachable_id',
        'file_name',
        'file_path',
        'disk',
        'mime_type',
        'file_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
