<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Contracts;

use App\Modules\Companies\Models\Company;
use App\Modules\Reporting\DTOs\ReportResult;

interface ReportInterface
{
    /**
     * Unique identifier / slug of the report (e.g. 'financial-summary').
     */
    public function key(): string;

    /**
     * Human-readable title of the report.
     */
    public function name(): string;

    /**
     * Short description of the report's purpose and contents.
     */
    public function description(): string;

    /**
     * Category grouping (e.g. 'Financial', 'Sales', 'Tax').
     */
    public function category(): string;

    /**
     * Blade template view name for presentation.
     *
     * @return view-string
     */
    public function view(): string;

    /**
     * Preferred paper orientation ('portrait' or 'landscape').
     */
    public function paperOrientation(): string;

    /**
     * Preferred paper size ('a4', 'letter', etc.).
     */
    public function paperSize(): string;

    /**
     * Execute data gathering and business logic formatting to build the report.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function generate(Company $company, array $parameters = []): ReportResult;
}
