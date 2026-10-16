<?php

namespace App\Modules\CrmContacts\Commands;

readonly class EnableCustomerPortalAccessCommand
{
    public function __construct(
        public int $customerId,
        public string $email,
        public string $password,
        public ?string $name = null,
        public ?int $contactPersonId = null,
    ) {}
}
