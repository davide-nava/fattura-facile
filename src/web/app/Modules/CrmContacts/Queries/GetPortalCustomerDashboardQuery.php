<?php

namespace App\Modules\CrmContacts\Queries;

readonly class GetPortalCustomerDashboardQuery
{
    public function __construct(
        public int $portalUserId
    ) {}
}
