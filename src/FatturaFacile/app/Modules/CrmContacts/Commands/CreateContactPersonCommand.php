<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\ContactPersonData;

readonly class CreateContactPersonCommand
{
    public function __construct(
        public ContactPersonData $data
    ) {}
}
