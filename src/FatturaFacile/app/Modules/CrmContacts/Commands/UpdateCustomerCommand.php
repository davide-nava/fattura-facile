<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\CustomerData;

readonly class UpdateCustomerCommand
{
    public function __construct(
        public int $customerId,
        public CustomerData $data
    ) {}
}
