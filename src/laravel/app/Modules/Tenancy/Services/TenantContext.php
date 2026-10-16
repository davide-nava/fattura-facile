<?php

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\Models\Tenant;
use RuntimeException;

class TenantContext
{
    private ?Tenant $tenant = null;

    private bool $bypassIsolation = false;

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function getTenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function requireTenant(): Tenant
    {
        if ($this->tenant === null) {
            throw new RuntimeException('A valid tenant context is strictly required for this operation.');
        }

        return $this->tenant;
    }

    public function requireTenantId(): int
    {
        return $this->requireTenant()->id;
    }

    public function isBypassingIsolation(): bool
    {
        return $this->bypassIsolation;
    }

    /**
     * Explicit privileged execution for system tasks (e.g. cross-tenant migrations or system health).
     */
    public function executeAsSystem(callable $callback): mixed
    {
        $previousBypass = $this->bypassIsolation;
        $this->bypassIsolation = true;

        try {
            return $callback();
        } finally {
            $this->bypassIsolation = $previousBypass;
        }
    }

    public function reset(): void
    {
        $this->tenant = null;
        $this->bypassIsolation = false;
    }
}
