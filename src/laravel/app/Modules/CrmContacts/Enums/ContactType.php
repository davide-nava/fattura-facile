<?php

namespace App\Modules\CrmContacts\Enums;

enum ContactType: string
{
    case Customer = 'customer';
    case Supplier = 'supplier';
    case Lead = 'lead';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Supplier => 'Supplier',
            self::Lead => 'Lead',
            self::Other => 'Other',
        };
    }
}
