<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class ContactPersonData
{
    /**
     * @param  array<int>  $groupIds
     */
    public function __construct(
        public int $companyId,
        public string $firstName,
        public string $lastName,
        public ?string $contactableType = null,
        public ?int $contactableId = null,
        public ?string $jobTitle = null,
        public ?string $department = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $mobile = null,
        public bool $isPrimary = false,
        public bool $isActive = true,
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
            firstName: (string) ($data['first_name'] ?? ''),
            lastName: (string) ($data['last_name'] ?? ''),
            contactableType: isset($data['contactable_type']) ? (string) $data['contactable_type'] : null,
            contactableId: isset($data['contactable_id']) ? (int) $data['contactable_id'] : null,
            jobTitle: isset($data['job_title']) ? (string) $data['job_title'] : null,
            department: isset($data['department']) ? (string) $data['department'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            mobile: isset($data['mobile']) ? (string) $data['mobile'] : null,
            isPrimary: (bool) ($data['is_primary'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
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
            'contactable_type' => $this->contactableType,
            'contactable_id' => $this->contactableId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'job_title' => $this->jobTitle,
            'department' => $this->department,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'is_primary' => $this->isPrimary,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
        ];
    }
}
