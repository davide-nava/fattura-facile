<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class ContactGroupData
{
    public function __construct(
        public int $companyId,
        public string $name,
        public ?string $code = null,
        public string $type = 'general',
        public ?string $color = null,
        public ?string $description = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?int $fallbackCompanyId = null): self
    {
        return new self(
            companyId: (int) ($data['company_id'] ?? $fallbackCompanyId ?? 1),
            name: (string) ($data['name'] ?? ''),
            code: isset($data['code']) ? (string) $data['code'] : null,
            type: (string) ($data['type'] ?? 'general'),
            color: isset($data['color']) ? (string) $data['color'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'color' => $this->color,
            'description' => $this->description,
        ];
    }
}
