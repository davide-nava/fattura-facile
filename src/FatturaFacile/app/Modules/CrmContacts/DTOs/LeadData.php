<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class LeadData
{
    /**
     * @param  array<int>  $groupIds
     */
    public function __construct(
        public int $companyId,
        public string $title,
        public string $contactName,
        public ?string $companyName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public string $status = 'new',
        public ?string $source = null,
        public ?int $assignedTo = null,
        public ?string $notes = null,
        public array $groupIds = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?int $fallbackCompanyId = null): self
    {
        return new self(
            companyId: (int) ($data['company_id'] ?? $fallbackCompanyId ?? 1),
            title: (string) ($data['title'] ?? ''),
            contactName: (string) ($data['contact_name'] ?? ''),
            companyName: isset($data['company_name']) ? (string) $data['company_name'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            status: (string) ($data['status'] ?? 'new'),
            source: isset($data['source']) ? (string) $data['source'] : null,
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            groupIds: isset($data['group_ids']) && is_array($data['group_ids']) ? $data['group_ids'] : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'title' => $this->title,
            'contact_name' => $this->contactName,
            'company_name' => $this->companyName,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'source' => $this->source,
            'assigned_to' => $this->assignedTo,
            'notes' => $this->notes,
        ];
    }
}
