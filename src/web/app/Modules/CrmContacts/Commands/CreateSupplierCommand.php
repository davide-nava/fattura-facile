<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\SupplierData;

readonly class CreateSupplierCommand
{
    public function __construct(
        public SupplierData $data
    ) {}
}
