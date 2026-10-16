<?php

namespace App\Modules\CrmContacts\Queries;

readonly class GetCustomerSalesStatsQuery
{
    public function __construct(
        public int $customerId,
        public ?int $year = null
    ) {}
}
