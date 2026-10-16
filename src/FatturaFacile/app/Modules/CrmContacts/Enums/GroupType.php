<?php

namespace App\Modules\CrmContacts\Enums;

enum GroupType: string
{
    case General = 'general';
    case Customer = 'customer';
    case Supplier = 'supplier';
    case Lead = 'lead';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Customer => 'Customer',
            self::Supplier => 'Supplier',
            self::Lead => 'Lead',
        };
    }
}
