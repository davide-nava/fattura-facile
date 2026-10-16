<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\ContactPersonData;

readonly class UpdateContactPersonCommand
{
    public function __construct(
        public int $contactPersonId,
        public ContactPersonData $data
    ) {}
}
