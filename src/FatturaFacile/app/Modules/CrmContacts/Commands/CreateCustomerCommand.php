<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\CustomerData;

readonly class CreateCustomerCommand
{
    public function __construct(
        public CustomerData $data
    ) {}
}
