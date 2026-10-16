<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class ContactOverviewData
{
    /**
     * @param  array<string, mixed>  $recentContacts
     */
    public function __construct(
        public int $totalCustomers,
        public int $totalSuppliers,
        public int $totalLeads,
        public int $totalContactPersons,
        public int $totalGroups,
        public array $recentContacts = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_customers' => $this->totalCustomers,
            'total_suppliers' => $this->totalSuppliers,
            'total_leads' => $this->totalLeads,
            'total_contact_persons' => $this->totalContactPersons,
            'total_groups' => $this->totalGroups,
            'recent_contacts' => $this->recentContacts,
        ];
    }
}
