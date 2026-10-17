<?php

declare(strict_types=1);

namespace App\Modules\Reporting\DTOs;

use App\Modules\Companies\Models\Company;
use App\Modules\Companies\Services\CompanyBranding;
use Carbon\CarbonImmutable;

class ReportResult
{
    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<int, array{label: string, value: string, subvalue?: ?string, change?: ?string}>  $kpis
     * @param  array<string, array{headers: array<int, string>, rows: array<int, array<string|int, mixed>>, alignment?: array<int, string>}>  $tables
     * @param  array<string, string>  $summary
     */
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $subtitle,
        public readonly Company $company,
        public readonly CompanyBranding $branding,
        public readonly CarbonImmutable $generatedAt,
        public readonly array $parameters = [],
        public readonly array $kpis = [],
        public readonly array $tables = [],
        public readonly array $summary = [],
        public readonly ?string $notes = null,
        public readonly string $orientation = 'portrait',
        public readonly string $paperSize = 'a4',
    ) {}

    /**
     * Convert DTO into array representation suitable for Blade view presentation.
     *
     * @return array<string, mixed>
     */
    public function toViewData(): array
    {
        return [
            'report' => $this,
            'key' => $this->key,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'company' => $this->company,
            'branding' => $this->branding,
            'generatedAt' => $this->generatedAt,
            'parameters' => $this->parameters,
            'kpis' => $this->kpis,
            'tables' => $this->tables,
            'summary' => $this->summary,
            'notes' => $this->notes,
            'orientation' => $this->orientation,
            'paperSize' => $this->paperSize,
        ];
    }
}
