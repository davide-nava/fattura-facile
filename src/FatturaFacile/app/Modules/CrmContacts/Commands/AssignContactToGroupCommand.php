<?php

namespace App\Modules\CrmContacts\Commands;

readonly class AssignContactToGroupCommand
{
    public function __construct(
        public int $groupId,
        public string $memberType,
        public int $memberId
    ) {}
}
