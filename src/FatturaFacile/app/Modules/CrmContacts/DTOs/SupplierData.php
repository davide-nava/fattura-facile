<?php

namespace App\Modules\CrmContacts\DTOs;

readonly class SupplierData
{
    /**
     * @param  array<int>  $groupIds
     */
    public function __construct(
        public int $companyId,
        public string $name,
        public ?string $code = null,
        public ?string $taxNumber = null,
        public ?string $vatNumber = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $street = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public string $country = 'CH',
        public int $paymentTermDays = 30,
        public string $currency = 'CHF',
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
            name: (string) ($data['name'] ?? ''),
            code: isset($data['code']) ? (string) $data['code'] : null,
            taxNumber: isset($data['tax_number']) ? (string) $data['tax_number'] : null,
            vatNumber: isset($data['vat_number']) ? (string) $data['vat_number'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            street: isset($data['street']) ? (string) $data['street'] : null,
            city: isset($data['city']) ? (string) $data['city'] : null,
            postalCode: isset($data['postal_code']) ? (string) $data['postal_code'] : null,
            country: (string) ($data['country'] ?? 'CH'),
            paymentTermDays: (int) ($data['payment_term_days'] ?? 30),
            currency: (string) ($data['currency'] ?? 'CHF'),
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
            'name' => $this->name,
            'code' => $this->code,
            'tax_number' => $this->taxNumber,
            'vat_number' => $this->vatNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'payment_term_days' => $this->paymentTermDays,
            'currency' => $this->currency,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
        ];
    }
}
