<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\CrmContacts\Commands\AssignContactToGroupCommand;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactGroupMembership;
use App\Modules\Tenancy\Services\TenantContext;

class AssignContactToGroupHandler
{
    public function __construct(
        private TenantContext $tenantContext
    ) {}

    public function handle(AssignContactToGroupCommand $command): ContactGroupMembership
    {
        $group = ContactGroup::findOrFail($command->groupId);
        $tenantId = $this->tenantContext->getTenantId() ?? $group->tenant_id;

        return ContactGroupMembership::firstOrCreate([
            'tenant_id' => $tenantId,
            'contact_group_id' => $group->id,
            'member_type' => $command->memberType,
            'member_id' => $command->memberId,
        ]);
    }
}
