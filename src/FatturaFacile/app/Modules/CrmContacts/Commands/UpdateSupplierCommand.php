<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\SupplierData;

readonly class UpdateSupplierCommand
{
    public function __construct(
        public int $supplierId,
        public SupplierData $data
    ) {}
}
