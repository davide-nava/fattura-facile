<?php

namespace App\Modules\CrmContacts\Commands;

readonly class ConvertLeadToCustomerCommand
{
    public function __construct(
        public int $leadId,
        public ?string $customerCode = null,
        public string $currency = 'CHF',
        public int $paymentTermDays = 30,
    ) {}
}
