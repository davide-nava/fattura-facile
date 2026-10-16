<?php

namespace App\Modules\CrmContacts\Queries;

readonly class GetContactOverviewQuery
{
    public function __construct(
        public ?int $companyId = null,
        public ?string $search = null,
        public ?string $type = null, // customer, supplier, lead
        public ?int $groupId = null,
        public int $perPage = 25
    ) {}
}
