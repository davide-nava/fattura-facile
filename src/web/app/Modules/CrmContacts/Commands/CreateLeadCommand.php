<?php

namespace App\Modules\CrmContacts\Commands;

use App\Modules\CrmContacts\DTOs\LeadData;

readonly class CreateLeadCommand
{
    public function __construct(
        public LeadData $data
    ) {}
}
