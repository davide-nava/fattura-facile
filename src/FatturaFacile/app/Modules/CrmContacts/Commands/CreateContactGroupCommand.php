<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\ContactGroupData;

readonly class CreateContactGroupCommand
{
    public function __construct(
        public ContactGroupData $data
    ) {}
}
